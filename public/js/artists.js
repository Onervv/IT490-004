/**
 * artists.js
 * ----------
 * Renders the user's starred / liked artist cards in a responsive grid.
 * Uses M3.renderCard for consistent card styling with the Explore page.
 * Depends on shared.js (M3 namespace) being loaded first.
 */

(function () {
    'use strict';

    function removeLike(id) {
        var liked = M3.getLikedItems();
        delete liked[String(id)];
        M3.saveLikedItems(liked);
        render();
    }

    function render() {
        var container = document.getElementById('artistsCardContainer');
        var emptyMsg  = document.getElementById('artistsEmpty');
        if (!container) return;

        var items = Object.values(M3.getLikedItems());

        if (!items.length) {
            container.innerHTML = '';
            if (emptyMsg) emptyMsg.style.display = 'block';
            return;
        }
        if (emptyMsg) emptyMsg.style.display = 'none';

        // Use the shared renderCard with removable=true (star acts as remove)
        container.innerHTML = items.map(function (item) {
            return M3.renderCard(item, true);
        }).join('');

        // Wire star-remove buttons
        container.querySelectorAll('.star-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                removeLike(btn.dataset.likeId);
            });
        });
    }

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', render) : render();
})();
