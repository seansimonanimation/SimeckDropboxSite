/**
 * @name Helpers
 * @description Shared portal helper library for Simeck Entertainment Dropbox
 * @usage Loaded in index.php <head>, available to all modules
 */
var Helpers = window.Helpers || {};

(function() {
    'use strict';

    var _islandIdCounter = 0;

    function _buildIsland(id, title, content, opts) {
        opts = opts || {};
        var island = document.createElement('div');
        island.className = 'floating-island';
        island.id = 'island-' + id;
        island.style.width = opts.width || '400px';
        island.style.height = opts.height || 'auto';
        if (opts.top)  island.style.top  = opts.top;
        if (opts.left) island.style.left = opts.left;

        if (!opts.left) {
            island.style.left = '50%';
            island.style.transform = 'translateX(-50%)';
        }
        if (!opts.top) {
            island.style.top = '15%';
        }

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

        // ← THIS LINE WAS MISSING: attaches drag to the island
        _attachIslandDrag(island, titlebar);

        var contentDiv = document.createElement('div');
        contentDiv.className = 'island-content';
        contentDiv.innerHTML = content;
        island.appendChild(titlebar);
        island.appendChild(contentDiv);
        island.addEventListener('mousedown', function() {
            _bringToFront(island);
        });
        return island;
    }

    function _bringToFront(island) {
        var allIslands = document.querySelectorAll('.floating-island');
        var maxZ = 1000;
        allIslands.forEach(function(el) {
            var z = parseInt(el.style.zIndex) || 1000;
            if (z > maxZ) maxZ = z;
        });
        island.style.zIndex = maxZ + 1;
    }

    // ─── Drag + Resize ──────────────────────────────────────────────

    function _attachIslandDrag(island, header) {
        if (!header || header._dragAttached) return;
        header._dragAttached = true;

        function onStart(e) {
            if (e.target.closest('.island-close') || e.target.closest('.floating-island__close')) return;
            e.preventDefault();
            var rect = island.getBoundingClientRect();
            var pt = e.type === 'touchstart' ? e.touches[0] : e;
            island._dragOffsetX = pt.clientX - rect.left;
            island._dragOffsetY = pt.clientY - rect.top;
            island.style.cursor = 'grabbing';
            island.style.transition = 'none';
            island.classList.add('floating-island--dragging');
        }

        header.addEventListener('mousedown',  onStart);
        header.addEventListener('touchstart', onStart, { passive: false });
    }

    document.addEventListener('mousemove', function(e) {
        document.querySelectorAll('.floating-island--dragging').forEach(function(island) {
            island.style.left = (e.clientX - island._dragOffsetX) + 'px';
            island.style.top  = (e.clientY - island._dragOffsetY) + 'px';
            island.style.transform = 'none';
        });
    });

    document.addEventListener('mouseup', function() {
        document.querySelectorAll('.floating-island--dragging').forEach(function(island) {
            island.style.cursor = '';
            island.style.transition = '';
            island.classList.remove('floating-island--dragging');
            delete island._dragOffsetX;
            delete island._dragOffsetY;
        });
    });

    function _attachIslandResize(island, handle) {
        if (!handle || handle._resizeAttached) return;
        handle._resizeAttached = true;
        var resizing = false, startX, startY, startW, startH;

        handle.addEventListener('mousedown', function(e) {
            resizing = true;
            startX = e.clientX;
            startY = e.clientY;
            startW = island.offsetWidth;
            startH = island.offsetHeight;
            e.preventDefault();
            e.stopPropagation();
        });

        document.addEventListener('mousemove', function(e) {
            if (!resizing) return;
            island.style.width  = Math.max(300, startW + (e.clientX - startX)) + 'px';
            island.style.height = Math.max(200, startH + (e.clientY - startY)) + 'px';
        });

        document.addEventListener('mouseup', function() {
            resizing = false;
        });
    }

    // ─── Public API ──────────────────────────────────────────────────

    Helpers.createIsland = function(title, content, opts) {
        opts = opts || {};
        var id = opts.id || 'island-' + (++_islandIdCounter);
        var island = document.createElement('div');
        island.className = 'floating-island' + (opts.extraClass ? ' ' + opts.extraClass : '');
        island.id = id;
        island.style.width  = opts.width  || '600px';
        island.style.height = opts.height || 'auto';
        island.style.left   = opts.left   || '50%';
        island.style.top    = opts.top    || '60px';
        if (!opts.left) {
            island.style.transform = 'translateX(-50%)';
        }

        var header = document.createElement('div');
        header.className = 'floating-island__header';
        var titleEl = document.createElement('h3');
        titleEl.className = 'floating-island__title';
        titleEl.textContent = title;
        var closeBtn = document.createElement('button');
        closeBtn.className = 'floating-island__close';
        closeBtn.innerHTML = '✕';
        closeBtn.addEventListener('click', function() { island.remove(); });
        header.appendChild(titleEl);
        header.appendChild(closeBtn);

        var body = document.createElement('div');
        body.className = 'floating-island__body';
        body.innerHTML = content;

        var resizeHandle = document.createElement('div');
        resizeHandle.className = 'floating-island__resize-handle';

        island.appendChild(header);
        island.appendChild(body);
        island.appendChild(resizeHandle);

        _attachIslandDrag(island, header);
        _attachIslandResize(island, resizeHandle);

        document.body.appendChild(island);
        _bringToFront(island);
        return island;
    };

    Helpers.attachDrag = function(island) {
        if (!island) return;
        var header = island.querySelector('.island-titlebar') ||
                     island.querySelector('.floating-island__header');
        if (!header) return;
        var resizeHandle = island.querySelector('.floating-island__resize-handle');
        _attachIslandDrag(island, header);
        _attachIslandResize(island, resizeHandle);
    };

    document.querySelectorAll('.floating-island').forEach(Helpers.attachDrag);

    var _dragObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mut) {
            mut.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    if (node.matches && node.matches('.floating-island')) {
                        Helpers.attachDrag(node);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('.floating-island').forEach(Helpers.attachDrag);
                    }
                }
            });
        });
    });
        if (document.body) {
        _dragObserver.observe(document.body, { childList: true, subtree: true });
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            _dragObserver.observe(document.body, { childList: true, subtree: true });
        });
    }


    // ─── AJAX, Islands, etc. (unchanged from current file) ──────────

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

                // Detect if the AJAX response already contains a full SpawnFloatingIsland() output
                if (html.indexOf('class="floating-island"') !== -1) {
                    // Remove the blank loading island
                    if (island.parentNode) island.parentNode.removeChild(island);

                    // Parse and append the real island from the response
                    var temp = document.createElement('div');
                    temp.innerHTML = html;
                    var realIsland = temp.querySelector('.floating-island');
                    if (realIsland) {
                        document.body.appendChild(realIsland);
                        _bringToFront(realIsland);

                        // Add drag behavior using the proven pattern from openPreviewIsland()
                        var island = realIsland;
                        var header = island.querySelector('.floating-island__header');
                        if (header) {
                            var offsetX = 0, offsetY = 0, dragging = false;
                            header.addEventListener('mousedown', function(e) {
                                if (e.target.closest('.floating-island__close')) return;
                                dragging = true;
                                var rect = island.getBoundingClientRect();
                                offsetX = e.clientX - rect.left;
                                offsetY = e.clientY - rect.top;
                                island.style.cursor = 'grabbing';
                                island.style.transition = 'none';
                                e.preventDefault();
                            });
                            document.addEventListener('mousemove', function(e) {
                                if (!dragging) return;
                                island.style.left = (e.clientX - offsetX) + 'px';
                                island.style.top  = (e.clientY - offsetY) + 'px';
                                island.style.transform = 'none';
                            });
                            document.addEventListener('mouseup', function() {
                                if (!dragging) return;
                                dragging = false;
                                island.style.cursor = '';
                                island.style.transition = '';
                            });
                        }

                        // Execute scripts inside the real island via DOM insertion
                        var scripts = realIsland.querySelectorAll('script');
                        scripts.forEach(function(script) {
                            var newScript = document.createElement('script');
                            if (script.src) {
                                newScript.src = script.src;
                            } else {
                                newScript.textContent = script.textContent;
                            }
                            document.head.appendChild(newScript);
                        });
                    }
                } else {
                    // Normal path: inject HTML into existing island content
                    if (contentDiv) contentDiv.innerHTML = html;

                    // Extract and execute any <script> tags from the HTML
                    // (innerHTML strips scripts, so we re-insert via DOM)
                    var scriptMatches = html.match(/<script[^>]*>([\s\S]*?)<\/script>/gi) || [];
                    scriptMatches.forEach(function(scriptTag) {
                        var code = scriptTag.replace(/<script[^>]*>([\s\S]*?)<\/script>/i, '$1');
                        if (code.trim()) {
                            var newScript = document.createElement('script');
                            newScript.innerHTML = code;
                            contentDiv.appendChild(newScript);
                        }
                    });
                }
            })
            .catch(function(err) {
                var contentDiv = island.querySelector('.island-content');
                if (contentDiv) contentDiv.innerHTML = '<p class="island-error">Failed to load content.</p>';
            });

        return id;
    };


    Helpers.closeIsland = function(id) {
        var el = document.getElementById('island-' + id);
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    };

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

    Helpers.refresh = function() {
        window.onbeforeunload = null;
        $(window).off('beforeunload');
        location.reload();
    };

    Helpers.formatBytes = function(bytes) {
        if (isNaN(bytes) || bytes === 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
    };

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

    Helpers.data = function(el) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el || !el.dataset) return {};
        return Object.assign({}, el.dataset);
    };

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
