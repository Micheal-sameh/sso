<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authentication Error - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title text-center">Authentication Error</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <strong>Error:</strong> {{ session('error', 'An unknown authentication error occurred.') }}
                        </div>
                        <div class="text-center">
                            <a href="{{ route('auth.login') }}" class="btn btn-primary">
                                Try Again
                            </a>
                            <a href="/" class="btn btn-secondary ms-2">
                                Go Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
