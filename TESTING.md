# SSO System Testing Guide

## Prerequisites

1. **Install Dependencies:**
   ```bash
   # SSO Server
   composer install
   npm install

   # Client Apps (repeat for each client)
   cd clients/rashi && composer install && npm install
   cd ../visits && composer install && npm install
   cd ../eftkad && composer install && npm install
   ```

2. **Environment Setup:**
   ```bash
   # SSO Server
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan passport:install

   # Client Apps
   cd clients/rashi && cp .env.example .env && php artisan key:generate && php artisan migrate
   cd ../visits && cp .env.example .env && php artisan key:generate && php artisan migrate
   cd ../eftkad && cp .env.example .env && php artisan key:generate && php artisan migrate
   ```

3. **Configure Environment Variables:**

   **SSO Server (.env):**
   ```env
   APP_URL=http://localhost:8000
   SSO_ISSUER=http://localhost:8000
   SSO_ENFORCE_HTTPS=false

   # Client configurations
   RASHI_CLIENT_ID=rashi_client_id
   RASHI_CLIENT_SECRET=rashi_client_secret
   RASHI_REDIRECT_URI=http://localhost:8001/callback

   VISITS_CLIENT_ID=visits_client_id
   VISITS_CLIENT_SECRET=visits_client_secret
   VISITS_REDIRECT_URI=http://localhost:8002/callback

   EFTKAD_CLIENT_ID=eftkad_client_id
   EFTKAD_CLIENT_SECRET=eftkad_client_secret
   EFTKAD_REDIRECT_URI=http://localhost:8003/callback
   ```

   **Rashi Client (.env):**
   ```env
   APP_URL=http://localhost:8001
   SSO_BASE_URL=http://localhost:8000
   SSO_CLIENT_ID=rashi_client_id
   SSO_CLIENT_SECRET=rashi_client_secret
   SSO_REDIRECT_URI=http://localhost:8001/callback
   ```

   **Visits Client (.env):**
   ```env
   APP_URL=http://localhost:8002
   SSO_BASE_URL=http://localhost:8000
   SSO_CLIENT_ID=visits_client_id
   SSO_CLIENT_SECRET=visits_client_secret
   SSO_REDIRECT_URI=http://localhost:8002/callback
   ```

   **Eftkad Client (.env):**
   ```env
   APP_URL=http://localhost:8003
   SSO_BASE_URL=http://localhost:8000
   SSO_CLIENT_ID=eftkad_client_id
   SSO_CLIENT_SECRET=eftkad_client_secret
   SSO_REDIRECT_URI=http://localhost:8003/callback
   ```

4. **Create OAuth Clients in SSO Server:**
   ```bash
   php artisan tinker
   ```
   ```php
   use Laravel\Passport\Client;

   // Create clients for each application
   Client::create([
       'name' => 'Rashi App',
       'redirect' => 'http://localhost:8001/callback',
       'personal_access_client' => false,
       'password_client' => false,
       'revoked' => false,
   ]);

   Client::create([
       'name' => 'Visits App',
       'redirect' => 'http://localhost:8002/callback',
       'personal_access_client' => false,
       'password_client' => false,
       'revoked' => false,
   ]);

   Client::create([
       'name' => 'Eftkad App',
       'redirect' => 'http://localhost:8003/callback',
       'personal_access_client' => false,
       'password_client' => false,
       'revoked' => false,
   ]);
   ```

5. **Create Test User:**
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;

   User::create([
       'name' => 'Test User',
       'email' => 'test@example.com',
       'password' => bcrypt('password'),
       'email_verified_at' => now(),
   ]);
   ```

## Testing Scenarios

### 1. Basic SSO Login Flow

**Test Steps:**
1. Start all servers:
   ```bash
   # Terminal 1 - SSO Server
   php artisan serve --host=localhost --port=8000

   # Terminal 2 - Rashi App
   cd clients/rashi && php artisan serve --host=localhost --port=8001

   # Terminal 3 - Visits App
   cd clients/visits && php artisan serve --host=localhost --port=8002

   # Terminal 4 - Eftkad App
   cd clients/eftkad && php artisan serve --host=localhost --port=8003
   ```

2. Open browser and visit: `http://localhost:8001/login`
3. Should redirect to SSO login: `http://localhost:8000/login?client_id=rashi_client_id&...`
4. Login with test@example.com / password
5. Should redirect back to Rashi app dashboard
6. Visit `http://localhost:8002/login` - should auto-login without credentials
7. Visit `http://localhost:8003/login` - should auto-login without credentials

**Expected Results:**
- First login requires credentials
- Subsequent app logins are seamless
- User session is shared across all apps

### 2. OAuth2 Authorization Code Flow Test

**Using cURL:**
```bash
# 1. Initiate authorization request
curl -X GET "http://localhost:8000/oauth/authorize?client_id=rashi_client_id&redirect_uri=http://localhost:8001/callback&response_type=code&scope=openid%20profile%20email&state=test_state"

# Should redirect to login page (302 response)

# 2. After login, exchange code for tokens
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=authorization_code&client_id=rashi_client_id&client_secret=rashi_client_secret&redirect_uri=http://localhost:8001/callback&code=YOUR_AUTH_CODE"

# Expected response:
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAi...",
  "refresh_token": "def50200...",
  "id_token": "eyJ0eXAi..."
}
```

### 3. OpenID Connect Discovery Test

```bash
# Get OIDC configuration
curl http://localhost:8000/.well-known/openid-configuration

# Expected response includes:
{
  "issuer": "http://localhost:8000",
  "authorization_endpoint": "http://localhost:8000/oauth/authorize",
  "token_endpoint": "http://localhost:8000/oauth/token",
  "userinfo_endpoint": "http://localhost:8000/oauth/userinfo",
  "jwks_uri": "http://localhost:8000/.well-known/jwks.json",
  ...
}
```

### 4. JWKS (JSON Web Key Set) Test

```bash
# Get public keys
curl http://localhost:8000/.well-known/jwks.json

# Expected response:
{
  "keys": [
    {
      "kty": "RSA",
      "use": "sig",
      "kid": "rsa-1234567890",
      "n": "base64_encoded_modulus",
      "e": "AQAB",
      "alg": "RS256"
    }
  ]
}
```

### 5. User Info Endpoint Test

```bash
# Get user info with access token
curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
     http://localhost:8000/oauth/userinfo

# Expected response:
{
  "sub": "1",
  "name": "Test User",
  "email": "test@example.com",
  "email_verified": true
}
```

### 6. Token Refresh Test

```bash
# Refresh access token
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=refresh_token&client_id=rashi_client_id&client_secret=rashi_client_secret&refresh_token=YOUR_REFRESH_TOKEN"

# Should return new access and refresh tokens
```

### 7. Token Revocation Test

```bash
# Revoke access token
curl -X POST http://localhost:8000/oauth/revoke \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "token=YOUR_ACCESS_TOKEN&client_id=rashi_client_id&client_secret=rashi_client_secret"

# Subsequent requests with revoked token should fail
```

### 8. Global Logout Test

1. Login to all apps
2. Logout from one app (e.g., Rashi)
3. Try accessing other apps - should require re-login

### 9. Error Handling Tests

**Invalid Client:**
```bash
curl "http://localhost:8000/oauth/authorize?client_id=invalid&redirect_uri=http://localhost:8001/callback&response_type=code"
# Should return error=invalid_client
```

**Invalid Redirect URI:**
```bash
curl "http://localhost:8000/oauth/authorize?client_id=rashi_client_id&redirect_uri=http://evil.com&response_type=code"
# Should return error=invalid_request
```

**Rate Limiting Test:**
```bash
# Make multiple rapid requests to trigger rate limiting
for i in {1..70}; do
  curl -s "http://localhost:8000/oauth/authorize?client_id=rashi_client_id&redirect_uri=http://localhost:8001/callback&response_type=code" &
done

# Should return 429 Too Many Requests after limit
```

### 10. PKCE (Proof Key for Code Exchange) Test

```bash
# Generate code verifier and challenge
CODE_VERIFIER="dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk"
CODE_CHALLENGE="E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM"

# Authorization request with PKCE
curl "http://localhost:8000/oauth/authorize?client_id=rashi_client_id&redirect_uri=http://localhost:8001/callback&response_type=code&scope=openid&code_challenge=$CODE_CHALLENGE&code_challenge_method=S256"

# Token request with code verifier
curl -X POST http://localhost:8000/oauth/token \
  -d "grant_type=authorization_code&client_id=rashi_client_id&client_secret=rashi_client_secret&redirect_uri=http://localhost:8001/callback&code=YOUR_CODE&code_verifier=$CODE_VERIFIER"
```

## Automated Testing

### Unit Tests

```bash
# Run SSO Server tests
php artisan test

# Run client app tests
cd clients/rashi && php artisan test
```

### Integration Tests

Create a test file `tests/Feature/SSOIntegrationTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SSOIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_authorization_flow()
    {
        // Test complete OAuth flow
        $response = $this->get('/oauth/authorize?' . http_build_query([
            'client_id' => 'rashi_client_id',
            'redirect_uri' => 'http://localhost:8001/callback',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => 'test_state'
        ]));

        $response->assertRedirect();
    }

    public function test_oidc_discovery()
    {
        $response = $this->get('/.well-known/openid-configuration');

        $response->assertOk()
                ->assertJsonStructure([
                    'issuer',
                    'authorization_endpoint',
                    'token_endpoint',
                    'userinfo_endpoint',
                    'jwks_uri'
                ]);
    }

    public function test_jwks_endpoint()
    {
        $response = $this->get('/.well-known/jwks.json');

        $response->assertOk()
                ->assertJsonStructure([
                    'keys' => [
                        '*' => [
                            'kty',
                            'use',
                            'kid',
                            'n',
                            'e',
                            'alg'
                        ]
                    ]
                ]);
    }
}
```

## Performance Testing

### Load Testing with Apache Bench

```bash
# Test authorization endpoint
ab -n 1000 -c 10 "http://localhost:8000/oauth/authorize?client_id=rashi_client_id&redirect_uri=http://localhost:8001/callback&response_type=code"

# Test token endpoint
ab -n 1000 -c 10 -p token_post_data.txt -T application/x-www-form-urlencoded http://localhost:8000/oauth/token
```

### Memory Usage Monitoring

```bash
# Monitor PHP memory usage during requests
php -r "
\$start = memory_get_peak_usage();
for(\$i = 0; \$i < 100; \$i++) {
    file_get_contents('http://localhost:8000/.well-known/jwks.json');
}
\$end = memory_get_peak_usage();
echo 'Memory usage: ' . (\$end - \$start) . ' bytes\n';
"
```

## Security Testing

### Penetration Testing Checklist

- [ ] Test for SQL injection in all endpoints
- [ ] Test for XSS in login forms
- [ ] Test for CSRF vulnerabilities
- [ ] Verify HTTPS enforcement
- [ ] Test token leakage prevention
- [ ] Verify PKCE implementation
- [ ] Test rate limiting effectiveness
- [ ] Check for information disclosure

### SSL/TLS Testing

```bash
# Test SSL configuration (when HTTPS is enabled)
openssl s_client -connect localhost:443 -servername localhost

# Check certificate validity
openssl x509 -in ssl/cert.pem -text -noout
```

## Troubleshooting

### Common Issues

1. **"Invalid client" error:**
   - Check client_id and client_secret in .env files
   - Verify client exists in oauth_clients table

2. **"Invalid redirect URI" error:**
   - Ensure redirect URI matches exactly in database
   - Check for trailing slashes

3. **PKCE verification fails:**
   - Verify code_challenge_method is 'S256'
   - Check code_verifier length (43-128 characters)

4. **JWT signature verification fails:**
   - Ensure JWKS endpoint is accessible
   - Check RSA key generation

### Debug Commands

```bash
# Check database tables
php artisan tinker
>>> \DB::table('oauth_clients')->get()
>>> \DB::table('users')->get()

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check routes
php artisan route:list --path=oauth

# Monitor logs
tail -f storage/logs/laravel.log
```

## Production Readiness Checklist

- [ ] All tests pass
- [ ] HTTPS configured and enforced
- [ ] Rate limiting tuned for production load
- [ ] Database optimized and indexed
- [ ] Caching configured (Redis recommended)
- [ ] Monitoring and alerting set up
- [ ] Backup strategy implemented
- [ ] Security headers configured
- [ ] SSL certificate valid and current
