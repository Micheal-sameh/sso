<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rashi Application - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center mb-0">Welcome to Rashi Application</h2>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="mb-4">SSO-Powered Application</h3>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                        <h5>Secure SSO</h5>
                                        <p class="text-muted">OAuth 2.0 + OpenID Connect authentication</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                                        <h5>Multi-App Access</h5>
                                        <p class="text-muted">Seamless access to all connected applications</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                                        <h5>JWT Security</h5>
                                        <p class="text-muted">RSA-signed tokens with PKCE protection</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4>Ready to Test SSO?</h4>
                            <p class="text-muted">Experience secure single sign-on across multiple applications</p>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-md-2">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Start SSO Login
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard (if logged in)
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="row text-start">
                            <div class="col-md-6">
                                <h5>SSO Features:</h5>
                                <ul class="list-unstyled">
                                    <li>✅ OAuth 2.0 Authorization Code Flow</li>
                                    <li>✅ OpenID Connect Support</li>
                                    <li>✅ PKCE (Proof Key for Code Exchange)</li>
                                    <li>✅ JWT Tokens with RSA Signing</li>
                                    <li>✅ Rate Limiting & Security</li>
                                    <li>✅ Cross-App Session Sharing</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Connected Apps:</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Rashi</strong> - Current Application</li>
                                    <li><a href="http://localhost:8002">Visits</a> - Appointment Management</li>
                                    <li><a href="http://localhost:8003">Eftkad</a> - Document System</li>
                                </ul>
                                <small class="text-muted">Note: Other apps must be running separately</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>
