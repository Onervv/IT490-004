<?php
$pageTitle = "About";
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">

        <!-- ══ Hero Banner ═══════════════════════════════════════ -->
        <section class="bg-primary bg-gradient text-dark py-5 text-center">
            <div class="container py-4">
                <h1 class="display-4 fw-bold">About Us</h1>
                <p class="lead col-lg-7 mx-auto mt-3">Learn more about our team and the technology behind M3USIC.</p>
            </div>
        </section>

        <!-- ══ Project Overview ══════════════════════════════════ -->
        <section class="py-5">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7">
                        <h2 class="fw-bold">IT490 System Integration Project</h2>
                        <p class="fs-5 text-muted">This project demonstrates a web application with distributed architecture using RabbitMQ for message-based communication between frontend and backend services.</p>
                        <p class="text-muted">M3USIC is a music discovery platform where users can explore artists and tracks, star their favorites, and manage their personal collection &mdash; all powered by a modern distributed backend.</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="card border-primary">
                            <div class="card-header">Tech Stack</div>
                            <div class="card-body">
                                <h4 class="card-title">Built With</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">PHP &amp; Bootstrap (Brite Theme)</li>
                                    <li class="list-group-item">RabbitMQ Message Queue</li>
                                    <li class="list-group-item">MySQL &amp; MongoDB</li>
                                    <li class="list-group-item">Session-Based Authentication</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ What We Do ════════════════════════════════════════ -->
        <section class="bg-body-tertiary py-5">
            <div class="container">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">What We Work On</h2>
                    <p class="text-muted">Core system integration concepts our team tackles.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-info h-100">
                            <div class="card-header">Frontend</div>
                            <div class="card-body">
                                <h5 class="card-title">Web Development</h5>
                                <p class="card-text">Building responsive pages with PHP and Bootstrap's Brite theme.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-success h-100">
                            <div class="card-header">Messaging</div>
                            <div class="card-body">
                                <h5 class="card-title">RabbitMQ</h5>
                                <p class="card-text">Message queue integration for reliable backend communication.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-warning h-100">
                            <div class="card-header">Data</div>
                            <div class="card-body">
                                <h5 class="card-title">Database Management</h5>
                                <p class="card-text">Session handling and data persistence with MySQL and MongoDB.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-danger h-100">
                            <div class="card-header">Architecture</div>
                            <div class="card-body">
                                <h5 class="card-title">Distributed Systems</h5>
                                <p class="card-text">Designing scalable, decoupled services that communicate asynchronously.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ CTA ═══════════════════════════════════════════════ -->
        <section class="bg-success bg-gradient text-dark py-5 text-center">
            <div class="container py-3">
                <h2 class="fw-bold display-6">Want to check it out?</h2>
                <p class="mt-2 mb-4 fs-5">See our features or create an account to start exploring.</p>
                <a href="features_page.php" class="btn btn-light btn-lg rounded-pill px-4 me-2">View Features</a>
                <a href="register_page.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Get Started</a>
            </div>
        </section>

    </main>

<?php
include __DIR__ . '/../includes/footer.php';
?>
