/**
 * reviews.js
 * ----------
 * Client-side logic for the Reviews page.
 * Handles creating reviews, loading "My Reviews", and searching all reviews.
 * All data is sent via reviews_api.php → RabbitMQ → DB VM.
 * Depends on shared.js (M3 namespace) being loaded first.
 */

(function () {
    'use strict';

    /* ── DOM refs ───────────────────────────────────────────────── */
    const createForm       = document.getElementById('createReviewForm');
    const subjectInput     = document.getElementById('reviewSubject');
    const ratingSelect     = document.getElementById('reviewRating');
    const reviewTextArea   = document.getElementById('reviewText');
    const submitBtn        = document.getElementById('submitReviewBtn');
    const myReviewsList    = document.getElementById('myReviewsList');

    const searchForm       = document.getElementById('searchReviewsForm');
    const searchInput      = document.getElementById('searchReviewsInput');
    const allReviewsList   = document.getElementById('allReviewsList');

    /* ── Helpers ────────────────────────────────────────────────── */

    function getSessionKey() {
        return sessionStorage.getItem('session_key') || '';
    }

    /** Render star icons for a 1-5 rating */
    function starsHtml(rating) {
        var s = '';
        for (var i = 1; i <= 5; i++) {
            s += i <= rating ? '&#9733;' : '&#9734;';
        }
        return '<span class="review-stars">' + s + '</span>';
    }

    /** Build HTML for a single review card */
    function reviewCardHtml(r, showDelete) {
        var palette = [
            { bg: '#1a1a2e', border: '#16213e' },
            { bg: '#0f3460', border: '#1a1a5e' },
            { bg: '#533483', border: '#4a2d7a' },
            { bg: '#1b4332', border: '#2d6a4f' },
            { bg: '#7b2d26', border: '#9b3a30' },
            { bg: '#2c3e50', border: '#34495e' },
        ];
        var hash = M3.hashCode(r.subject || String(r.review_id));
        var p = palette[Math.abs(hash) % palette.length];

        var deleteBtn = showDelete
            ? '<button class="btn btn-sm btn-outline-danger mt-2 delete-review-btn" data-review-id="' +
              r.review_id + '">Delete</button>'
            : '';

        var date = r.created_at ? new Date(r.created_at).toLocaleDateString() : '';

        return '<div class="col">' +
            '<div class="card h-100 border-0 shadow-sm" style="background:' + p.bg +
            ';color:#fff;border-left:4px solid ' + p.border + '!important">' +
                '<div class="card-body d-flex flex-column">' +
                    '<div class="d-flex justify-content-between align-items-start mb-1">' +
                        '<h5 class="card-title mb-0">' + M3.escapeHtml(r.subject) + '</h5>' +
                        '<span class="ms-2" style="white-space:nowrap">' + starsHtml(r.rating) + '</span>' +
                    '</div>' +
                    '<p class="small mb-1" style="opacity:.6">by <strong>' +
                        M3.escapeHtml(r.username) + '</strong> &middot; ' + M3.escapeHtml(date) + '</p>' +
                    '<p class="card-text flex-grow-1 mt-2" style="opacity:.85">' +
                        M3.escapeHtml(r.review_text) + '</p>' +
                    deleteBtn +
                '</div>' +
            '</div>' +
        '</div>';
    }

    /** POST to reviews_api.php which forwards to the DB VM via RabbitMQ */
    async function apiCall(action, extraData) {
        var body = new URLSearchParams();
        body.set('action', action);
        body.set('session_key', getSessionKey());

        if (extraData) {
            for (var k in extraData) {
                if (extraData.hasOwnProperty(k)) {
                    body.set(k, extraData[k]);
                }
            }
        }

        var res = await fetch('reviews_api.php', {
            method: 'POST',
            body: body
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    /* ── Load My Reviews ────────────────────────────────────────── */

    async function loadMyReviews() {
        myReviewsList.innerHTML =
            '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>';

        try {
            var data = await apiCall('get_my_reviews');

            if (data.status !== 'ok' || !data.reviews || data.reviews.length === 0) {
                myReviewsList.innerHTML =
                    '<p class="text-muted">You haven\'t written any reviews yet. Use the form above to create one!</p>';
                return;
            }

            var html = '<div class="row row-cols-1 row-cols-md-2 g-4">';
            data.reviews.forEach(function (r) {
                html += reviewCardHtml(r, true);
            });
            html += '</div>';
            myReviewsList.innerHTML = html;

            // Attach delete handlers
            myReviewsList.querySelectorAll('.delete-review-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    deleteReview(this.dataset.reviewId);
                });
            });

        } catch (err) {
            console.error('Failed to load my reviews:', err);
            myReviewsList.innerHTML =
                '<div class="alert alert-danger">Failed to load your reviews. Make sure the DB VM server is running.</div>';
        }
    }

    /* ── Load All / Search Reviews ──────────────────────────────── */

    async function loadAllReviews(searchQuery) {
        allReviewsList.innerHTML =
            '<div class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></div>';

        try {
            var extra = {};
            if (searchQuery) extra.search = searchQuery;

            var data = await apiCall('get_all_reviews', extra);

            if (data.status !== 'ok' || !data.reviews || data.reviews.length === 0) {
                allReviewsList.innerHTML =
                    '<p class="text-muted">No reviews found.' +
                    (searchQuery ? ' Try a different search term.' : '') + '</p>';
                return;
            }

            var html = '<div class="row row-cols-1 row-cols-md-2 g-4">';
            data.reviews.forEach(function (r) {
                html += reviewCardHtml(r, false);
            });
            html += '</div>';
            allReviewsList.innerHTML = html;

        } catch (err) {
            console.error('Failed to load reviews:', err);
            allReviewsList.innerHTML =
                '<div class="alert alert-danger">Failed to load reviews. Make sure the DB VM server is running.</div>';
        }
    }

    /* ── Create Review ──────────────────────────────────────────── */

    createForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            var data = await apiCall('create_review', {
                subject:     subjectInput.value.trim(),
                rating:      ratingSelect.value,
                review_text: reviewTextArea.value.trim()
            });

            if (data.status === 'ok') {
                M3.showToast('Review submitted successfully!', 'success');
                createForm.reset();
                loadMyReviews();
                loadAllReviews('');
            } else {
                M3.showToast(data.message || 'Failed to submit review.', 'danger');
            }
        } catch (err) {
            console.error('Create review error:', err);
            M3.showToast('Failed to submit review. Please try again.', 'danger');
        }

        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Review';
    });

    /* ── Delete Review ──────────────────────────────────────────── */

    async function deleteReview(reviewId) {
        if (!confirm('Delete this review?')) return;

        try {
            var data = await apiCall('delete_review', { review_id: reviewId });

            if (data.status === 'ok') {
                M3.showToast('Review deleted.', 'info');
                loadMyReviews();
                loadAllReviews('');
            } else {
                M3.showToast(data.message || 'Failed to delete review.', 'danger');
            }
        } catch (err) {
            console.error('Delete review error:', err);
            M3.showToast('Failed to delete review.', 'danger');
        }
    }

    /* ── Search Reviews Form ────────────────────────────────────── */

    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadAllReviews(searchInput.value.trim());
    });

    // Live-search on typing (debounced)
    searchInput.addEventListener('input', M3.debounce(function () {
        loadAllReviews(searchInput.value.trim());
    }, 400));

    /* ── Tab switch: lazy-load search tab ────────────────────────── */
    document.getElementById('search-reviews-tab').addEventListener('shown.bs.tab', function () {
        loadAllReviews(searchInput.value.trim());
    });

    /* ── Init ───────────────────────────────────────────────────── */
    loadMyReviews();

})();
