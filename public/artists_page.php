<?php
/**
 * Artists - Protected page showing user's liked/starred items
 */
$activePage = 'artists';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Artists</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/dashboard_nav.php'; ?>

    <div class="container mt-5">
        <h1>Your Favorites</h1>
        <p class="lead">Artists you've starred from the Explore page.</p>

        <div id="artistsEmpty" class="alert alert-info mt-3" style="display: none;">
            You haven't starred anything yet. Head over to <a href="explore_page.php">Explore</a> and hit the &#9734; on cards you like!
        </div>
        
        <div id="artistsCardContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-3">
            <!-- Liked cards rendered by artists.js -->
        </div>
    </div>

    <script src="js/shared.js"></script>
    <script src="js/artists.js" defer></script>
    <script src="js/auth-guard.js" defer></script>
</body>
</html>
