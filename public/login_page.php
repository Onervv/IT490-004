<?php
$pageTitle = "Login Portal";
// include header from central includes directory
include __DIR__ . '/../includes/header.php';
?>

<body class="d-flex flex-column min-vh-100">

    <main class="flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">

                <div class="card">
                    <div class="card-header">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <form id="loginForm" novalidate>
                            <div class="mb-3">
                                <label for="usernameInput" class="form-label">Username</label>
                                <input type="text" class="form-control" id="usernameInput" placeholder="Enter username" required>
                                <div class="invalid-feedback">Please enter your username.</div>
                            </div>

                            <div class="mb-3">
                                <label for="passwordInput" class="form-label">Password</label>
                                <input type="password" class="form-control" id="passwordInput" placeholder="Enter password" required>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>

                            <button id="loginBtn" type="button" class="btn btn-primary">Sign In</button>

                            <div id="textResponse" class="mt-3 text-muted">
                                awaiting response...
                            </div>
                        </form>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </main>

    <script src="js/login.js"></script>

<?php
include __DIR__ . '/../includes/footer.php';
?>