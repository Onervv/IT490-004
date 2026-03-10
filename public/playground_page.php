<?php
/**
 * Playground - Protected page
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Playground</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
</head>
<body>
    <!-- Dashboard Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary-green">
        <div class="container">
            <a class="navbar-brand" href="dashboard_page.php">AppName</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav" aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="dashboardNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard_page.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="artists_page.php">Artists</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tracks_page.php">Tracks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="playground_page.php">Playground</a>
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

    <div class="container mt-5">
        <h1>Playground</h1>
        <p class="lead">Experiment and test features here.</p>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-3">
            <div class="col">
                <div class="card bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Feature 1</h5>
                        <p class="card-text">Test feature description</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Feature 2</h5>
                        <p class="card-text">Test feature description</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Feature 3</h5>
                        <p class="card-text">Test feature description</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title">Feature 4</h5>
                        <p class="card-text">Test feature description</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        (function() {
            const sessionKey = sessionStorage.getItem('session_key');
            if (!sessionKey) {
                window.location.href = 'login_page.php';
                return;
            }
            
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
