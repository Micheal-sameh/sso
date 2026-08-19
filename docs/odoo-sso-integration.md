# Connecting Odoo to Avarewase Auth (SSO)

Odoo's built-in **Sign in with Provider** feature (`auth_oauth` module) speaks the
OAuth2 **implicit grant** (`response_type=token`), not the authorization-code +
PKCE flow used by every other client on this SSO. It also calls its
"Validation Endpoint" with the access token as a **query parameter**
(`?access_token=...`), not an `Authorization: Bearer` header.

To support this, the SSO now has:

1. An **Implicit** grant type option when registering an OAuth client (in
   addition to the existing Authorization Code / PKCE option).
2. A dedicated validation endpoint that accepts the token as a query
   parameter: `GET /api/oauth/odoo/validate?access_token=...`.

> **Verify against your Odoo version.** Odoo's `auth_oauth` field names and
> exact request/response shape have shifted across major versions (13 → 18).
> The steps below match the module's long-standing behavior, but confirm the
> field labels against the specific Odoo version you're running before
> assuming this is 100% correct without testing.

## 1. Register an OAuth client for Odoo

In the SSO admin, go to **OAuth Clients → Register New Client** and fill in:

| Field | Value |
|---|---|
| App Name | `Odoo` (or your instance name) |
| Redirect URI | `https://your-odoo-domain.com/auth_oauth/signin` |
| Grant Type | **Implicit (legacy, e.g. Odoo)** |
| Who can authenticate | whichever access rule fits (All Users / Specific Stage / Specific Users) |

Implicit clients never get a client secret — the token is returned directly
in the browser's redirect fragment, so there's nowhere to keep one secret.
After saving, copy the **Client ID** (a UUID) — you'll need it in Odoo.

## 2. Configure the OAuth provider in Odoo

Odoo doesn't ship Avarewase as a preset provider, so you add a **custom
OAuth provider**. As an admin, with developer mode enabled:

Go to **Settings → General Settings → Users → OAuth Authentication** (or
**Settings → Technical → OAuth Providers**, depending on your Odoo version),
enable OAuth, then create a new provider with:

| Field | Value |
|---|---|
| Provider name | Avarewase SSO |
| Client ID | the UUID copied in step 1 |
| Authorization URL | `https://your-sso-domain.com/oauth/authorize` |
| Scope | `openid profile email` |
| Validation Endpoint | `https://your-sso-domain.com/api/oauth/odoo/validate` |
| CSS class (icon) | any Font Awesome class, e.g. `fa fa-sign-in` |
| Enabled / Allowed | checked |

Odoo builds the authorization redirect itself from `Authorization URL` +
`Client ID` + `Scope`, appending `response_type=token` and a `redirect_uri`
pointing back at `/auth_oauth/signin` on your Odoo instance. That redirect
URI **must exactly match** the one registered in step 1.

## 3. What happens on login

1. User clicks the "Avarewase SSO" login button on Odoo's login page.
2. Odoo redirects the browser to the SSO's `/oauth/authorize` with
   `response_type=token`.
3. The user authenticates (and completes MFA, if enabled) on the SSO.
4. The SSO redirects back to `https://your-odoo-domain.com/auth_oauth/signin#access_token=...&...`
   (implicit grant returns the token in the URL **fragment**, not a query
   string — Odoo's JS reads it client-side).
5. Odoo calls `GET /api/oauth/odoo/validate?access_token=...` on the SSO.
6. The SSO validates the token and responds with:
   ```json
   {
     "user_id": "7ce39f91-6bd6-4256-a548-fd358932341d",
     "email": "user@example.com",
     "name": "Jane Doe"
   }
   ```
7. Odoo matches `email` against an existing `res.users` record (or creates
   one, if your provider config allows sign-up) and logs the user in.

## 4. Field mapping note

`user_id` in the validation response is the SSO user's stable UUID — Odoo
typically stores whatever this field returns as `oauth_uid` on `res.users`.
`email` is used to match/create the Odoo user record. If your Odoo version's
`auth_oauth` expects different key names (some older forks look for
`sub`/`user_id` interchangeably), check `addons/auth_oauth/models/res_users.py`
in your Odoo source for the exact keys it reads from the validation response,
and let us know if they differ — the SSO-side response shape can be adjusted.

## 5. Testing without a full Odoo instance

You can sanity-check the validation endpoint independently of Odoo:

```bash
# Get a token (e.g. from a completed browser login, or any Passport token)
curl "https://your-sso-domain.com/api/oauth/odoo/validate?access_token=<token>"
```

A valid token returns `200` with the JSON shown above; a missing or invalid
token returns `401`.

---

## 6. Full production runbook (what we actually did)

The sections above describe the mechanism. This section is the step-by-step
operational sequence for wiring up a real Odoo deployment, including the
gotchas we hit doing it the first time. Swap in your own SSH hosts / domains
/ container names.

### 6.1 SSO-side prerequisites (one-time per SSO instance)

- `Passport::enableImplicitGrant();` in `app/Providers/AppServiceProvider.php::boot()`
  (implicit grant is disabled by default in Passport).
- `ClientGrantType` enum (`authorization_code` / `implicit`) threaded through
  `OAuthClientDTO`, `OAuthClientRequest`, `OAuthClientService::registerClient()`.
  Implicit clients must be created via `ClientRepository::createImplicitGrantClient()`
  (always non-confidential — no secret is issued).
- "Grant Type" selector in the OAuth Clients admin UI, immutable after creation.
- The `GET /api/oauth/odoo/validate?access_token=...` endpoint described above.

**After any change to the admin UI, rebuild and deploy the compiled frontend
assets on the actual server.** A `git pull` alone does not rebuild a
bind-mounted container's `public/build` output. Verify a newly added form
field actually reached the deployed bundle before relying on it:

```bash
grep -o 'grant_type[a-zA-Z_]*' public/build/assets/Index-*.js | sort -u
```

A stale build silently drops new fields from form submissions (they just
don't get sent), which surfaces later as a confusing backend error — in our
case Passport returning `unauthorized_client` because the client had quietly
been created as `authorization_code` instead of `implicit`, since the
`grant_type` field never reached the server.

### 6.2 Register the OAuth client

In the SSO admin → OAuth Clients → Register New Client:

| Field | Value |
|---|---|
| Redirect URI | `https://<odoo-domain>/auth_oauth/signin` |
| Homepage URL | `https://<odoo-domain>` |
| Grant Type | **Implicit** |
| Who can authenticate | your call |

Copy the Client ID.

### 6.3 Install `auth_oauth` and register the provider on the Odoo server

```bash
docker exec <odoo-container> odoo -c /etc/odoo/odoo.conf -d <db> -i auth_oauth --stop-after-init
```

Insert the provider row directly via `psql` — faster and more reliable than
hunting for the right Settings screen across Odoo versions:

```sql
INSERT INTO auth_oauth_provider
  (sequence, name, client_id, auth_endpoint, scope, validation_endpoint, css_class, body, enabled)
VALUES
  (10, 'Avarewase SSO', '<CLIENT_ID>', 'https://auth.avarewase.com/oauth/authorize',
   'openid profile email', 'https://auth.avarewase.com/api/oauth/odoo/validate',
   'fa fa-sign-in', '{"en_US": "Log in with Avarewase SSO"}', true);
```

### 6.4 The X-Forwarded-Proto gotcha

Fetch the login page and check what `redirect_uri` Odoo actually generates:

```bash
curl -s https://<odoo-domain>/web/login | grep -o 'redirect_uri=[^&"]*'
```

If it comes back URL-encoded as `http%3A%2F%2F...` instead of `https%3A%2F%2F...`
— even when the page itself loaded over real HTTPS — the origin nginx isn't
telling Odoo the original request was HTTPS. This happens when TLS is
terminated upstream (e.g. Cloudflare) and nginx's own listener is plain HTTP:
`proxy_set_header X-Forwarded-Proto $scheme;` reflects nginx's *own* connection
scheme (always `http` in that setup), not the client's. Fix by hardcoding it:

```nginx
proxy_set_header X-Forwarded-Proto https;
```

in the relevant server block, then `nginx -t && systemctl reload nginx` and
re-check the login page. A mismatched `redirect_uri` fails Passport's exact-match
redirect URI validation and breaks the flow non-obviously (looks like a generic
auth failure, not a scheme mismatch).

### 6.5 Custom provisioning module

Odoo's default OAuth signup path (`res.users.signup()` →
`_create_user_from_template`) creates new users from the **portal** template
(`ir.config_parameter['base.template_portal_user_id']`), and separately
refuses "uninvited" signup entirely unless `auth_signup.invitation_scope` is
`b2c`. Changing that global config affects *all* signup on the instance
(including any public/customer website signup), so instead override the
OAuth-specific hook directly — `res.users._auth_oauth_signin` is explicitly
documented in Odoo's source as overridable for this purpose.

Module layout (`add-ons/avarewase_sso_provisioning/`):

```
__manifest__.py
__init__.py
models/__init__.py
models/res_users.py
views/login_templates.xml
```

`__manifest__.py`:

```python
{
    'name': 'Avarewase SSO Provisioning',
    'version': '1.0',
    'summary': 'New Avarewase SSO logins provision as internal users, not portal users',
    'depends': ['auth_oauth', 'web'],
    'author': 'Avarewase',
    'category': 'Authentication',
    'installable': True,
    'auto_install': False,
    'data': ['views/login_templates.xml'],
}
```

`models/res_users.py`:

```python
from odoo import api, models


class ResUsers(models.Model):
    _inherit = 'res.users'

    @api.model
    def _auth_oauth_signin(self, provider, validation, params):
        """Every new SSO identity lands as a plain internal user (Settings >
        Users) with no extra permissions, instead of Odoo's default portal
        template / invitation-scope-gated signup. Every re-authentication
        (not just first login) also refreshes name/email from the SSO, so
        the SSO stays the source of truth for identity data.
        """
        oauth_uid = validation['user_id']
        values = self._generate_signup_values(provider, validation, params)

        sync_values = {
            'name': values['name'],
            'email': values['email'],
            'oauth_access_token': params['access_token'],
        }

        oauth_user = self.sudo().search([
            ('oauth_uid', '=', oauth_uid),
            ('oauth_provider_id', '=', provider),
        ], limit=1)
        if oauth_user:
            oauth_user.write(sync_values)
            return oauth_user.login

        existing = self.sudo().search([('login', '=', values['login'])], limit=1)
        if existing:
            existing.write({
                **sync_values,
                'oauth_uid': oauth_uid,
                'oauth_provider_id': provider,
            })
            return existing.login

        internal_group = self.env.ref('base.group_user')
        new_user = self.sudo().with_context(no_reset_password=True).create({
            'name': values['name'],
            'login': values['login'],
            'email': values['email'],
            'oauth_uid': oauth_uid,
            'oauth_provider_id': provider,
            'oauth_access_token': params['access_token'],
            'group_ids': [(6, 0, [internal_group.id])],
            'active': True,
        })
        return new_user.login
```

Install and restart so the running server picks up the Python override
(installing a module does not hot-reload Python code):

```bash
docker exec <odoo-container> odoo -c /etc/odoo/odoo.conf -d <db> -i avarewase_sso_provisioning --stop-after-init
docker restart <odoo-container>
```

**Verify before trusting it** — pipe a small script into `odoo shell` that
calls `_auth_oauth_signin` directly with fake validation data, check
`share`/`group_ids` on the result, then delete the test user:

```bash
docker exec -i <odoo-container> odoo shell -c /etc/odoo/odoo.conf -d <db> --no-http <<'PY'
provider_id = env['auth.oauth.provider'].search([('name', '=', 'Avarewase SSO')]).id
login = env['res.users']._auth_oauth_signin(provider_id, {
    'user_id': 'test-uid-123', 'email': 'newperson@example.com', 'name': 'New Person',
}, {'access_token': 'fake-token'})
u = env['res.users'].sudo().search([('login', '=', login)])
print('share=', u.share, 'groups=', sorted(u.group_ids.mapped('full_name')))
env.cr.commit()
PY
```

### 6.6 Login page branding cleanup (optional)

Odoo's `web.login_layout` template wraps "Manage Databases" and "Powered by
Odoo" in one footer `<div>` with no config flag to hide the branding link
specifically, so override it via QWeb inheritance in the same module
(`views/login_templates.xml`):

```xml
<odoo>
    <template id="login_layout_no_branding" inherit_id="web.login_layout">
        <xpath expr="//div[@class='text-center small mt-4 pt-3 border-top']" position="replace"/>
    </template>
</odoo>
```

Also set `list_db = False` in `odoo.conf` (restart required) — production
hardening that stops the DB list/selector from being exposed. Note the
`/web/database/manager` route itself still responds either way; actual
destructive actions on it remain gated behind the master password
separately, which is Odoo's real security boundary there.

To disable Odoo's own built-in "Sign in with Odoo.com" button:

```sql
UPDATE auth_oauth_provider SET enabled = false WHERE name = 'Odoo.com Accounts';
```

### 6.7 Promoting a specific SSO identity to full internal access

If one particular SSO login (e.g. your own admin account) needs to match an
existing Odoo Administrator's access level rather than the default minimal
`base.group_user`, do it through the ORM — not raw SQL — so implied/related
groups cascade correctly:

```python
user = env['res.users'].browse(<target_id>)
admin = env['res.users'].browse(<reference_admin_id>)
user.write({'share': False, 'group_ids': [(6, 0, admin.group_ids.ids)]})
env.cr.commit()
```

(Note: Odoo 19 renamed the `res.users` groups field from `groups_id` to
`group_ids` — check your version if copying this verbatim.)
