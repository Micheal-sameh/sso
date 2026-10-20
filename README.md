# Avarewase Auth

"Login with Avarewase" — the branded OAuth2/OIDC authorization server for Avarewase church management systems, hosted at `auth.avarewase.com`.

## 1. Requirements

- PHP 8.4 (tested against 8.3+)
- Node 20+
- MySQL 8+
- Composer 2+

## 2. Installation

```bash
composer install

cp .env.example .env
php artisan key:generate

# configure DB_* in .env, then:
php artisan migrate --seed

php artisan passport:keys

npm install
npm run build
```

The seeder creates a super admin using `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env` (defaults to `admin@avarewase.com` / `changeme`). Change these before seeding a production database.

For local development, run `npm run dev` alongside `php artisan serve`.

## 3. Register an OAuth Client (admin panel walkthrough)

1. Sign in as the super admin at `/login`.
2. Navigate to **OAuth Clients** in the admin sidebar (`/admin/clients`).
3. Click **Register New Client** and fill in:
   - **App Name** — shown on the consent screen.
   - **Redirect URI** — where users are sent back after authorizing (must match exactly what your app requests).
   - **Homepage URL** / **Logo URL** — optional, shown on the consent screen.
   - **Internal application** — toggle on for first-party apps to skip the consent screen entirely.
4. Submit. The generated **Client ID** and **Client Secret** are shown once — store the secret securely, it cannot be retrieved again.

## 4. Authorization Code + PKCE Flow

1. **Generate a PKCE pair** in your client app:
   - `code_verifier` — a random string.
   - `code_challenge` — `BASE64URL(SHA256(code_verifier))`.

2. **Redirect the user to authorize:**

   ```
   GET https://auth.avarewase.com/oauth/authorize
       ?client_id={CLIENT_ID}
       &redirect_uri={REDIRECT_URI}
       &response_type=code
       &scope=openid profile email
       &code_challenge={CODE_CHALLENGE}
       &code_challenge_method=S256
       &state={RANDOM_STATE}
   ```

   Internal clients are auto-approved; external clients see the Avarewase consent screen.

3. **Avarewase redirects back** to your `redirect_uri` with `?code=...&state=...`.

4. **Exchange the code for tokens:**

   ```
   POST https://auth.avarewase.com/oauth/token
   Content-Type: application/x-www-form-urlencoded

   grant_type=authorization_code
   client_id={CLIENT_ID}
   client_secret={CLIENT_SECRET}
   redirect_uri={REDIRECT_URI}
   code={CODE}
   code_verifier={CODE_VERIFIER}
   ```

   Response:

   ```json
   {
     "token_type": "Bearer",
     "expires_in": 86400,
     "access_token": "...",
     "refresh_token": "..."
   }
   ```

## 5. Userinfo Endpoint

```
GET https://auth.avarewase.com/api/userinfo
Authorization: Bearer {ACCESS_TOKEN}
```

Response shape (OIDC-flavored):

```json
{
  "data": {
    "sub": "b3f1e2a0-....",
    "name": "Jane Doe",
    "email": "jane@example.com",
    "email_verified": true,
    "picture": "https://auth.avarewase.com/storage/avatars/....jpg",
    "updated_at": "2026-08-13T12:00:00+00:00"
  },
  "message": null
}
```

## 6. Adding "Login with Avarewase" to a Consuming Laravel App

### Manual OAuth2 flow

```php
// routes/web.php
Route::get('/login/avarewase', function () {
    $state = Str::random(40);
    session(['avarewase_state' => $state]);

    return redirect('https://auth.avarewase.com/oauth/authorize?'.http_build_query([
        'client_id' => config('services.avarewase.client_id'),
        'redirect_uri' => config('services.avarewase.redirect'),
        'response_type' => 'code',
        'scope' => 'openid profile email',
        'state' => $state,
    ]));
});

Route::get('/login/avarewase/callback', function (Request $request) {
    abort_unless($request->state === session('avarewase_state'), 403);

    $response = Http::asForm()->post('https://auth.avarewase.com/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => config('services.avarewase.client_id'),
        'client_secret' => config('services.avarewase.client_secret'),
        'redirect_uri' => config('services.avarewase.redirect'),
        'code' => $request->code,
    ]);

    $token = $response->json('access_token');

    $user = Http::withToken($token)->get('https://auth.avarewase.com/api/userinfo')->json('data');

    // find or create local user from $user['sub'] / $user['email'], then log them in
});
```

### Custom Socialite provider

```php
// composer require socialiteproviders/manager

// config/services.php
'avarewase' => [
    'client_id' => env('AVAREWASE_CLIENT_ID'),
    'client_secret' => env('AVAREWASE_CLIENT_SECRET'),
    'redirect' => env('AVAREWASE_REDIRECT_URI'),
],

// app/Providers/SocialiteProvider.php (custom provider)
class AvarewaseProvider extends \SocialiteProviders\Manager\OAuth2\AbstractProvider
{
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.avarewase.com/oauth/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://auth.avarewase.com/oauth/token';
    }

    protected function getUserByToken($token)
    {
        return Http::withToken($token)->get('https://auth.avarewase.com/api/userinfo')->json('data');
    }

    protected function mapUserToObject(array $user)
    {
        return (new \Laravel\Socialite\Two\User)->setRaw($user)->map([
            'id' => $user['sub'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['picture'],
        ]);
    }
}
```

Then: `Socialite::driver('avarewase')->redirect()` / `Socialite::driver('avarewase')->user()`.

## 7. Scope Definitions

| Scope     | Description                                     |
|-----------|--------------------------------------------------|
| `openid`  | Know that you have an Avarewase account          |
| `profile` | View your name and profile picture               |
| `email`   | View your email address                          |

## 8. Production security checklist

Set in the production `.env` (see `.env.example`):

- `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`.
- `ADMIN_MEMBERSHIP_CODE` and an `ADMIN_PASSWORD` of at least 12 characters (the seeder refuses to create a super admin without them). Rotate any password that was ever committed.
- `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` (login fails closed in production without them).
- `TRUSTED_PROXIES` = the address(es) of your reverse proxy if it is not on a private range.
- `OAUTH_IMPLICIT_GRANT=true` **only** if a legacy client (e.g. Odoo `auth_oauth`) needs the implicit grant.
- `CSP_MODE=enforce` is the default. If a page breaks, set `report-only` temporarily and check the browser console for the blocked source, then extend the policy in `app/Http/Middleware/SecurityHeaders.php`.

Docker: the source tree is mounted read-only; only `storage/`, `bootstrap/cache/` and `public/build/` are writable.
Create them on the host first (`mkdir -p storage bootstrap/cache public/build`) and run `php artisan storage:link` there once.

nginx: Odoo sends the access token in the query string of `/api/oauth/odoo/validate`. Keep it out of the access log:

```nginx
map $request_uri $loggable_uri {
    ~^(?<path>[^?]*)\?(.*)access_token=[^&]*(?<rest>.*)$  "$path?$2access_token=REDACTED$rest";
    default                                               $request_uri;
}
log_format sso '$remote_addr - [$time_local] "$request_method $loggable_uri" $status $body_bytes_sent';
access_log /var/log/nginx/sso.access.log sso;
```
