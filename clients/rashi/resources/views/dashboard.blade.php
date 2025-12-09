<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Rashi Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="antialiased">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Rashi Application</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, {{ auth()->user()->name }} ({{ auth()->user()->email }})
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Rashi Application Dashboard</h2>
                    </div>
                    <div class="card-body">
                        <h4>Welcome to your Rashi dashboard!</h4>
                        <p>You are successfully authenticated via SSO.</p>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>User Information</h5>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><strong>Name:</strong> {{ auth()->user()->name }}</li>
                                            <li class="list-group-item"><strong>Email:</strong> {{ auth()->user()->email }}</li>
                                            <li class="list-group-item"><strong>SSO ID:</strong> {{ auth()->user()->sso_id }}</li>
                                            <li class="list-group-item"><strong>Created:</strong> {{ auth()->user()->created_at->format('M d, Y') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>SSO Status</h5>
                                        <p class="card-text">
                                            ✅ Successfully authenticated via SSO<br>
                                            🔐 Secure token-based authentication<br>
                                            🌐 Cross-application session sharing
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>Available Applications</h5>
                            <p>Since you're logged in via SSO, you can seamlessly access other applications:</p>
                            <div class="list-group">
                                <a href="http://localhost:8002" class="list-group-item list-group-item-action">
                                    <strong>Visits Application</strong> - Manage visits and appointments
                                </a>
                                <a href="http://localhost:8003" class="list-group-item list-group-item-action">
                                    <strong>Eftkad Application</strong> - Document management system
                                </a>
                            </div>
                            <small class="text-muted">Note: Other applications must be running on their respective ports.</small>
                        </div>

                        <div class="mt-4">
                            <h5>Test SSO Features</h5>
                            <div class="btn-group" role="group">
                                <a href="{{ route('logout') }}" class="btn btn-warning">Test Logout</a>
                                <button class="btn btn-info" onclick="testSession()">Check Session</button>
                                <button class="btn btn-secondary" onclick="showTokenInfo()">Token Info</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testSession() {
            fetch('/api/user', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                alert('Session Active: ' + JSON.stringify(data, null, 2));
            })
            .catch(error => {
                alert('Session check failed: ' + error.message);
            });
        }

        function showTokenInfo() {
            // This would show token information if available
            alert('Token information would be displayed here in a real implementation.');
        }
    </script>
</body>
</html>
