<?php
$pageTitle = "Welcome";
// header.php moved into parent includes directory
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <!-- TODO: update this to show the correct landing page content -->
        <section id="features" class="py-5">
            <div class="container">
                <h2>Features</h2>
                <p>Describe application's key features here.</p>
            </div>
        </section>

        <!-- about section -->
        <section id="about" class="py-5 bg-light">
            <div class="container">
                <h2>About Us</h2>
                <p>Random filler information about the app when we come up with an idea.</p>
            </div>
        </section>

        <div class="container py-4">
            <p>Sign up today or login if your already have an account.</p>
            <div class="btn-group mt-3" role="group">
                <a href="login_page.php" class="btn btn-primary">Go to Login</a>
                <a href="register_page.php" class="btn btn-success">Register</a>
            </div>
        </div>
    </main>

<?php
// include footer to close body/html
include __DIR__ . '/../includes/footer.php';
?>