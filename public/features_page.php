<?php
$pageTitle = "Features";
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <!-- ══ Feature Cards ═════════════════════════════════════ -->
        <section class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">What M3USIC Offers</h2>
                    <p class="text-muted">Built for music lovers, powered by modern tech.</p>
                </div>

                <div class="row g-3">

                    <!-- 1 ─ Explore & Discover -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-primary h-100">
                            <div class="card-header">Explore &amp; Discover</div>
                            <div class="card-body">
                                <h4 class="card-title">Explore &amp; Discover</h4>
                                <p class="card-text">Browse an ever-growing library of artists and tracks with our infinite-scroll card grid. Search in real-time and find exactly what you're looking for.</p>
                                <span class="badge rounded-pill bg-light text-dark">Virtual Scroll</span>
                                <span class="badge rounded-pill bg-light text-dark">Live Search</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2 ─ Star Your Favorites -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-success h-100">
                            <div class="card-header">Star Your Favorites</div>
                            <div class="card-body">
                                <h4 class="card-title">Star Your Favorites</h4>
                                <p class="card-text">Hit the star on any artist or track card to save it. Your favorites are always one click away on the Artists page &mdash; your personal collection.</p>
                                <span class="badge rounded-pill bg-light text-dark">One-Click Save</span>
                                <span class="badge rounded-pill bg-light text-dark">Persistent</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3 ─ Browse Tracks -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-danger h-100">
                            <div class="card-header">Browse Tracks</div>
                            <div class="card-body">
                                <h4 class="card-title">Browse Tracks</h4>
                                <p class="card-text">Dive into a curated grid of tracks. Each card gives you a quick look at what's playing &mdash; discover new music effortlessly.</p>
                                <span class="badge rounded-pill bg-light text-dark">Card Grid</span>
                                <span class="badge rounded-pill bg-light text-dark">Responsive</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 ─ Secure Authentication -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-warning h-100">
                            <div class="card-header">Secure Authentication</div>
                            <div class="card-body">
                                <h4 class="card-title">Secure Authentication</h4>
                                <p class="card-text">Sign up or log in with confidence. Sessions are validated in real-time through our backend message queue &mdash; no shortcuts on security.</p>
                                <span class="badge rounded-pill bg-light text-dark">Session Guard</span>
                                <span class="badge rounded-pill bg-light text-dark">RabbitMQ</span>
                            </div>
                        </div>
                    </div>

                    <!-- 5 ─ Personal Dashboard -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-info h-100">
                            <div class="card-header">Personal Dashboard</div>
                            <div class="card-body">
                                <h4 class="card-title">Personal Dashboard</h4>
                                <p class="card-text">Your home base after logging in. The dashboard greets you by name and keeps your session alive with automatic validation.</p>
                                <span class="badge rounded-pill bg-light text-dark">Protected</span>
                                <span class="badge rounded-pill bg-light text-dark">Personalized</span>
                            </div>
                        </div>
                    </div>

                    <!-- 6 ─ Instant Search -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-dark h-100">
                            <div class="card-header">Instant Search</div>
                            <div class="card-body">
                                <h4 class="card-title">Instant Search</h4>
                                <p class="card-text">Global search in the navbar plus a dedicated explore search bar with a live dropdown &mdash; find artists and tracks as you type.</p>
                                <span class="badge rounded-pill bg-light text-dark">Autocomplete</span>
                                <span class="badge rounded-pill bg-light text-dark">Global</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- ══ CTA ═══════════════════════════════════════════════ -->
        <section class="bg-success bg-gradient text-dark py-5 text-center">
            <div class="container py-3">
                <h2 class="fw-bold display-6">Ready to explore?</h2>
                <p class="mt-2 mb-4 fs-5">Create an account and start discovering music in seconds.</p>
                <a href="register_page.php" class="btn btn-light btn-lg rounded-pill px-4 me-2">Get Started</a>
                <a href="login_page.php" class="btn btn-light btn-lg rounded-pill px-4">Login</a>
            </div>
        </section>

    </main>

<?php
include __DIR__ . '/../includes/footer.php';
?>
