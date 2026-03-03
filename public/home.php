<?php
$pageTitle = "Welcome";
// header.php moved into parent includes directory
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <!-- features section -->
        <section id="features" class="py-5">
            <div class="container">
                <h2>Features</h2>
                <p>Describe your application's key features here.</p>
            </div>
        </section>

        <!-- about section -->
        <section id="about" class="py-5 bg-light">
            <div class="container">
                <h2>About Us</h2>
                <p>Some information about your team or company.</p>
            </div>
        </section>

        <div class="container py-4">
            <p><!-- placeholder: intro text about your app --></p>
            <div class="btn-group mt-3" role="group">
                <a href="login_page.php" class="btn btn-primary">Go to Login</a>
                <a href="register.php" class="btn btn-success">Register</a>
            </div>
        </div>
    </main>

<?php
// include footer to close body/html
include __DIR__ . '/../includes/footer.php';
?>