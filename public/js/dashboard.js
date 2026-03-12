/**
 * dashboard.js
 * -----------
 * Dynamic card rendering with:
 *   - Virtual scrolling / windowing (only visible rows in the DOM)
 *   - Infinite scroll  (load more data as user scrolls down)
 *   - Search bar that filters cards and jumps to matches
 *
 * TODO: Replace placeholder data source with real API calls.
 */

(function () {
    'use strict';

    /* ====================================================================
     *  CONFIG
     * ==================================================================== */

    const CARD_MIN_HEIGHT   = 220;   // estimated card height in px (used for windowing math)
    const COLS_PER_ROW      = 4;     // matches row-cols-lg-4
    const BATCH_SIZE        = 20;    // how many items to load per infinite-scroll fetch
    const SCROLL_THRESHOLD  = 300;   // px from bottom to trigger next batch
    const DEBOUNCE_MS       = 120;   // debounce interval for scroll & search events

    /* ====================================================================
     *  STATE
     * ==================================================================== */

    let allItems       = [];   // master list of every loaded item
    let filteredItems  = [];   // subset after search filtering (points to allItems when no filter)
    let currentBatch   = 0;    // how many batches have been fetched so far
    let isFetching     = false;
    let hasMore        = true; // false once API signals no more data
    let activeSearch   = '';   // current search term

    // Virtual-scroll bookkeeping
    let firstVisibleRow = 0;
    let lastVisibleRow  = 0;
    const OVERSCAN_ROWS = 2;  // extra rows rendered above/below viewport

    /* ====================================================================
     *  DOM REFERENCES (resolved once after dashboard is shown)
     * ==================================================================== */

    let viewport      = null;
    let spacer         = null;
    let cardContainer  = null;
    let sentinel       = null;
    let searchInput    = null;
    let searchForm     = null;
    let searchDropdown = null;

    /* ====================================================================
     *  PLACEHOLDER DATA SOURCE
     *  TODO: Replace this function with a real API call, e.g.:
     *    async function fetchItems(offset, limit) {
     *        const res = await fetch(`/api/artists?offset=${offset}&limit=${limit}`);
     *        const json = await res.json();
     *        return json.data;  // array of { id, title, artist, genre, imageUrl, ... }
     *    }
     * ==================================================================== */

    /**
     * Simulates fetching a page of items.
     * Each item has the shape the card renderer expects.
     */
    function generatePlaceholderItems(offset, limit) {
        // TODO: Replace with real API fetch
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
            const artist = SAMPLE_ARTISTS[idx % SAMPLE_ARTISTS.length];
            const genre  = SAMPLE_GENRES[idx % SAMPLE_GENRES.length];
            items.push({
                id:       idx,
                title:    `Track #${idx + 1}`,
                artist:   artist,
                genre:    genre,
                bgClass:  BOOTSTRAP_BG[idx % BOOTSTRAP_BG.length],
                textClass: (idx % BOOTSTRAP_BG.length === 7) ? 'text-white' : '',
                // TODO: add imageUrl, album, duration, etc. from real API data
            });
        }
        return items;
    }

    /* ====================================================================
     *  CARD RENDERING
     * ==================================================================== */

    /**
     * Build HTML for a single card column.
     * TODO: Update card markup to include real API fields (album art, play button, etc.)
     */
    function renderCard(item) {
        return `
            <div class="col card-item" data-item-id="${item.id}">
                <div class="card ${item.bgClass} ${item.textClass} h-100">
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
     *  Only the visible rows (+ overscan) are kept in the DOM.
     *  A spacer element is sized to represent the full scrollable height.
     * ==================================================================== */

    function getTotalRows() {
        return Math.ceil(filteredItems.length / COLS_PER_ROW);
    }

    function getRowHeight() {
        // Use a measured height if available, otherwise fallback
        const firstCard = cardContainer.querySelector('.card-item');
        if (firstCard) {
            return firstCard.offsetHeight + 24; // 24 = g-4 gap approx
        }
        return CARD_MIN_HEIGHT;
    }

    function updateSpacerHeight() {
        const totalHeight = getTotalRows() * getRowHeight();
        spacer.style.minHeight = totalHeight + 'px';
    }

    /**
     * Determine which rows are visible in the viewport and render only those
     * (plus OVERSCAN_ROWS above and below).
     */
    function renderVisibleCards() {
        const scrollTop    = window.scrollY || document.documentElement.scrollTop;
        const viewportH    = window.innerHeight;
        const containerTop = viewport.getBoundingClientRect().top + scrollTop;
        const rowH         = getRowHeight();

        const relativeScroll = Math.max(0, scrollTop - containerTop);
        const firstRow = Math.max(0, Math.floor(relativeScroll / rowH) - OVERSCAN_ROWS);
        const visibleRows = Math.ceil(viewportH / rowH) + OVERSCAN_ROWS * 2;
        const lastRow  = Math.min(getTotalRows() - 1, firstRow + visibleRows);

        // Skip re-render if the window hasn't changed
        if (firstRow === firstVisibleRow && lastRow === lastVisibleRow && cardContainer.childElementCount > 0) {
            return;
        }
        firstVisibleRow = firstRow;
        lastVisibleRow  = lastRow;

        // Build only the rows in range
        let html = '';
        for (let row = firstRow; row <= lastRow; row++) {
            const startIdx = row * COLS_PER_ROW;
            const endIdx   = Math.min(startIdx + COLS_PER_ROW, filteredItems.length);
            for (let i = startIdx; i < endIdx; i++) {
                html += renderCard(filteredItems[i]);
            }
        }

        cardContainer.innerHTML = html;

        // Offset the card container so the visible cards sit at the right scroll position
        const offsetY = firstRow * rowH;
        cardContainer.style.transform = `translateY(${offsetY}px)`;
    }

    /* ====================================================================
     *  INFINITE SCROLL
     * ==================================================================== */

    async function loadNextBatch() {
        if (isFetching || !hasMore) return;
        isFetching = true;
        sentinel.style.display = 'block';

        const offset = currentBatch * BATCH_SIZE;

        // TODO: Replace with:  const newItems = await fetchItems(offset, BATCH_SIZE);
        const newItems = generatePlaceholderItems(offset, BATCH_SIZE);

        if (newItems.length < BATCH_SIZE) {
            hasMore = false;
        }

        allItems = allItems.concat(newItems);
        currentBatch++;

        // Re-apply search filter if active
        applyFilter();

        updateSpacerHeight();
        renderVisibleCards();

        sentinel.style.display = 'none';
        isFetching = false;
    }

    /**
     * IntersectionObserver watches the sentinel element at the bottom of the
     * card list. When it becomes visible, fetch the next batch.
     */
    function setupInfiniteScroll() {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && hasMore && !isFetching) {
                loadNextBatch();
            }
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

    /**
     * Show a compact dropdown of matching items while the user types,
     * clicking a result scrolls the viewport to that card.
     */
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

        // Attach click handlers for jump-to-card
        searchDropdown.querySelectorAll('.search-result-item').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = parseInt(btn.dataset.targetId, 10);
                jumpToCard(targetId);
                searchDropdown.style.display = 'none';
            });
        });
    }

    /**
     * Scroll the page so the card with the given item id is visible and highlighted.
     */
    function jumpToCard(itemId) {
        // Find the index and compute which row it lives in
        const idx = filteredItems.findIndex(item => item.id === itemId);
        if (idx === -1) return;

        const row   = Math.floor(idx / COLS_PER_ROW);
        const rowH  = getRowHeight();
        const containerTop = viewport.getBoundingClientRect().top + (window.scrollY || document.documentElement.scrollTop);
        const targetScroll = containerTop + row * rowH;

        window.scrollTo({ top: targetScroll - 80, behavior: 'smooth' });

        // After scroll settles, force-render and highlight the card
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
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function wireEvents() {
        // Scroll → re-render visible window + check infinite scroll
        window.addEventListener('scroll', debounce(() => {
            renderVisibleCards();

            // Fallback infinite-scroll check (belt-and-suspenders with IntersectionObserver)
            const docHeight  = document.documentElement.scrollHeight;
            const scrollPos  = window.scrollY + window.innerHeight;
            if (docHeight - scrollPos < SCROLL_THRESHOLD && hasMore && !isFetching) {
                loadNextBatch();
            }
        }, DEBOUNCE_MS));

        window.addEventListener('resize', debounce(() => {
            updateSpacerHeight();
            renderVisibleCards();
        }, DEBOUNCE_MS));

        // Search input (live filtering + dropdown)
        searchInput.addEventListener('input', debounce(() => {
            activeSearch = searchInput.value.trim();
            applyFilter();
            updateSpacerHeight();
            firstVisibleRow = -1; // force re-render
            renderVisibleCards();
            updateSearchDropdown();
        }, DEBOUNCE_MS));

        // Search form submit → jump to first result
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            activeSearch = searchInput.value.trim();
            applyFilter();
            updateSpacerHeight();
            firstVisibleRow = -1;
            renderVisibleCards();
            if (filteredItems.length > 0) {
                jumpToCard(filteredItems[0].id);
            }
            searchDropdown.style.display = 'none';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchDropdown.contains(e.target) && e.target !== searchInput) {
                searchDropdown.style.display = 'none';
            }
        });
    }

    /* ====================================================================
     *  INITIALIZATION
     *  Waits for the dashboard div to become visible (session validated)
     *  before bootstrapping the card system.
     * ==================================================================== */

    function init() {
        viewport       = document.getElementById('virtualScrollViewport');
        spacer         = document.getElementById('virtualScrollSpacer');
        cardContainer  = document.getElementById('cardContainer');
        sentinel       = document.getElementById('scrollSentinel');
        searchInput    = document.getElementById('dashboardSearchInput');
        searchForm     = document.getElementById('dashboardSearchForm');
        searchDropdown = document.getElementById('dashboardSearchResults');

        if (!viewport || !cardContainer) return; // safety

        wireEvents();
        setupInfiniteScroll();
        loadNextBatch(); // first batch
    }

    // We need to wait until the dashboard <div> is actually shown (after
    // session validation). Use a MutationObserver to detect that.
    function waitForDashboard() {
        const dashboard = document.getElementById('dashboard');
        if (!dashboard) {
            document.addEventListener('DOMContentLoaded', waitForDashboard);
            return;
        }

        // If already visible, init immediately
        if (dashboard.style.display !== 'none') {
            init();
            return;
        }

        // Watch for the display change
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                if (m.attributeName === 'style' && dashboard.style.display !== 'none') {
                    observer.disconnect();
                    init();
                    return;
                }
            }
        });
        observer.observe(dashboard, { attributes: true, attributeFilter: ['style'] });
    }

    waitForDashboard();
})();
