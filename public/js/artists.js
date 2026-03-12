/**
 * artists.js
 * ----------
 * Renders the user's starred / liked cards from localStorage.
 * Depends on shared.js (M3 namespace) being loaded first.
 */

(function () {
    'use strict';

    function removeLike(itemId) {
        const liked = M3.getLikedItems();
        delete liked[itemId];
        M3.saveLikedItems(liked);
        render();
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
        container.innerHTML = items.map(i => M3.renderCard(i, true)).join('');

        container.querySelectorAll('.star-btn').forEach(btn =>
            btn.addEventListener('click', e => { e.stopPropagation(); removeLike(parseInt(btn.dataset.likeId, 10)); })
        );
    }

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', render) : render();
})();
