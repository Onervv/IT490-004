<?php
$pageTitle = "Features";
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <section class="py-5">
            <div class="container">
                <h1>Features</h1>
                <p class="lead">Discover what our application has to offer.</p>
                
                <div class="row mt-4">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Secure Authentication</h5>
                                <p class="card-text">User login and registration with session-based authentication via RabbitMQ.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Dashboard</h5>
                                <p class="card-text">Protected dashboard with real-time session validation.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Message Queue</h5>
                                <p class="card-text">RabbitMQ integration for reliable backend communication.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php
include __DIR__ . '/../includes/footer.php';
?>
