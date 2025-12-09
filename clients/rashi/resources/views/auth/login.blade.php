<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Rashi Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">Login to Rashi Application</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>SSO Authentication:</strong> You will be redirected to the SSO server for secure authentication.
                        </div>

                        <div class="text-center mb-4">
                            <h5>SSO Login Process:</h5>
                            <ol class="text-start">
                                <li>Click "Login with SSO" below</li>
                                <li>You'll be redirected to the SSO server</li>
                                <li>Enter your credentials (if not already logged in)</li>
                                <li>You'll be redirected back here automatically</li>
                                <li>Access all connected applications seamlessly</li>
                            </ol>
                        </div>

                        <div class="d-grid">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login with SSO
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <h6>SSO Features:</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">
                                        🔐 OAuth 2.0 + OIDC<br>
                                        🛡️ PKCE Protection<br>
                                        🔑 JWT Tokens
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        🌐 Cross-App Access<br>
                                        ⚡ Single Sign-On<br>
                                        🚀 Seamless Experience
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="/" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-home me-1"></i>
                                Back to Home
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Testing Information</h6>
                        <small class="text-muted">
                            <strong>SSO Server:</strong> http://localhost:8000<br>
                            <strong>Rashi App:</strong> http://localhost:8001<br>
                            <strong>Test User:</strong> test@example.com / password
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>
