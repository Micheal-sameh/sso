<?php
/**
 * Plugin Name: Avarewase SSO Login
 * Description: "Sign in with Avarewase" for WordPress (OAuth2 authorization code + PKCE).
 * Version: 1.0.0
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

final class Avarewase_SSO_Login
{
    const OPT = 'avarewase_sso_settings';
    const META_SUB = 'avarewase_sub';

    public static function init(): void
    {
        add_action('login_footer', [__CLASS__, 'login_button_below_form']);
        add_action('login_init', [__CLASS__, 'handle_login_actions']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_shortcode('avarewase_login', [__CLASS__, 'shortcode']);
        add_filter('manage_users_columns', [__CLASS__, 'users_column']);
        add_filter('manage_users_custom_column', [__CLASS__, 'users_column_value'], 10, 3);
    }

    /* ---------- Users list: show which accounts are linked to Avarewase SSO ---------- */

    public static function users_column(array $cols): array
    {
        $cols['avarewase_sso'] = 'Avarewase SSO';
        return $cols;
    }

    public static function users_column_value($value, $column, $user_id)
    {
        if ($column !== 'avarewase_sso') {
            return $value;
        }
        $sub = get_user_meta((int) $user_id, self::META_SUB, true);
        return $sub !== '' ? '<span title="' . esc_attr('SSO ID: ' . $sub) . '">&#10003; Linked</span>' : '&mdash;';
    }

    /* ---------- settings ---------- */

    private static function cfg(string $key, string $default = ''): string
    {
        $const = 'AVAREWASE_SSO_' . strtoupper($key);
        if (defined($const)) {
            return (string) constant($const);
        }
        $o = get_option(self::OPT, []);
        return isset($o[$key]) && $o[$key] !== '' ? (string) $o[$key] : $default;
    }

    private static function base(): string
    {
        return untrailingslashit(self::cfg('base_url', 'https://auth.avarewase.com'));
    }

    private static function redirect_uri(): string
    {
        return add_query_arg('avarewase_sso', 'callback', wp_login_url());
    }

    /** The SSO base URL must be https (override for local dev with the avarewase_sso_allow_insecure_base filter). */
    private static function base_is_secure(): bool
    {
        $p = wp_parse_url(self::base());
        if (empty($p['scheme']) || empty($p['host'])) {
            return false;
        }
        return $p['scheme'] === 'https' || (bool) apply_filters('avarewase_sso_allow_insecure_base', false);
    }

    private static function configured(): bool
    {
        return self::cfg('client_id') !== '' && self::cfg('client_secret') !== '' && self::base_is_secure();
    }

    /** A role is "safe" for auto-provisioning only if it has no administrative capabilities. */
    private static function role_is_safe(string $role): bool
    {
        $r = get_role($role);
        if (! $r) {
            return false;
        }
        $dangerous = ['manage_options', 'promote_users', 'edit_users', 'create_users', 'delete_users', 'install_plugins',
            'activate_plugins', 'edit_plugins', 'edit_themes', 'switch_themes', 'update_core', 'unfiltered_html', 'manage_network'];
        foreach ($dangerous as $cap) {
            if ($r->has_cap($cap)) {
                return false;
            }
        }
        return true;
    }

    private static function default_role(): string
    {
        $role = sanitize_key(self::cfg('default_role', 'subscriber'));
        return self::role_is_safe($role) ? $role : 'subscriber';
    }

    private static function is_privileged(WP_User $u): bool
    {
        return is_super_admin($u->ID) || user_can($u, 'manage_options') || user_can($u, 'promote_users') || user_can($u, 'edit_users');
    }

    /* ---------- state cookie (binds the OAuth flow to the browser that started it) ---------- */

    const STATE_COOKIE = 'avarewase_sso_state';

    private static function state_cookie(string $value, int $expires): void
    {
        setcookie(self::STATE_COOKIE, $value, [
            'expires' => $expires,
            'path' => COOKIEPATH ?: '/',
            'domain' => COOKIE_DOMAIN ? (string) COOKIE_DOMAIN : '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function admin_menu(): void
    {
        add_options_page('Avarewase SSO', 'Avarewase SSO', 'manage_options', 'avarewase-sso', [__CLASS__, 'settings_page']);
    }

    public static function register_settings(): void
    {
        register_setting('avarewase_sso', self::OPT, ['sanitize_callback' => function ($in) {
            $in = is_array($in) ? $in : [];
            $old = get_option(self::OPT, []);
            $old = is_array($old) ? $old : [];

            $base = esc_url_raw((string) ($in['base_url'] ?? ''));
            if ($base !== '' && strpos($base, 'https://') !== 0 && ! apply_filters('avarewase_sso_allow_insecure_base', false)) {
                add_settings_error(self::OPT, 'base_url', 'The SSO base URL must use https://. Previous value kept.');
                $base = $old['base_url'] ?? '';
            }

            // Blank secret field = keep the stored one (the secret is never echoed back into the page).
            $secret = trim((string) ($in['client_secret'] ?? ''));
            if ($secret === '') {
                $secret = (string) ($old['client_secret'] ?? '');
            }

            $role = sanitize_key((string) ($in['default_role'] ?? 'subscriber'));
            if (! self::role_is_safe($role)) {
                add_settings_error(self::OPT, 'default_role', 'That role has administrative capabilities and cannot be used for new SSO users. Using "subscriber".');
                $role = 'subscriber';
            }

            return [
                'base_url' => $base,
                'client_id' => sanitize_text_field((string) ($in['client_id'] ?? '')),
                'client_secret' => $secret,
                'auto_create' => empty($in['auto_create']) ? '0' : '1',
                'default_role' => $role,
            ];
        }]);
    }

    public static function settings_page(): void
    {
        $o = get_option(self::OPT, []);
        $n = self::OPT;
        ?>
        <div class="wrap"><h1>Avarewase SSO</h1>
        <p>Register this site in the SSO admin (<em>OAuth Clients &rarr; Register New Client</em>, grant type
        <strong>Authorization Code</strong>) with this Redirect URI:</p>
        <p><code><?php echo esc_html(self::redirect_uri()); ?></code></p>
        <form method="post" action="options.php">
            <?php settings_fields('avarewase_sso'); ?>
            <table class="form-table">
                <tr><th>SSO base URL</th><td><input class="regular-text" name="<?php echo esc_attr($n); ?>[base_url]" value="<?php echo esc_attr($o['base_url'] ?? 'https://auth.avarewase.com'); ?>"></td></tr>
                <tr><th>Client ID</th><td><input class="regular-text" name="<?php echo esc_attr($n); ?>[client_id]" value="<?php echo esc_attr($o['client_id'] ?? ''); ?>"></td></tr>
                <tr><th>Client secret</th><td><input class="regular-text" type="password" autocomplete="new-password" name="<?php echo esc_attr($n); ?>[client_secret]" value="" placeholder="<?php echo esc_attr(! empty($o['client_secret']) ? '•••••••• (saved — leave blank to keep)' : ''); ?>"></td></tr>
                <tr><th>Create new users</th><td><label><input type="checkbox" name="<?php echo esc_attr($n); ?>[auto_create]" value="1" <?php checked(($o['auto_create'] ?? '1'), '1'); ?>> Create a WordPress account on first SSO login</label></td></tr>
                <tr><th>Default role</th><td><select name="<?php echo esc_attr($n); ?>[default_role]"><?php wp_dropdown_roles($o['default_role'] ?? 'subscriber'); ?></select></td></tr>
            </table>
            <p>Instead of storing these in the DB, you can define <code>AVAREWASE_SSO_CLIENT_ID</code>, <code>AVAREWASE_SSO_CLIENT_SECRET</code> and <code>AVAREWASE_SSO_BASE_URL</code> in wp-config.php.</p>
            <?php submit_button(); ?>
        </form></div>
        <?php
    }

    /* ---------- UI ---------- */

    private static function start_url(): string
    {
        return add_query_arg('avarewase_sso', 'start', wp_login_url());
    }

    public static function login_button_below_form(): void
    {
        if (! self::configured()) {
            return;
        }
        $label = is_rtl() ? 'تسجيل الدخول عبر Avarewase' : 'Sign in with Avarewase';
        $or = is_rtl() ? 'أو' : 'or';
        $url = self::start_url();
        if (! empty($_REQUEST['redirect_to']) && is_string($_REQUEST['redirect_to'])) {
            $url = add_query_arg('redirect_to', rawurlencode(wp_unslash($_REQUEST['redirect_to'])), $url);
        }

        echo '<div id="avarewase-sso-block" style="margin:20px auto 0;padding:0 24px;max-width:320px;box-sizing:content-box">'
            . '<p style="text-align:center;margin:0 0 14px;color:#787c82">&mdash; ' . esc_html($or) . ' &mdash;</p>'
            . '<a class="button button-large" style="display:block;width:100%;box-sizing:border-box;text-align:center;float:none;height:auto;line-height:2.4;font-size:15px" href="' . esc_url($url) . '">' . esc_html($label) . '</a>'
            . '</div>';
        echo '<script>(function(){var b=document.getElementById("avarewase-sso-block"),f=document.getElementById("loginform");'
            . 'if(b&&f){b.style.padding="0";b.style.maxWidth="none";f.parentNode.insertBefore(b,f.nextSibling);}})();</script>';
    }

    public static function shortcode(): string
    {
        return self::configured()
            ? '<a class="button" href="' . esc_url(self::start_url()) . '">Sign in with Avarewase</a>'
            : '';
    }

    /* ---------- OAuth flow ---------- */

    public static function handle_login_actions(): void
    {
        $action = $_GET['avarewase_sso'] ?? '';
        if ($action === 'start') {
            self::start();
        } elseif ($action === 'callback') {
            self::callback();
        }
    }

    private static function b64url(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private static function start(): void
    {
        if (! self::configured()) {
            wp_die('Avarewase SSO is not configured.');
        }
        $state = bin2hex(random_bytes(16));
        $verifier = self::b64url(random_bytes(48));
        $to = isset($_GET['redirect_to']) && is_string($_GET['redirect_to'])
            ? wp_validate_redirect(wp_unslash($_GET['redirect_to']), admin_url())
            : admin_url();
        set_transient('avarewase_sso_' . $state, ['v' => $verifier, 'to' => $to], 10 * MINUTE_IN_SECONDS);
        self::state_cookie($state, time() + 10 * MINUTE_IN_SECONDS);

        $url = self::base() . '/oauth/authorize?' . http_build_query([
            'client_id' => self::cfg('client_id'),
            'redirect_uri' => self::redirect_uri(),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'code_challenge' => self::b64url(hash('sha256', $verifier, true)),
            'code_challenge_method' => 'S256',
            'state' => $state,
        ]);
        wp_redirect($url);
        exit;
    }

    private static function fail(string $msg): void
    {
        wp_die(esc_html($msg) . ' <a href="' . esc_url(wp_login_url()) . '">Back to login</a>', 'Login failed', ['response' => 403]);
    }

    private static function callback(): void
    {
        if (! empty($_GET['error'])) {
            self::fail('SSO error: ' . sanitize_text_field(wp_unslash($_GET['error'])));
        }
        $state = sanitize_text_field(wp_unslash($_GET['state'] ?? ''));
        $code = sanitize_text_field(wp_unslash($_GET['code'] ?? ''));
        $cookie = isset($_COOKIE[self::STATE_COOKIE]) && is_string($_COOKIE[self::STATE_COOKIE])
            ? sanitize_text_field(wp_unslash($_COOKIE[self::STATE_COOKIE]))
            : '';
        $saved = $state ? get_transient('avarewase_sso_' . $state) : false;
        // Single use: burn the state whatever happens next, and clear the browser binding.
        if ($state) {
            delete_transient('avarewase_sso_' . $state);
        }
        self::state_cookie('', time() - HOUR_IN_SECONDS);
        // The state must also match the cookie set in this browser (prevents login CSRF / code injection).
        if (! $saved || $code === '' || $cookie === '' || ! hash_equals($state, $cookie)) {
            self::fail('Invalid or expired login attempt.');
        }

        $tok = wp_remote_post(self::base() . '/oauth/token', [
            'timeout' => 10,
            'body' => [
                'grant_type' => 'authorization_code',
                'client_id' => self::cfg('client_id'),
                'client_secret' => self::cfg('client_secret'),
                'redirect_uri' => self::redirect_uri(),
                'code' => $code,
                'code_verifier' => $saved['v'],
            ],
        ]);
        $tok = is_wp_error($tok) ? null : json_decode(wp_remote_retrieve_body($tok), true);
        if (empty($tok['access_token'])) {
            self::fail('Could not obtain a token from the SSO server.');
        }

        $res = wp_remote_get(self::base() . '/api/userinfo', [
            'timeout' => 10,
            'headers' => ['Authorization' => 'Bearer ' . $tok['access_token'], 'Accept' => 'application/json'],
        ]);
        $body = is_wp_error($res) ? null : json_decode(wp_remote_retrieve_body($res), true);
        $info = $body['data'] ?? $body;
        if (empty($info['sub'])) {
            self::fail('Could not read your profile from the SSO server.');
        }

        $user = self::resolve_user($info);
        if (is_wp_error($user)) {
            self::fail($user->get_error_message());
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, (bool) apply_filters('avarewase_sso_remember', false, $user));
        do_action('wp_login', $user->user_login, $user);
        wp_safe_redirect($saved['to']);
        exit;
    }

    /** Match by sub, then by SSO-verified email; otherwise create. */
    private static function resolve_user(array $info)
    {
        $sub = (string) $info['sub'];
        $email = isset($info['email']) ? sanitize_email($info['email']) : '';
        $verified = ! empty($info['email_verified']);
        $name = sanitize_text_field($info['name'] ?? '');

        $found = get_users(['meta_key' => self::META_SUB, 'meta_value' => $sub, 'number' => 1]);
        $user = $found ? $found[0] : null;

        if (! $user && $email && $verified) {
            $user = get_user_by('email', $email) ?: null;
            if ($user) {
                $linked = get_user_meta($user->ID, self::META_SUB, true);
                if ($linked && $linked !== $sub) {
                    return new WP_Error('linked', 'This WordPress account is linked to a different SSO identity.');
                }
                // Never auto-link by email onto an administrative account; those must be linked deliberately.
                if (self::is_privileged($user) && ! apply_filters('avarewase_sso_allow_privileged_email_link', false, $user, $info)) {
                    return new WP_Error('privileged', 'Administrator accounts cannot be linked automatically. Ask a site administrator to link your account.');
                }
            }
        }

        if (! $user) {
            if (self::cfg('auto_create', '1') !== '1') {
                return new WP_Error('nouser', 'No WordPress account matches your Avarewase identity.');
            }
            if (! $email || ! $verified) {
                return new WP_Error('noemail', 'Your SSO profile needs a verified email address before an account can be created.');
            }
            $login = sanitize_user(strstr($email, '@', true), true) ?: 'sso_' . substr(md5($sub), 0, 8);
            $base = $login;
            for ($i = 1; username_exists($login); $i++) {
                $login = $base . $i;
            }
            $id = wp_insert_user([
                'user_login' => $login,
                'user_email' => $email,
                'display_name' => $name ?: $login,
                'user_pass' => wp_generate_password(40),
                'role' => self::default_role(),
            ]);
            if (is_wp_error($id)) {
                return $id;
            }
            $user = get_user_by('id', $id);
        }

        update_user_meta($user->ID, self::META_SUB, $sub);
        return $user;
    }
}

Avarewase_SSO_Login::init();
