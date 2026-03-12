<?php
/**
 * Explore - Protected page with virtual scrolling card grid and search
 */
$activePage = 'explore';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Explore</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/dashboard_nav.php'; ?>

    <div class="container mt-5">
        <h1>Explore</h1>
        <p class="lead">Discover new content and recommendations.</p>

        <!-- Explore Search Bar -->
        <div class="explore-search-wrapper mb-4">
            <form class="d-flex" id="exploreSearchForm">
                <input class="form-control me-2" type="search" placeholder="Search artists, tracks..." aria-label="Search" id="exploreSearchInput" autocomplete="off">
                <button class="btn btn-light" type="submit">Search</button>
            </form>
            <div id="exploreSearchResults" class="search-results-dropdown"></div>
        </div>

        <!-- Virtual scroll container for cards -->
        <div id="virtualScrollViewport" class="virtual-scroll-viewport">
            <div id="virtualScrollSpacer" class="virtual-scroll-spacer">
                <div id="cardContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <!-- Cards are rendered dynamically by explore.js -->
                </div>
            </div>
        </div>
        <!-- Infinite scroll sentinel -->
        <div id="scrollSentinel" class="text-center py-3" style="display: none;">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading more...</span>
            </div>
        </div>
    </div>

    <script src="js/shared.js"></script>
    <script src="js/explore.js" defer></script>
    <script src="js/auth-guard.js" defer></script>
</body>
</html>
