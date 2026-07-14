/**
 * portfolioEditor.js — Main module: initialization, state, toolbar, undo/redo, save
 */

(function() {
    'use strict';

    const config = window.__PORTFOLIO_CONFIG__;
    if (!config) {
        console.error('Portfolio Editor: No config found');
        return;
    }

    // ── State ──
    const state = {
        pieces: new Map(),
        selectedIds: new Set(),
        history: [],
        historyIndex: -1,
        zoom: 1.0,
        panX: 0,
        panY: 0,
        background: '',
        profile: {
            username: config.username,
            displayName: config.displayName,
            bio: '',
            links: []
        },
        publishPortfolio: false,
        dirty: false,
        readOnly: config.readOnly,
        portfolioDir: config.portfolioDir,
        fileTokens: config.fileTokens || {},
        canvasWidth: 1920,
        canvasHeight: 1080,

        autoSaveTimer: null,

        markDirty() {
            this.dirty = true;
            if (this.autoSaveTimer) clearTimeout(this.autoSaveTimer);
            this.autoSaveTimer = setTimeout(() => savePortfolio(), 800);
        },

        markClean() {
            this.dirty = false;
        },


        pushUndoState() {
            // Save current piece positions
            const snapshot = [];
            for (const piece of this.pieces.values()) {
                snapshot.push({
                    id: piece.id,
                    type: piece.type,
                    filename: piece.filename,
                    x: piece.x,
                    y: piece.y,
                    z: piece.z,
                    rot: piece.rot,
                    scaleX: piece.scaleX,
                    scaleY: piece.scaleY,
                    galleryOrder: piece.galleryOrder,
                    caption: piece.caption,
                    textContent: piece.textContent
                });
            }

            // Trim future states if we're in the middle of history
            if (this.historyIndex < this.history.length - 1) {
                this.history = this.history.slice(0, this.historyIndex + 1);
            }

            this.history.push(snapshot);
            this.historyIndex++;

            // Limit history size
            if (this.history.length > 50) {
                this.history.shift();
                this.historyIndex--;
            }

            this.updateUndoRedoButtons();
        },

        undo() {
            if (this.historyIndex <= 0) return;
            this.historyIndex--;
            this.restoreHistory(this.history[this.historyIndex]);
            this.updateUndoRedoButtons();
        },

        redo() {
            if (this.historyIndex >= this.history.length - 1) return;
            this.historyIndex++;
            this.restoreHistory(this.history[this.historyIndex]);
            this.updateUndoRedoButtons();
        },

        restoreHistory(snapshot) {
            this.pieces.clear();
            for (const data of snapshot) {
                this.pieces.set(data.id, {
                    id: data.id,
                    type: data.type,
                    filename: data.filename,
                    x: data.x,
                    y: data.y,
                    z: data.z,
                    rot: data.rot,
                    scaleX: data.scaleX,
                    scaleY: data.scaleY,
                    galleryOrder: data.galleryOrder,
                    caption: data.caption,
                    textContent: data.textContent
                });
            }

            PortfolioRenderer.renderAllPieces(this, canvas);
            this.selectedIds.clear();
            PortfolioInteraction.updateChannelBox();
            this.markDirty();
        },

        updateUndoRedoButtons() {
            const undoBtn = document.getElementById('portfolio-undo-btn');
            const redoBtn = document.getElementById('portfolio-redo-btn');
            if (undoBtn) undoBtn.disabled = this.historyIndex <= 0;
            if (redoBtn) redoBtn.disabled = this.historyIndex >= this.history.length - 1;
        }
    };

    // Expose pushUndoState globally for interaction modules
    window.__pushUndoState = () => state.pushUndoState();

    // ── DOM refs ──
    const canvas = document.getElementById('portfolio-canvas');
    const canvasWrapper = document.getElementById('portfolio-canvas-wrapper');

    if (!canvas || !canvasWrapper) {
        console.error('Portfolio Editor: Canvas elements not found');
        return;
    }

    // ── Load portfolio data ──
    const deserialized = PortfolioSerializer.deserialize(config.portfolioJson);
    state.pieces = deserialized.pieces;
    state.profile = deserialized.profile;
    state.publishPortfolio = deserialized.publishPortfolio;

    // Fill in display name if empty
    if (!state.profile.displayName) {
        state.profile.displayName = config.displayName;
    }

    // ── Initialize ──
    PortfolioRenderer.renderAllPieces(state, canvas);
    PortfolioInteraction.init(state, canvas, canvasWrapper);
    PortfolioUploader.init(state, canvas, canvasWrapper);
    PortfolioProfilePanel.init(state);
    PortfolioChannelBox.init(state);
    
    // Zoom to fit all pieces after layout is complete
    requestAnimationFrame(() => zoomToFit());

    // Push initial state
    state.pushUndoState();

    // ── Toolbar button bindings ──

    // Undo
    document.getElementById('portfolio-undo-btn')?.addEventListener('click', () => state.undo());

    // Redo
    document.getElementById('portfolio-redo-btn')?.addEventListener('click', () => state.redo());

    // Add Text
    document.getElementById('portfolio-add-text-btn')?.addEventListener('click', () => {
        if (state.readOnly) return;
        const id = PortfolioSerializer.generateId();
        const piece = {
            id,
            type: 'text',
            filename: null,
            x: 0,
            y: 0,
            z: PortfolioUploader.getNextZ ? PortfolioUploader.getNextZ() : state.pieces.size + 1,
            rot: 0,
            scaleX: 1.0,
            scaleY: 1.0,
            galleryOrder: state.pieces.size + 1,
            caption: '',
            textContent: 'Double-click to edit, then paste in HTML.',
            fontSize: 14,
            baseWidth: 200,
            baseHeight: 48
        };
        state.pieces.set(id, piece);
        const el = PortfolioRenderer.createPieceElement(piece, state);
        canvas.appendChild(el);
        PortfolioInteraction.deselectAll();
        state.selectedIds.add(id);
        el.classList.add('portfolio-piece-selected');
        PortfolioRenderer.createSelectionHandles(el);
        PortfolioInteraction.updateChannelBox();
        state.markDirty();
        state.pushUndoState();
    });

    // Publish toggle
    const publishCheckbox = document.getElementById('portfolio-publish-checkbox');
    if (publishCheckbox) {
        publishCheckbox.addEventListener('change', () => {
            state.publishPortfolio = publishCheckbox.checked;
            state.markDirty();
        });
    }

    // Gallery Order button
    document.getElementById('portfolio-gallery-order-btn')?.addEventListener('click', () => {
        showGalleryOrder();
    });

    // Gallery close button
    document.getElementById('portfolio-gallery-close-btn')?.addEventListener('click', () => {
        document.getElementById('portfolio-gallery-island').style.display = 'none';
    });

    // Gallery done button
    document.getElementById('portfolio-gallery-done-btn')?.addEventListener('click', () => {
        applyGalleryOrder();
        document.getElementById('portfolio-gallery-island').style.display = 'none';
    });
    // Fit to Screen button
    document.getElementById('portfolio-zoom-fit-btn')?.addEventListener('click', () => {
        zoomToFit();
    });

    // Zoom indicator click = fit to screen
    document.getElementById('portfolio-zoom-indicator')?.addEventListener('click', () => {
        zoomToFit();
    });

    // ── Double-click text editing ──
    canvas.addEventListener('dblclick', (e) => {
        if (state.readOnly) return;
        const pieceEl = e.target.closest('.portfolio-piece');
        if (!pieceEl) return;
        const id = pieceEl.dataset.pieceId;
        const piece = state.pieces.get(id);
        if (!piece || piece.type !== 'text') return;

        const inner = pieceEl.querySelector('.portfolio-piece-inner');
        if (!inner) return;

        const textarea = document.createElement('textarea');
        textarea.className = 'portfolio-text-editor';
        textarea.value = piece.textContent || '';
        textarea.style.width = inner.offsetWidth + 'px';
        textarea.style.height = inner.offsetHeight + 'px';

        inner.innerHTML = '';
        inner.appendChild(textarea);
        textarea.focus();

        const saveText = () => {
            const newContent = textarea.value;
            piece.textContent = newContent;
            inner.innerHTML = '';
            inner.textContent = newContent || '(empty)';

            // Save to .txt file
            if (piece.filename) {
                fetch('index.php?action=portfolio_save_text', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ filename: piece.filename, content: newContent })
                }).catch(err => console.error('Failed to save text:', err));
            }

            state.markDirty();
        };

        textarea.addEventListener('blur', saveText);
        textarea.addEventListener('keydown', (e2) => {
            if (e2.key === 'Escape') {
                textarea.blur();
            }
        });
    });

    // ── Save function ──
    function savePortfolio() {
        const jsonData = PortfolioSerializer.serialize(state);

        // Update display name and bio from inputs
        jsonData.artist.display_name = state.profile.displayName;
        jsonData.artist.bio = state.profile.bio;

        fetch('index.php?action=portfolio_save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jsonData)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                state.markClean();
            } else {
                console.error('Save failed:', result.error || 'Unknown error');
            }
        })
        .catch(err => {
            console.error('Save error:', err.message);
        });
    }



    // ── Gallery Order Floating Island ──
    function showGalleryOrder() {
        const island = document.getElementById('portfolio-gallery-island');
        const list = document.getElementById('portfolio-gallery-list');
        if (!island || !list) return;

        list.innerHTML = '';

        const allPieces = Array.from(state.pieces.values());
        // Stable sort: included pieces first (by galleryOrder), excluded pieces last
        const included = [];
        const excluded = [];
        for (const piece of allPieces) {
            if (piece.galleryOrder && piece.galleryOrder > 0) {
                included.push(piece);
            } else {
                excluded.push(piece);
            }
        }
        included.sort((a, b) => a.galleryOrder - b.galleryOrder);
        const sorted = [...included, ...excluded];


        for (let i = 0; i < sorted.length; i++) {
            const piece = sorted[i];
            const isExcluded = !piece.galleryOrder || piece.galleryOrder === 0;
            const row = document.createElement('div');
            row.className = 'portfolio-gallery-row' + (isExcluded ? ' portfolio-gallery-excluded' : '');
            row.draggable = !isExcluded && !state.readOnly;
            row.dataset.pieceId = piece.id;

            const dragHandle = document.createElement('span');
            dragHandle.className = 'portfolio-gallery-drag-handle';
            dragHandle.textContent = isExcluded ? '\u25CB' : '\u2261'; // circle vs hamburger
            row.appendChild(dragHandle);

            const thumb = document.createElement('span');
            thumb.className = 'portfolio-gallery-thumb';
            let label = piece.filename || piece.type;
            if (piece.type === 'text') label = '"' + (piece.textContent?.substring(0, 30) || 'text') + '"';
            thumb.textContent = label.substring(0, 40) + (isExcluded ? ' (Excluded)' : '');
            row.appendChild(thumb);

            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'portfolio-gallery-toggle-btn';
            if (isExcluded) {
                toggleBtn.textContent = 'Include';
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // Assign next available gallery order
                    const maxOrder = Math.max(0, ...Array.from(state.pieces.values()).map(p => p.galleryOrder || 0));
                    piece.galleryOrder = maxOrder + 1;
                    state.markDirty();
                    showGalleryOrder(); // Re-render
                });
            } else {
                toggleBtn.textContent = 'Remove';
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    piece.galleryOrder = 0;
                    state.markDirty();
                    showGalleryOrder(); // Re-render
                });
            }
            row.appendChild(toggleBtn);

            // Drag events (only for included pieces)
            if (!isExcluded && !state.readOnly) {
                row.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', piece.id);
                    row.classList.add('portfolio-gallery-dragging');
                });

                row.addEventListener('dragend', () => {
                    row.classList.remove('portfolio-gallery-dragging');
                });

                row.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    row.classList.add('portfolio-gallery-dragover');
                });

                row.addEventListener('dragleave', () => {
                    row.classList.remove('portfolio-gallery-dragover');
                });

                row.addEventListener('drop', (e) => {
                    e.preventDefault();
                    row.classList.remove('portfolio-gallery-dragover');
                    const draggedId = e.dataTransfer.getData('text/plain');
                    const targetId = piece.id;
                    if (draggedId === targetId) return;

                    const draggedPiece = state.pieces.get(draggedId);
                    if (draggedPiece) {
                        // Only swap if target is also included
                        if (piece.galleryOrder > 0) {
                            const tempOrder = draggedPiece.galleryOrder;
                            draggedPiece.galleryOrder = piece.galleryOrder;
                            piece.galleryOrder = tempOrder;
                            showGalleryOrder();
                            state.markDirty();
                        }
                    }
                });
            }

            list.appendChild(row);
        }

        island.style.display = 'flex';
    }


    function applyGalleryOrder() {
        const sorted = Array.from(state.pieces.values())
            .filter(p => p.galleryOrder > 0)
            .sort((a, b) => a.galleryOrder - b.galleryOrder);
        for (let i = 0; i < sorted.length; i++) {
            sorted[i].galleryOrder = i + 1;
        }
        state.markDirty();
    }

    // Zoom in/out buttons
    document.getElementById('portfolio-zoom-in-btn')?.addEventListener('click', () => {
        const newZoom = Math.min(5, state.zoom + 0.1);
        state.zoom = newZoom;
        PortfolioRenderer.applyViewTransform(canvas, state.zoom, state.panX, state.panY);
        PortfolioRenderer.updateZoomIndicator(state.zoom);
    });

    document.getElementById('portfolio-zoom-out-btn')?.addEventListener('click', () => {
        const newZoom = Math.max(0.1, state.zoom - 0.1);
        state.zoom = newZoom;
        PortfolioRenderer.applyViewTransform(canvas, state.zoom, state.panX, state.panY);
        PortfolioRenderer.updateZoomIndicator(state.zoom);
    });

    // ── Zoom to Fit ──
    function zoomToFit() {
        if (state.pieces.size === 0) {
            state.zoom = 1;
            state.panX = 0;
            state.panY = 0;
            PortfolioRenderer.applyViewTransform(canvas, state.zoom, state.panX, state.panY);
            PortfolioRenderer.updateZoomIndicator(state.zoom);
            return;
        }

        // Calculate bounding box of all pieces using actual DOM dimensions
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        for (const piece of state.pieces.values()) {
            const el = canvas.querySelector(`[data-piece-id="${piece.id}"]`);
            let w = 200, h = 200;
            if (el) {
                w = el.offsetWidth || 200;
                h = el.offsetHeight || 200;
            } else if (piece.type === 'text') {
                w = (piece.baseWidth || 200) * piece.scaleX;
                h = (piece.baseHeight || 48) * piece.scaleY;
            }
            minX = Math.min(minX, piece.x);
            minY = Math.min(minY, piece.y);
            maxX = Math.max(maxX, piece.x + w);
            maxY = Math.max(maxY, piece.y + h);
        }

        const contentWidth = maxX - minX + 100;
        const contentHeight = maxY - minY + 100;

        const wrapperRect = canvasWrapper.getBoundingClientRect();
        const zoomX = wrapperRect.width / contentWidth;
        const zoomY = wrapperRect.height / contentHeight;
        state.zoom = Math.min(zoomX, zoomY, 2);

        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;
        state.panX = (wrapperRect.width / 2) - (centerX * state.zoom);
        state.panY = (wrapperRect.height / 2) - (centerY * state.zoom);

        PortfolioRenderer.applyViewTransform(canvas, state.zoom, state.panX, state.panY);
        PortfolioRenderer.updateZoomIndicator(state.zoom);
    }


    // ── Apply theme background ──
    function applyThemeBackground() {
        // Get computed background from the module container
        const module = document.querySelector('.portfolio-editor-module');
        if (module) {
            const bg = getComputedStyle(module).backgroundColor;
            if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') {
                canvasWrapper.style.backgroundColor = bg;
            }
        }
    }
    applyThemeBackground();
    
    // ── Initialize Grid Labels ──
    if (typeof initGridLabels === 'function') {
        initGridLabels(state, canvas);
    }

    // ── Cleanup ──
    console.log('Portfolio Editor initialized for', config.username, state.readOnly ? '(read-only)' : '(editable)');

})();
