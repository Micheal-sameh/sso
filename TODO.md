# SSO System Development Plan

## Part A: SSO Server (Laravel 11)
- [x] Update composer.json to Laravel 11 and Passport
- [x] Install dependencies (composer install)
- [x] Configure Passport in config/auth.php and app/Providers/AuthServiceProvider.php
- [x] Create database migrations for OAuth2 tables (clients, auth_codes, access_tokens, refresh_tokens)
- [x] Update User model to use Passport traits
- [x] Create JWKS service for OIDC
- [x] Create PKCE helper service
- [x] Create OAuth2 service for token management
- [x] Create AuthController for login, consent, logout
- [x] Create OAuth2Controller for OAuth2 endpoints
- [x] Create middleware for authentication and CSRF
- [x] Create blade templates for login, consent, error pages
- [x] Implement OAuth2 endpoints: /oauth/authorize, /oauth/token, /oauth/revoke, /oauth/userinfo
- [x] Implement OIDC endpoints: /.well-known/jwks.json, /.well-known/openid-configuration
- [x] Add rate limiting and HTTPS enforcement
- [x] Create config/sso.php for SSO settings
- [x] Update routes/web.php and routes/api.php
- [x] Create example .env file

## Part B: Client Web Apps (Rashi, Visits, Eftkad)
- [x] Create separate Laravel projects for each client
- [x] For each client:
  - [x] Install Socialite
  - [x] Create custom SSO provider class
  - [x] Configure services.php for SSO
  - [x] Create routes for /login and /callback
  - [x] Create controllers for authentication
  - [x] Implement middleware for protection
  - [x] Handle local user storage and session management
  - [x] Implement local logout redirecting to SSO logout

## Part C: Documentation
- [x] Generate sequence diagrams for login flow
- [x] Explain PKCE mechanism
- [x] Document token refresh process
- [x] Describe cross-app SSO functionality
- [x] Detail global logout implementation
- [x] Create architecture diagrams (ASCII/Mermaid)

## General
- [x] Ensure all code follows security best practices
- [x] Add comprehensive comments and docblocks
- [x] Test all endpoints and flows
- [x] Create setup guide
- [x] Document environment variables
