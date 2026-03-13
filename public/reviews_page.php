<?php
/**
 * Reviews - Protected page for creating and browsing user reviews.
 * Two tabs: "My Reviews" (user's own) and "Search Reviews" (all users).
 * All data stored on the DB VM via RabbitMQ.
 */
$activePage = 'reviews';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reviews</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/dashboard_nav.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4">Reviews</h1>

        <!-- Tab navigation -->
        <ul class="nav nav-tabs mb-4" id="reviewTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="my-reviews-tab" data-bs-toggle="tab"
                        data-bs-target="#myReviews" type="button" role="tab"
                        aria-controls="myReviews" aria-selected="true">My Reviews</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="search-reviews-tab" data-bs-toggle="tab"
                        data-bs-target="#searchReviews" type="button" role="tab"
                        aria-controls="searchReviews" aria-selected="false">Search Reviews</button>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content" id="reviewTabContent">

            <!-- ═══ My Reviews Tab ═══ -->
            <div class="tab-pane fade show active" id="myReviews" role="tabpanel" aria-labelledby="my-reviews-tab">

                <!-- Write a new review -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Write a Review</h5>
                        <form id="createReviewForm">
                            <div class="mb-3">
                                <label for="reviewSubject" class="form-label">Artist or Track Name</label>
                                <input type="text" class="form-control" id="reviewSubject"
                                       placeholder="e.g. Kendrick Lamar, Bohemian Rhapsody" required>
                            </div>
                            <div class="mb-3">
                                <label for="reviewRating" class="form-label">Rating</label>
                                <select class="form-select" id="reviewRating" required>
                                    <option value="">Choose...</option>
                                    <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5)</option>
                                    <option value="4">&#9733;&#9733;&#9733;&#9733;&#9734; (4)</option>
                                    <option value="3">&#9733;&#9733;&#9733;&#9734;&#9734; (3)</option>
                                    <option value="2">&#9733;&#9733;&#9734;&#9734;&#9734; (2)</option>
                                    <option value="1">&#9733;&#9734;&#9734;&#9734;&#9734; (1)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="reviewText" class="form-label">Your Review</label>
                                <textarea class="form-control" id="reviewText" rows="4"
                                          placeholder="Share your thoughts..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitReviewBtn">Submit Review</button>
                        </form>
                    </div>
                </div>

                <!-- My reviews list -->
                <h5 class="mb-3">Your Past Reviews</h5>
                <div id="myReviewsList">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading your reviews...</p>
                    </div>
                </div>
            </div>

            <!-- ═══ Search Reviews Tab ═══ -->
            <div class="tab-pane fade" id="searchReviews" role="tabpanel" aria-labelledby="search-reviews-tab">

                <div class="mb-4" style="max-width:500px">
                    <form class="d-flex" id="searchReviewsForm">
                        <input class="form-control me-2" type="search" id="searchReviewsInput"
                               placeholder="Search by artist, track, or username..." aria-label="Search" autocomplete="off">
                        <button class="btn btn-light" type="submit">Search</button>
                    </form>
                </div>

                <div id="allReviewsList">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading reviews...</p>
                    </div>
                </div>
            </div>

        </div><!-- /tab-content -->
    </div>

    <script src="js/shared.js"></script>
    <script src="js/reviews.js" defer></script>
    <script src="js/auth-guard.js" defer></script>
</body>
</html>
