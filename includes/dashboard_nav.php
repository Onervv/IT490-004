<?php
/**
 * dashboard_nav.php - Shared navbar for all authenticated (dashboard) pages.
 *
 * Usage: Set $activePage before including, e.g.:
 *   $activePage = 'explore';
 *   include __DIR__ . '/../includes/dashboard_nav.php';
 *
 * Valid $activePage values: dashboard, artists, tracks, playground, explore
 */
$activePage = $activePage ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-primary-green">
    <div class="container">
        <a class="navbar-brand" href="dashboard_page.php">M3USIC</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav" aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="dashboardNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard_page.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'artists' ? 'active' : '' ?>" href="artists_page.php">Artists</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'tracks' ? 'active' : '' ?>" href="tracks_page.php">Tracks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'playground' ? 'active' : '' ?>" href="playground_page.php">Playground</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'explore' ? 'active' : '' ?>" href="explore_page.php">Explore</a>
                </li>
            </ul>
            <a href="#" id="logoutBtn" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
