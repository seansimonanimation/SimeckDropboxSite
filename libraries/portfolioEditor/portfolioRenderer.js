/**
 * portfolioRenderer.js — DOM rendering for portfolio pieces
 */

const PortfolioRenderer = {
    /**
     * Create a DOM element for a portfolio piece.
     */
    createPieceElement(piece, state) {
        const el = document.createElement('div');
        el.className = 'portfolio-piece';
        el.dataset.pieceId = piece.id;
        el.style.zIndex = piece.z;

        // Inner content based on type
        const inner = document.createElement('div');
        inner.className = 'portfolio-piece-inner';

        if (piece.type === 'text') {
            inner.className += ' portfolio-piece-text';
            inner.textContent = piece.textContent || 'Double-click to edit';
            inner.style.fontSize = (piece.fontSize || 14) + 'px';
            el.title = 'Double-click to edit text';

            // Set explicit dimensions (not using CSS scale for text)
            const bw = piece.baseWidth || 200;
            const bh = piece.baseHeight || 48;
            el.style.width = Math.round(bw * piece.scaleX) + 'px';
            el.style.height = Math.round(bh * piece.scaleY) + 'px';
        } else if (piece.type === 'video') {
            inner.className += ' portfolio-piece-video';
            const thumbUrl = state.portfolioDir + '/' + piece.filename + '.thumb.jpg';
            const img = document.createElement('img');
            img.className = 'portfolio-piece-thumb';
            img.src = thumbUrl;
            img.alt = piece.filename;
            img.onerror = function() {
                this.style.display = 'none';
            };
            inner.appendChild(img);
            const playOverlay = document.createElement('div');
            playOverlay.className = 'portfolio-piece-play-overlay';
            playOverlay.innerHTML = '&#9654;';
            inner.appendChild(playOverlay);
        } else if (piece.type === 'pdf') {
            inner.className += ' portfolio-piece-pdf';
            const icon = document.createElement('div');
            icon.className = 'portfolio-piece-pdf-icon';
            icon.textContent = 'PDF';
            inner.appendChild(icon);
            const label = document.createElement('div');
            label.className = 'portfolio-piece-label';
            label.textContent = piece.filename;
            inner.appendChild(label);
        } else {
            // image (default)
            inner.className += ' portfolio-piece-image';
            const img = document.createElement('img');
            img.className = 'portfolio-piece-img';
            img.src = state.portfolioDir + '/' + piece.filename;
            img.alt = piece.filename;
            img.draggable = false;
            inner.appendChild(img);
        }

        el.appendChild(inner);
        this.updatePieceTransform(el, piece);
        return el;
    },

    /**
     * Update a piece element's CSS transform.
     */
    updatePieceTransform(el, piece) {
        el.style.zIndex = piece.z;
        if (piece.type === 'text') {
            // Text pieces: no CSS scale — use explicit width/height instead
            el.style.transform = `translate(${piece.x}px, ${piece.y}px) rotate(${piece.rot}deg)`;
            const bw = piece.baseWidth || 200;
            const bh = piece.baseHeight || 48;
            el.style.width = Math.round(bw * Math.abs(piece.scaleX)) + 'px';
            el.style.height = Math.round(bh * Math.abs(piece.scaleY)) + 'px';
            // Update font size
            const inner = el.querySelector('.portfolio-piece-text');
            if (inner) {
                inner.style.fontSize = (piece.fontSize || 14) + 'px';
            }
        } else {
            // All other piece types use CSS scale normally
            el.style.transform = `translate(${piece.x}px, ${piece.y}px) rotate(${piece.rot}deg) scale(${piece.scaleX}, ${piece.scaleY})`;
        }
    },


    /**
     * Create selection handles around a piece.
     */
    createSelectionHandles(pieceEl) {
        const container = document.createElement('div');
        container.className = 'portfolio-selection-handles';

        // Corner handles
        const positions = ['nw', 'ne', 'sw', 'se'];
        for (const pos of positions) {
            const handle = document.createElement('div');
            handle.className = `portfolio-handle portfolio-handle-corner portfolio-handle-${pos}`;
            handle.dataset.handle = pos;
            container.appendChild(handle);
        }

        // Edge handles
        const edges = ['n', 's', 'e', 'w'];
        for (const edge of edges) {
            const handle = document.createElement('div');
            handle.className = `portfolio-handle portfolio-handle-edge portfolio-handle-${edge}`;
            handle.dataset.handle = edge;
            container.appendChild(handle);
        }

        // Rotation handle
        const rotHandle = document.createElement('div');
        rotHandle.className = 'portfolio-handle portfolio-handle-rotate';
        rotHandle.dataset.handle = 'rotate';
        container.appendChild(rotHandle);

        pieceEl.appendChild(container);
    },

    /**
     * Remove selection handles from a piece.
     */
    removeSelectionHandles(pieceEl) {
        const handles = pieceEl.querySelector('.portfolio-selection-handles');
        if (handles) {
            handles.remove();
        }
    },

    /**
     * Rebuild all pieces on the canvas from state.
     */
    renderAllPieces(state, canvas) {
        // Clear canvas (keep background)
        const pieces = canvas.querySelectorAll('.portfolio-piece');
        for (const p of pieces) p.remove();

        // Sort by z-order
        const sorted = Array.from(state.pieces.values()).sort((a, b) => a.z - b.z);

        for (const piece of sorted) {
            const el = this.createPieceElement(piece, state);
            canvas.appendChild(el);
        }

        // Re-apply selection
        for (const id of state.selectedIds) {
            const el = canvas.querySelector(`[data-piece-id="${id}"]`);
            if (el) {
                el.classList.add('portfolio-piece-selected');
                this.createSelectionHandles(el);
            }
        }
    },

    /**
     * Update the zoom indicator in the canvas.
     */
    updateZoomIndicator(zoom) {
        const text = document.getElementById('portfolio-zoom-text');
        if (text) {
            text.textContent = Math.round(zoom * 100) + '%';
        }
    },

    /**
     * Apply zoom and pan transform to the canvas.
     */
    applyViewTransform(canvas, zoom, panX, panY) {
        canvas.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
        canvas.style.transformOrigin = '0 0';
    }
};
