/**
 * explore.js
 * ----------
 * Dynamic card rendering on the Explore page with:
 *   - Virtual scrolling / windowing (only visible rows in the DOM)
 *   - Infinite scroll  (load more data as user scrolls down)
 *   - Search bar that filters cards and jumps to matches
 *   - Star / like button on each card (persisted per-user in localStorage)
 *
 * TODO: Replace placeholder data source with real API calls.
 */

(function () {
    'use strict';

    /* ====================================================================
     *  CONFIG
     * ==================================================================== */

    const CARD_MIN_HEIGHT   = 220;
    const COLS_PER_ROW      = 4;
    const BATCH_SIZE        = 20;
    const SCROLL_THRESHOLD  = 300;
    const DEBOUNCE_MS       = 120;

    /* ====================================================================
     *  STATE
     * ==================================================================== */

    let allItems       = [];
    let filteredItems  = [];
    let currentBatch   = 0;
    let isFetching     = false;
    let hasMore        = true;
    let activeSearch   = '';

    let firstVisibleRow = 0;
    let lastVisibleRow  = 0;
    const OVERSCAN_ROWS = 2;

    /* ====================================================================
     *  DOM REFS
     * ==================================================================== */

    let viewport, spacer, cardContainer, sentinel;
    let searchInput, searchForm, searchDropdown;

    /* ====================================================================
     *  LIKED / STARRED ITEMS  (per-user via localStorage)
     * ==================================================================== */

    function getLikedKey() {
        const username = sessionStorage.getItem('username') || '_anon';
        return `liked_items_${username}`;
    }

    function getLikedItems() {
        try {
            return JSON.parse(localStorage.getItem(getLikedKey())) || {};
        } catch { return {}; }
    }

    function saveLikedItems(liked) {
        localStorage.setItem(getLikedKey(), JSON.stringify(liked));
    }

    function isLiked(itemId) {
        return !!getLikedItems()[itemId];
    }

    /**
     * Toggle like state and persist the full item data so the Artists page
     * can render the card without needing another API call.
     */
    function toggleLike(itemId) {
        const liked = getLikedItems();
        if (liked[itemId]) {
            delete liked[itemId];
        } else {
            const item = allItems.find(i => i.id === itemId);
            if (item) liked[itemId] = item;
        }
        saveLikedItems(liked);
        // Re-render so the star updates
        firstVisibleRow = -1;
        renderVisibleCards();
    }

    /* ====================================================================
     *  PLACEHOLDER DATA SOURCE
     *  TODO: Replace with real API fetch
     * ==================================================================== */

    function generatePlaceholderItems(offset, limit) {
        const SAMPLE_GENRES = ['Pop', 'Rock', 'Jazz', 'Hip-Hop', 'R&B', 'Electronic', 'Classical', 'Country'];
        const SAMPLE_ARTISTS = [
            'The Weeknd', 'Doja Cat', 'Tyler the Creator', 'SZA',
            'Kendrick Lamar', 'Frank Ocean', 'Bad Bunny', 'Billie Eilish',
            'Drake', 'Ariana Grande', 'Post Malone', 'Dua Lipa',
            'Travis Scott', 'Lana Del Rey', 'Metro Boomin', 'J. Cole'
        ];
        const BOOTSTRAP_BG = [
            'bg-primary', 'bg-secondary', 'bg-success', 'bg-danger',
            'bg-warning', 'bg-info', 'bg-light', 'bg-dark'
        ];

        const MAX_ITEMS = 200; // TODO: remove cap once real API is wired up
        const items = [];
        for (let i = 0; i < limit; i++) {
            const idx = offset + i;
            if (idx >= MAX_ITEMS) break;
            items.push({
                id:        idx,
                title:     `Track #${idx + 1}`,
                artist:    SAMPLE_ARTISTS[idx % SAMPLE_ARTISTS.length],
                genre:     SAMPLE_GENRES[idx % SAMPLE_GENRES.length],
                bgClass:   BOOTSTRAP_BG[idx % BOOTSTRAP_BG.length],
                textClass: (idx % BOOTSTRAP_BG.length === 7) ? 'text-white' : '',
            });
        }
        return items;
    }

    /* ====================================================================
     *  CARD RENDERING  (with star button)
     * ==================================================================== */

    function renderCard(item) {
        const liked = isLiked(item.id);
        const starClass = liked ? 'star-btn starred' : 'star-btn';
        const starFill  = liked ? '&#9733;' : '&#9734;';   // ★ filled vs ☆ outline

        return `
            <div class="col card-item" data-item-id="${item.id}">
                <div class="card ${item.bgClass} ${item.textClass} h-100 position-relative">
                    <button type="button" class="${starClass}" data-like-id="${item.id}" title="Add to favorites">${starFill}</button>
                    <div class="card-header">${escapeHtml(item.genre)}</div>
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(item.title)}</h5>
                        <p class="card-text">${escapeHtml(item.artist)}</p>
                    </div>
                </div>
            </div>`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* ====================================================================
     *  VIRTUAL SCROLLING / WINDOWING
     * ==================================================================== */

    function getTotalRows() {
        return Math.ceil(filteredItems.length / COLS_PER_ROW);
    }

    function getRowHeight() {
        const firstCard = cardContainer.querySelector('.card-item');
        if (firstCard) return firstCard.offsetHeight + 24;
        return CARD_MIN_HEIGHT;
    }

    function updateSpacerHeight() {
        spacer.style.minHeight = getTotalRows() * getRowHeight() + 'px';
    }

    function renderVisibleCards() {
        const scrollTop    = window.scrollY || document.documentElement.scrollTop;
        const viewportH    = window.innerHeight;
        const containerTop = viewport.getBoundingClientRect().top + scrollTop;
        const rowH         = getRowHeight();

        const relativeScroll = Math.max(0, scrollTop - containerTop);
        const firstRow  = Math.max(0, Math.floor(relativeScroll / rowH) - OVERSCAN_ROWS);
        const lastRow   = Math.min(getTotalRows() - 1, firstRow + Math.ceil(viewportH / rowH) + OVERSCAN_ROWS * 2);

        if (firstRow === firstVisibleRow && lastRow === lastVisibleRow && cardContainer.childElementCount > 0) return;
        firstVisibleRow = firstRow;
        lastVisibleRow  = lastRow;

        let html = '';
        for (let row = firstRow; row <= lastRow; row++) {
            const start = row * COLS_PER_ROW;
            const end   = Math.min(start + COLS_PER_ROW, filteredItems.length);
            for (let i = start; i < end; i++) html += renderCard(filteredItems[i]);
        }
        cardContainer.innerHTML = html;
        cardContainer.style.transform = `translateY(${firstRow * rowH}px)`;

        // Attach star-button click handlers (event delegation)
        cardContainer.querySelectorAll('.star-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleLike(parseInt(btn.dataset.likeId, 10));
            });
        });
    }

    /* ====================================================================
     *  INFINITE SCROLL
     * ==================================================================== */

    async function loadNextBatch() {
        if (isFetching || !hasMore) return;
        isFetching = true;
        sentinel.style.display = 'block';

        // TODO: Replace with real API call
        const newItems = generatePlaceholderItems(currentBatch * BATCH_SIZE, BATCH_SIZE);
        if (newItems.length < BATCH_SIZE) hasMore = false;

        allItems = allItems.concat(newItems);
        currentBatch++;
        applyFilter();
        updateSpacerHeight();
        renderVisibleCards();

        sentinel.style.display = 'none';
        isFetching = false;
    }

    function setupInfiniteScroll() {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && hasMore && !isFetching) loadNextBatch();
        }, { rootMargin: `${SCROLL_THRESHOLD}px` });
        observer.observe(sentinel);
    }

    /* ====================================================================
     *  SEARCH / FILTER
     * ==================================================================== */

    function applyFilter() {
        if (!activeSearch) {
            filteredItems = allItems;
        } else {
            const term = activeSearch.toLowerCase();
            filteredItems = allItems.filter(item =>
                item.title.toLowerCase().includes(term) ||
                item.artist.toLowerCase().includes(term) ||
                item.genre.toLowerCase().includes(term)
            );
        }
    }

    function updateSearchDropdown() {
        if (!activeSearch || filteredItems.length === 0) {
            searchDropdown.innerHTML = '';
            searchDropdown.style.display = 'none';
            return;
        }
        const maxPreview = 8;
        const preview = filteredItems.slice(0, maxPreview);
        let html = '';
        preview.forEach(item => {
            html += `<button type="button" class="dropdown-item search-result-item" data-target-id="${item.id}">
                        <strong>${escapeHtml(item.title)}</strong> &mdash; ${escapeHtml(item.artist)}
                     </button>`;
        });
        if (filteredItems.length > maxPreview) {
            html += `<span class="dropdown-item text-muted small">+${filteredItems.length - maxPreview} more results</span>`;
        }
        searchDropdown.innerHTML = html;
        searchDropdown.style.display = 'block';

        searchDropdown.querySelectorAll('.search-result-item').forEach(btn => {
            btn.addEventListener('click', () => {
                jumpToCard(parseInt(btn.dataset.targetId, 10));
                searchDropdown.style.display = 'none';
            });
        });
    }

    function jumpToCard(itemId) {
        const idx = filteredItems.findIndex(item => item.id === itemId);
        if (idx === -1) return;
        const row  = Math.floor(idx / COLS_PER_ROW);
        const rowH = getRowHeight();
        const containerTop = viewport.getBoundingClientRect().top + (window.scrollY || document.documentElement.scrollTop);
        window.scrollTo({ top: containerTop + row * rowH - 80, behavior: 'smooth' });

        setTimeout(() => {
            renderVisibleCards();
            const cardEl = cardContainer.querySelector(`[data-item-id="${itemId}"]`);
            if (cardEl) {
                cardEl.classList.add('card-highlight');
                setTimeout(() => cardEl.classList.remove('card-highlight'), 1500);
            }
        }, 400);
    }

    /* ====================================================================
     *  EVENT WIRING
     * ==================================================================== */

    function debounce(fn, ms) {
        let timer;
        return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
    }

    function wireEvents() {
        window.addEventListener('scroll', debounce(() => {
            renderVisibleCards();
            const docH = document.documentElement.scrollHeight;
            if (docH - (window.scrollY + window.innerHeight) < SCROLL_THRESHOLD && hasMore && !isFetching) loadNextBatch();
        }, DEBOUNCE_MS));

        window.addEventListener('resize', debounce(() => { updateSpacerHeight(); renderVisibleCards(); }, DEBOUNCE_MS));

        searchInput.addEventListener('input', debounce(() => {
            activeSearch = searchInput.value.trim();
            applyFilter();
            updateSpacerHeight();
            firstVisibleRow = -1;
            renderVisibleCards();
            updateSearchDropdown();
        }, DEBOUNCE_MS));

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            activeSearch = searchInput.value.trim();
            applyFilter();
            updateSpacerHeight();
            firstVisibleRow = -1;
            renderVisibleCards();
            if (filteredItems.length > 0) jumpToCard(filteredItems[0].id);
            searchDropdown.style.display = 'none';
        });

        document.addEventListener('click', (e) => {
            if (!searchDropdown.contains(e.target) && e.target !== searchInput) searchDropdown.style.display = 'none';
        });
    }

    /* ====================================================================
     *  INIT — runs immediately (no session-gated display on explore)
     * ==================================================================== */

    function init() {
        viewport       = document.getElementById('virtualScrollViewport');
        spacer         = document.getElementById('virtualScrollSpacer');
        cardContainer  = document.getElementById('cardContainer');
        sentinel       = document.getElementById('scrollSentinel');
        searchInput    = document.getElementById('exploreSearchInput');
        searchForm     = document.getElementById('exploreSearchForm');
        searchDropdown = document.getElementById('exploreSearchResults');
        if (!viewport || !cardContainer) return;

        wireEvents();
        setupInfiniteScroll();
        loadNextBatch();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
