<?php
/**
 * Dashboard - Protected page that validates sessions via RabbitMQ
 */

// Handle AJAX validation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate') {
    header('Content-Type: application/json');
    
    $sessionKey = $_POST['session_key'] ?? '';
    
    if (empty($sessionKey)) {
        echo json_encode(['status' => 'error', 'message' => 'no session key']);
        exit(0);
    }
    
    // Include RabbitMQ client
    require_once __DIR__ . '/../includes/path.inc';
    require_once __DIR__ . '/../includes/get_host_info.inc';
    require_once __DIR__ . '/../includes/rabbitMQLib.inc';
    
    try {
        $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
        $request = [
            'type' => 'validate_session',
            'session_key' => $sessionKey
        ];
        $response = $client->send_request($request);
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'validation failed']);
    }
    exit(0);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
</head>
<body>
    <!-- Dashboard Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary-green">
        <div class="container">
            <a class="navbar-brand" href="dashboard_page.php">M3SIC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav" aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="dashboardNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard_page.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="artists_page.php">Artists</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tracks_page.php">Tracks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="playground_page.php">Playground</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="explore_page.php">Explore</a>
                    </li>
                </ul>
                <a href="#" id="logoutBtn" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <div id="loading" class="container mt-5">
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Validating session...</p>
        </div>
    </div>
    
    <div id="dashboard" class="container mt-5" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Welcome, <span id="usernameDisplay"></span></h1>
        </div>
        
        <div class="alert alert-success">
            <strong>Session Valid</strong> - You are authenticated.
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <div class="col">
                <div class="card bg-primary h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Primary card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-secondary h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Secondary card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-success h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Success card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-danger h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Danger card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-warning h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Warning card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-info h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Info card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-light h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Light card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card text-white bg-dark h-100">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h4 class="card-title">Dark card title</h4>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        (function() {
            const sessionKey = sessionStorage.getItem('session_key');
            const username = sessionStorage.getItem('username');
            
            if (!sessionKey) {
                window.location.href = 'login_page.php';
                return;
            }
            
            // Validate session with backend
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 10000);
            
            const data = new URLSearchParams({
                action: 'validate',
                session_key: sessionKey
            });
            
            fetch('dashboard_page.php', {
                method: 'POST',
                body: data,
                signal: controller.signal
            })
            .then(response => response.json())
            .then(result => {
                clearTimeout(timeout);
                if (result.status === 'ok') {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('dashboard').style.display = 'block';
                    document.getElementById('usernameDisplay').textContent = result.username || username || 'User';
                } else {
                    sessionStorage.removeItem('session_key');
                    sessionStorage.removeItem('username');
                    window.location.href = 'login_page.php';
                }
            })
            .catch((err) => {
                clearTimeout(timeout);
                sessionStorage.removeItem('session_key');
                sessionStorage.removeItem('username');
                window.location.href = 'login_page.php';
            });
            
            // Logout handler
            document.getElementById('logoutBtn').addEventListener('click', function(e) {
                e.preventDefault();
                sessionStorage.removeItem('session_key');
                sessionStorage.removeItem('username');
                window.location.href = 'login_page.php';
            });
        })();
    </script>
</body>
</html>
