# Laravel SSO Client Implementation TODO

## Phase 1: Configuration and Setup
- [ ] Create config/sso.php for SSO configuration
- [ ] Update .env.example with SSO environment variables
- [ ] Update User model to include membership_code, sub, and refresh_token fields
- [ ] Create migration to add membership_code to users table

## Phase 2: Core Services and Helpers
- [ ] Create app/Services/PKCEHelper.php for PKCE code_verifier and challenge generation
- [ ] Create app/Services/OAuth2Service.php for token exchange, JWKS verification, and token refresh
- [ ] Create app/Services/JWKService.php for JWKS handling and ID Token verification (RS256)

## Phase 3: Controllers and Middleware
- [ ] Create app/Http/Controllers/AuthController.php with loginRedirect, callback, logout methods
- [ ] Create app/Http/Middleware/EnsureAuthenticated.php for protecting routes

## Phase 4: Routes and Views
- [ ] Update routes/web.php to include SSO routes
- [ ] Create resources/views/auth/login.blade.php
- [ ] Create resources/views/auth/error.blade.php
- [ ] Create resources/views/dashboard.blade.php

## Phase 5: Testing and Refinement
- [ ] Test full login flow
- [ ] Test logout flow
- [ ] Implement automatic token refresh
- [ ] Add error handling and logging
- [ ] Optional: Create unit tests for AuthController
- [ ] Optional: Create integration test with mock SSO server

## Phase 6: Final Checks
- [ ] Ensure cross-domain compatibility
- [ ] Verify no reliance on SSO server cookies
- [ ] Test with different domains
