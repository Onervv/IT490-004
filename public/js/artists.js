/**
 * artists.js
 * ----------
 * Renders the user's starred / liked cards from localStorage.
 * Includes review/blog functionality per artist.
 * Depends on shared.js (M3 namespace) being loaded first.
 */

(function () {
    'use strict';

    function removeLike(itemId) {
        const liked = M3.getLikedItems();
        delete liked[String(itemId)];
        M3.saveLikedItems(liked);
        render();
    }

    /** Build a card with an attached review section */
    function renderFavoriteCard(item) {
        const sid    = String(item.id);
        const review = M3.getReview(sid);
        const card   = M3.renderCard(item, true);

        const reviewHtml = review
            ? `<div class="review-display mt-2 p-2 rounded" style="background:rgba(0,0,0,.05)">
                   <small class="text-muted d-block mb-1">Your review &mdash; ${M3.escapeHtml(review.date)}</small>
                   <p class="mb-1 small">${M3.escapeHtml(review.text)}</p>
                   <button class="btn btn-sm btn-outline-secondary edit-review-btn" data-artist-id="${sid}">Edit</button>
                   <button class="btn btn-sm btn-outline-danger delete-review-btn ms-1" data-artist-id="${sid}">Delete</button>
               </div>`
            : `<button class="btn btn-sm btn-outline-primary write-review-btn mt-2" data-artist-id="${sid}">
                   &#9998; Write a Review
               </button>`;

        // Wrap card + review in a containing div
        return `<div class="col card-item-wrap" data-artist-sid="${sid}">
                    ${card.replace('<div class="col card-item"', '<div class="card-item"')}
                    ${reviewHtml}
                </div>`;
    }

    function openReviewEditor(artistId, existingText) {
        const sid = String(artistId);
        // Find the wrap element
        const wrap = document.querySelector(`[data-artist-sid="${sid}"]`);
        if (!wrap) return;

        // Get artist name from the card
        const nameEl = wrap.querySelector('.card-title');
        const name   = nameEl ? nameEl.textContent : 'this artist';

        // Remove existing review display / button
        const old = wrap.querySelector('.review-display, .write-review-btn, .review-editor');
        if (old) old.remove();

        const editor = document.createElement('div');
        editor.className = 'review-editor mt-2';
        editor.innerHTML = `
            <label class="form-label small fw-bold">Your thoughts on ${M3.escapeHtml(name)}:</label>
            <textarea class="form-control form-control-sm review-textarea" rows="3"
                      placeholder="What do you think about this artist? Share your thoughts..."
                      maxlength="500">${M3.escapeHtml(existingText || '')}</textarea>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-sm btn-success save-review-btn" data-artist-id="${sid}">Save</button>
                <button class="btn btn-sm btn-secondary cancel-review-btn">Cancel</button>
                <small class="text-muted ms-auto align-self-center char-count">0/500</small>
            </div>`;

        wrap.appendChild(editor);

        const textarea = editor.querySelector('.review-textarea');
        const charCount = editor.querySelector('.char-count');
        textarea.focus();
        updateCharCount();

        textarea.addEventListener('input', updateCharCount);

        function updateCharCount() {
            charCount.textContent = `${textarea.value.length}/500`;
        }

        editor.querySelector('.save-review-btn').addEventListener('click', () => {
            M3.saveReview(sid, textarea.value);
            render();
        });

        editor.querySelector('.cancel-review-btn').addEventListener('click', () => render());
    }

    function render() {
        const container = document.getElementById('artistsCardContainer');
        const emptyMsg  = document.getElementById('artistsEmpty');
        if (!container) return;

        const items = Object.values(M3.getLikedItems());

        if (!items.length) {
            container.innerHTML = '';
            emptyMsg.style.display = 'block';
            return;
        }

        emptyMsg.style.display = 'none';
        container.innerHTML = items.map(i => renderFavoriteCard(i)).join('');

        // Star (remove) buttons
        container.querySelectorAll('.star-btn').forEach(btn =>
            btn.addEventListener('click', e => { e.stopPropagation(); removeLike(btn.dataset.likeId); })
        );

        // Write review buttons
        container.querySelectorAll('.write-review-btn').forEach(btn =>
            btn.addEventListener('click', () => openReviewEditor(btn.dataset.artistId, ''))
        );

        // Edit review buttons
        container.querySelectorAll('.edit-review-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const review = M3.getReview(btn.dataset.artistId);
                openReviewEditor(btn.dataset.artistId, review ? review.text : '');
            });
        });

        // Delete review buttons
        container.querySelectorAll('.delete-review-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                M3.saveReview(btn.dataset.artistId, '');
                render();
            });
        });
    }

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', render) : render();
})();
