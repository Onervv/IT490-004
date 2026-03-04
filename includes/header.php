<?php
// header.php - shared <head> section for all pages
// Set the variable $pageTitle before including this file
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Untitled'; ?></title>

    <!-- favicons (only need to declare once) -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">

    <!-- Bootswatch Brite theme (green on white) for Bootstrap) -->
    <!-- local copy already includes the brite theme -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <!-- optional: if you need to pull from CDN instead, uncomment this -->
    <!--
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/brite/bootstrap.min.css" crossorigin="anonymous">
    -->

    <!-- navigation bar: custom green matching .btn-primary color -->
    <!-- custom styles are defined in assets/css/bootstrap.css -->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary-green">
        <div class="container">
            <a class="navbar-brand" href="home.php">AppName</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="getStartedDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Get Started
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="getStartedDropdown">
                            <li><a class="dropdown-item" href="login_page.php">Login</a></li>
                            <li><a class="dropdown-item" href="register_page.php">Register</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex navbar-custom-search" id="navSearchForm">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" id="navSearchInput">
                    <button class="btn btn-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    <script src="js/search.js"></script>
    <!-- bootstrap bundle required for dropdowns, toggler, etc. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<!-- make sure html/body are full-height so flexbox pages can push footer down -->
<style>
    html, body { height: 100%; }
</style>