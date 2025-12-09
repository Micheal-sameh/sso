<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">{{ config('app.name', 'Laravel') }}</a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('auth.logout') }}" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Welcome to your Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <h4>Authentication Successful!</h4>
                        <p>You have been successfully authenticated via SSO.</p>

                        @if(session('user'))
                            <div class="alert alert-success">
                                <h5>User Information:</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Name:</strong> {{ session('user')->name ?? 'N/A' }}</li>
                                    <li><strong>Email:</strong> {{ session('user')->email ?? 'N/A' }}</li>
                                    <li><strong>Membership Code:</strong> {{ session('user')->membership_code ?? 'N/A' }}</li>
                                </ul>
                            </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('auth.logout') }}" class="btn btn-danger">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
