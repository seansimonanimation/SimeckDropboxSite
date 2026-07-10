/**
 * @name Helpers
 * @description Shared portal helper library for Simeck Entertainment Dropbox
 * @usage Loaded in index.php <head>, available to all modules
 */
var Helpers = window.Helpers || {};

(function() {
    'use strict';

    // ─── Private State ───────────────────────────────────────────────

    var _islandIdCounter = 0;
    var _dragState = null; // { island, startX, startY, origX, origY }

    // ─── Floating Island Engine ──────────────────────────────────────

    /**
     * Create a floating island DOM element
     * @param {string} id    - Unique ID (without 'island-' prefix)
     * @param {string} title - Title bar text
     * @param {string} content - HTML content for the body
     * @param {object} [opts] - { width, height, top, left }
     * @returns {HTMLElement} The island container
     */
    function _buildIsland(id, title, content, opts) {
        opts = opts || {};
        var island = document.createElement('div');
        island.className = 'floating-island';
        island.id = 'island-' + id;
        island.style.width = opts.width || '400px';
        island.style.height = opts.height || 'auto';
        if (opts.top)  island.style.top  = opts.top;
        if (opts.left) island.style.left = opts.left;

        // Center horizontally if no position given
        if (!opts.left) {
            island.style.left = '50%';
            island.style.marginLeft = '-' + (parseInt(island.style.width) / 2 || 200) + 'px';
        }
        if (!opts.top) {
            island.style.top = '15%';
        }

        // Title bar
        var titlebar = document.createElement('div');
        titlebar.className = 'island-titlebar';

        var titleSpan = document.createElement('span');
        titleSpan.className = 'island-title';
        titleSpan.textContent = title;

        var closeBtn = document.createElement('button');
        closeBtn.className = 'island-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function() {
            Helpers.closeIsland(id);
        });

        titlebar.appendChild(titleSpan);
        titlebar.appendChild(closeBtn);

        // Make titlebar draggable
        titlebar.addEventListener('mousedown', function(e) {
            _startDrag(e, island);
        });

        // Content area
        var contentDiv = document.createElement('div');
        contentDiv.className = 'island-content';
        contentDiv.innerHTML = content;

        island.appendChild(titlebar);
        island.appendChild(contentDiv);

        // Bring to front on click
        island.addEventListener('mousedown', function() {
            _bringToFront(island);
        });

        return island;
    }

    /**
     * Bring an island to the top of the z-index stack
     */
    function _bringToFront(island) {
        var allIslands = document.querySelectorAll('.floating-island');
        var maxZ = 1000;
        allIslands.forEach(function(el) {
            var z = parseInt(el.style.zIndex) || 1000;
            if (z > maxZ) maxZ = z;
        });
        island.style.zIndex = maxZ + 1;
    }

    // ─── Drag Implementation ─────────────────────────────────────────

    function _startDrag(e, island) {
        if (e.target.closest('.island-close')) return;
        _dragState = {
            island: island,
            startX: e.clientX,
            startY: e.clientY,
            origX: parseInt(island.style.left) || 0,
            origY: parseInt(island.style.top)  || 0
        };
        island.style.cursor = 'grabbing';
        document.addEventListener('mousemove', _onDrag);
        document.addEventListener('mouseup', _endDrag);
        e.preventDefault();
    }

    function _onDrag(e) {
        if (!_dragState) return;
        var dx = e.clientX - _dragState.startX;
        var dy = e.clientY - _dragState.startY;
        _dragState.island.style.left = (_dragState.origX + dx) + 'px';
        _dragState.island.style.top  = (_dragState.origY + dy) + 'px';
        _dragState.island.style.marginLeft = '0';
    }

    function _endDrag() {
        if (_dragState) {
            _dragState.island.style.cursor = '';
            _dragState = null;
        }
        document.removeEventListener('mousemove', _onDrag);
        document.removeEventListener('mouseup', _endDrag);
    }

    // ─── Public API ──────────────────────────────────────────────────

    // ═══ AJAX ═══════════════════════════════════════════════════════

    /**
     * Perform a GET request and parse JSON response
     * @param {string} url    - Base URL (e.g. '?action=something')
     * @param {object} [params] - Query parameters as key/value pairs
     * @returns {Promise<object>} Resolves with parsed JSON
     *
     * @example
     * Helpers.get('?action=search_client', { query: 'Bob' })
     *   .then(function(data) {
     *     if (data.success) { handle 
     */
    Helpers.get = function(url, params) {
        var fullUrl = Helpers.buildUrl(url, params);
        return fetch(fullUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .catch(function(err) {
            Helpers.alertIsland('Request Failed', 'Could not complete request: ' + err.message, 'error');
            throw err;
        });
    };

    /**
     * Perform a POST request with URL-encoded data and parse JSON response
     * @param {string} url  - Base URL
     * @param {object|FormData} data - Key/value pairs or FormData
     * @returns {Promise<object>} Resolves with parsed JSON
     */
    Helpers.post = function(url, data) {
        var isFormData = (typeof FormData !== 'undefined' && data instanceof FormData);
        var body = isFormData ? data : new URLSearchParams(data || {});

        return fetch(url, {
            method: 'POST',
            headers: isFormData ? {} : { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .catch(function(err) {
            Helpers.alertIsland('Request Failed', 'Could not complete request: ' + err.message, 'error');
            throw err;
        });
    };
    
    /**
     * Perform a POST request and get HTML text response
     * @param {string} url  - Base URL
     * @param {object|FormData|URLSearchParams} data - POST body
     * @returns {Promise<string>} Resolves with response text
     */
    Helpers.postHtml = function(url, data) {
        var isFormData = (typeof FormData !== 'undefined' && data instanceof FormData);
        return fetch(url, {
            method: 'POST',
            headers: isFormData ? {} : { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: isFormData ? data : new URLSearchParams(data || {})
        }).then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        });
    };

    // ═══ Floating Islands ═══════════════════════════════════════════

    /**
     * Spawn a floating island notification
     * @param {string} title   - Island title bar text
     * @param {string} content - HTML content for the island body
     * @param {string} [type]  - Style class: 'info', 'success', 'error', 'warning'
     * @returns {string} The island ID (for use with closeIsland)
     *
     * @example
     * Helpers.alertIsland('Success', 'File uploaded successfully.', 'success');
     */
    Helpers.alertIsland = function(title, content, type) {
        _islandIdCounter++;
        var id = 'alert-' + _islandIdCounter;
        type = type || 'info';

        var styledContent = '<div class="alert-island alert-island--' + type + '">' + content + '</div>';
        var island = _buildIsland(id, title, styledContent, { width: '420px' });
        island.classList.add('floating-island--alert');
        document.body.appendChild(island);
        _bringToFront(island);
        return id;
    };

    /**
     * Load HTML from a URL into a floating island
     * @param {string} url   - Endpoint that returns HTML fragment
     * @param {string} title - Island title bar text
     * @returns {Promise<string>} Resolves with the island ID when loaded
     *
     * @example
     * Helpers.spawnIsland('endpoints/getCommentsIsland.php?file=...', 'Comments');
     */
    Helpers.spawnIsland = function(url, title) {
        _islandIdCounter++;
        var id = 'spawned-' + _islandIdCounter;

        var island = _buildIsland(id, title, '<div class="island-loading">Loading...</div>', { width: '500px', height: '400px' });
        document.body.appendChild(island);
        _bringToFront(island);

        fetch(url)
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var contentDiv = island.querySelector('.island-content');
                if (contentDiv) contentDiv.innerHTML = html;
            })
            .catch(function(err) {
                var contentDiv = island.querySelector('.island-content');
                if (contentDiv) contentDiv.innerHTML = '<p class="island-error">Failed to load content.</p>';
            });

        return id;
    };

    /**
     * Close and remove a floating island
     * @param {string} id - The island ID (without 'island-' prefix)
     */
    Helpers.closeIsland = function(id) {
        var el = document.getElementById('island-' + id);
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    };

    // ═══ Confirmation Dialog ════════════════════════════════════════

    /**
     * Show a confirmation dialog as a floating island
     * @param {string} message - The confirmation question
     * @returns {Promise<boolean>} Resolves true if confirmed, false if cancelled
     *
     * @example
     * Helpers.confirm('Are you sure you want to delete this?')
     *   .then(function(confirmed) {
     *     if (confirmed) { do it 
     * 
     */ 
    Helpers.confirm = function(message) {
        _islandIdCounter++;
        var id = 'confirm-' + _islandIdCounter;

        var content =
            '<p class="confirm-message">' + message + '</p>' +
            '<div class="confirm-buttons">' +
                '<button class="confirm-yes module-button module-button--primary" data-action="confirm">Confirm</button> ' +
                '<button class="confirm-no module-button module-button--secondary" data-action="cancel">Cancel</button>' +
            '</div>';

        var island = _buildIsland(id, 'Confirm', content, { width: '380px' });
        document.body.appendChild(island);
        _bringToFront(island);

        return new Promise(function(resolve) {
            island.querySelector('.confirm-yes').addEventListener('click', function() {
                Helpers.closeIsland(id);
                resolve(true);
            });
            island.querySelector('.confirm-no').addEventListener('click', function() {
                Helpers.closeIsland(id);
                resolve(false);
            });
        });
    };

    // ═══ Loading State ══════════════════════════════════════════════

    /**
     * Toggle loading state on a button or element
     * @param {HTMLElement|string} el   - Element or CSS selector
     * @param {boolean} state           - true = loading, false = done
     * @param {string} [originalText]   - Text to restore when done (optional)
     *
     * @example
     * Helpers.loading('#submitBtn', true);
     * // ... after async operation ...
     * Helpers.loading('#submitBtn', false, 'Submit');
     */
    Helpers.loading = function(el, state, originalText) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el) return;

        if (state) {
            el.dataset.originalText = el.innerHTML;
            el.disabled = true;
            el.innerHTML = 'Loading...';
        } else {
            el.disabled = false;
            el.innerHTML = originalText || el.dataset.originalText || el.innerHTML;
        }
    };
    // ═══ Page Refresh ═══════════════════════════════════════════════

    /**
     * Reload the current page, removing beforeunload handlers to bypass
     * any "unsaved changes" prompts.
     *
     * @example
     * Helpers.refresh();
     */
    Helpers.refresh = function() {
        window.onbeforeunload = null;
        $(window).off('beforeunload');
        location.reload();
    };
    // ═══ Formatting Utilities ══════════════════════════════════════

    /**
     * Format bytes into a human-readable string (B, KB, MB, GB, TB)
     * @param {number} bytes - The byte count
     * @returns {string} e.g. "1.5 MB"
     *
     * @example
     * Helpers.formatBytes(1048576); // => "1.0 MB"
     */
    Helpers.formatBytes = function(bytes) {
        if (isNaN(bytes) || bytes === 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
    };
    // ═══ Clipboard ═══════════════════════════════════════════════════

    /**
     * Copy text to clipboard with fallback to prompt()
     * @param {string} text - The text to copy
     * @param {string} [successMsg] - Optional success message (displayed via alertIsland)
     *
     * @example
     * Helpers.copyToClipboard('https://example.com', 'Link copied!');
     */
    Helpers.copyToClipboard = function(text, successMsg) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (successMsg) Helpers.alertIsland('Copied', successMsg, 'success');
            }).catch(function() {
                prompt('Copy this text (Ctrl+C, then Enter):', text);
            });
        } else {
            prompt('Copy this text (Ctrl+C, then Enter):', text);
        }
    };

    // ═══ URL Utilities ══════════════════════════════════════════════

    /**
     * Get parsed URL parameters from the current page
     * @returns {object} Key/value pairs of URL parameters
     *
     * @example
     * var params = Helpers.urlParams();
     * // params.projectId => 'C01'
     */
    Helpers.urlParams = function() {
        var params = {};
        var search = window.location.search.substring(1);
        if (!search) return params;

        search.split('&').forEach(function(pair) {
            var parts = pair.split('=');
            if (parts[0]) {
                params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1] || '');
            }
        });
        return params;
    };

    /**
     * Build a URL with query parameters
     * @param {string} base    - Base URL (e.g. '?action=search' or '/endpoint.php')
     * @param {object} [params] - Key/value pairs to append
     * @returns {string} The full URL with query string
     *
     * @example
     * Helpers.buildUrl('?action=filter', { project: 'C01', page: 2 });
     * // => '?action=filter&project=C01&page=2'
     */
    Helpers.buildUrl = function(base, params) {
        if (!params) return base;
        var keys = Object.keys(params);
        if (keys.length === 0) return base;

        var separator = base.indexOf('?') === -1 ? '?' : '&';
        var qs = keys.map(function(k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');

        return base + separator + qs;
    };

    // ═══ DOM Utilities ══════════════════════════════════════════════

    /**
     * Get all data-* attributes from an element as an object
     * @param {HTMLElement|string} el - Element or CSS selector
     * @returns {object} Key/value pairs of data attributes
     *
     * @example
     * var cardData = Helpers.data('#project-card');
     * // cardData.projectId => 'C01'
     */
    Helpers.data = function(el) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el || !el.dataset) return {};
        return Object.assign({}, el.dataset);
    };

    /**
     * Serialize a form element to an object
     * @param {HTMLFormElement|string} form - Form element or CSS selector
     * @returns {object} Key/value pairs of form fields
     *
     * @example
     * var formData = Helpers.serialize('#myForm');
     * Helpers.post('?action=save', formData);
     */
    Helpers.serialize = function(form) {
        if (typeof form === 'string') form = document.querySelector(form);
        if (!form || !form.elements) return {};

        var data = {};
        Array.prototype.forEach.call(form.elements, function(field) {
            if (!field.name || field.disabled) return;
            if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) data[field.name] = field.value;
            } else if (field.type !== 'submit' && field.type !== 'button') {
                data[field.name] = field.value;
            }
        });
        return data;
    };

})();
