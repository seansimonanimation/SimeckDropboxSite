/**
 * portfolioGridLabels.js — Pixel coordinate axis overlay
 * Adds numbered ruler markers along the top (X) and left (Y) axes
 */
(function() {
    'use strict';

    function initGridLabels(state, canvas) {
        const canvasWrapper = document.getElementById('portfolio-canvas-wrapper');
        if (!canvasWrapper) return;

        // Create the canvas-bounded grid background (inside the canvas, so it zooms/pans)
        let gridBg = canvas.querySelector('.portfolio-canvas-grid-bg');
        if (!gridBg) {
            gridBg = document.createElement('div');
            gridBg.className = 'portfolio-canvas-grid-bg';
            canvas.appendChild(gridBg);
        }

        // Create overlay container for label numbers
        let overlay = canvasWrapper.querySelector('.portfolio-grid-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'portfolio-grid-overlay';
            overlay.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:2;overflow:hidden;';
            canvasWrapper.appendChild(overlay);
        }

        function updateLabels() {
            overlay.innerHTML = '';
            const zoom = state.zoom || 1;
            const panX = state.panX || 0;
            const panY = state.panY || 0;
            const wrapperRect = canvasWrapper.getBoundingClientRect();
            const w = wrapperRect.width;
            const h = wrapperRect.height;

            const baseStep = 100;
            const step = baseStep * zoom;

            const startX = Math.floor(-panX / step) * baseStep;
            const startY = Math.floor(-panY / step) * baseStep;
            const endX = startX + Math.ceil(w / step) * baseStep;
            const endY = startY + Math.ceil(h / step) * baseStep;

            const frag = document.createDocumentFragment();

            // ── X-axis labels (clamped to canvas bounds -960 to 960) ──
            for (let x = startX; x <= endX; x += baseStep) {
                if (x < -960 || x > 960) continue;
                const screenX = x * zoom + panX;
                if (screenX < 0 || screenX > w) continue;

                const label = document.createElement('div');
                label.className = 'portfolio-grid-label portfolio-grid-label-x';
                label.style.left = screenX + 'px';
                label.textContent = x;
                frag.appendChild(label);
            }

            // ── Y-axis labels (clamped to canvas bounds -540 to 540) ──
            for (let y = startY; y <= endY; y += baseStep) {
                if (y < -540 || y > 540) continue;
                const screenY = y * zoom + panY;
                if (screenY < 0 || screenY > h) continue;

                const label = document.createElement('div');
                label.className = 'portfolio-grid-label portfolio-grid-label-y';
                label.style.top = screenY + 'px';
                label.textContent = y;
                frag.appendChild(label);
            }

            overlay.appendChild(frag);
        }

        // Initial render
        updateLabels();

        // Re-render on zoom/pan changes
        const origApply = PortfolioRenderer.applyViewTransform;
        if (origApply) {
            PortfolioRenderer.applyViewTransform = function(canvasEl, zoom, panX, panY) {
                origApply(canvasEl, zoom, panX, panY);
                updateLabels();
            };
        }

        // Also update on resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateLabels, 100);
        });
    }

    window.__gridLabelsReady = true;
    window.initGridLabels = initGridLabels;
})();
