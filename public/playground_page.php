<?php
/**
 * Playground - Protected page
 */
$activePage = 'playground';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Playground</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/dashboard_nav.php'; ?>

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
    
    <script src="js/auth-guard.js" defer></script>
</body>
</html>
