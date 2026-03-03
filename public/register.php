<?php
$pageTitle = "Register";
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Create an Account</h3>
                    </div>
                    <div class="card-body">
                        <form id="registerForm" novalidate>
                            <div class="mb-3">
                                <label for="regUsername" class="form-label">Username</label>
                                <input type="text" class="form-control" id="regUsername" placeholder="Choose a username" required>
                                <div class="invalid-feedback">Username must be at least 3 characters long.</div>
                            </div>

                            <div class="mb-3">
                                <label for="regPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="regPassword" placeholder="Enter password" required>
                                <div class="invalid-feedback">Password must be at least 8 characters and include a number.</div>
                            </div>

                            <div class="mb-3">
                                <label for="regConfirm" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="regConfirm" placeholder="Re-enter password" required>
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>

                            <button type="submit" class="btn btn-success btn-block">Register</button>
                        </form>
                        <p class="mt-3 text-center"><a href="login_page.php">Already have an account? Sign in</a></p>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/register.js"></script>

<?php
include __DIR__ . '/../includes/footer.php';
?>
