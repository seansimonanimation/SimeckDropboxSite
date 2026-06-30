/**
 * portfolioInteraction.js — Mouse/touch interactions for the portfolio canvas
 */

const PortfolioInteraction = {
    dragState: null,
    resizeState: null,
    rotateState: null,
    isPanning: false,
    panStart: null,
    isDragging: false,

    /**
     * Initialize all canvas interactions.
     */
    init(state, canvas, canvasWrapper) {
        this.state = state;
        this.canvas = canvas;
        this.canvasWrapper = canvasWrapper;

        this.bindCanvasEvents();
        this.bindKeyboardEvents();
    },

    /**
     * Bind mouse/pointer events on the canvas.
     */
    bindCanvasEvents() {
        const wrapper = this.canvasWrapper;

        // Piece interaction via delegation
        wrapper.addEventListener('mousedown', (e) => this.onMouseDown(e));
        wrapper.addEventListener('mousemove', (e) => this.onMouseMove(e));
        wrapper.addEventListener('mouseup', (e) => this.onMouseUp(e));
        wrapper.addEventListener('mouseleave', (e) => this.onMouseUp(e));

        // Context menu
        wrapper.addEventListener('contextmenu', (e) => this.onContextMenu(e));

        // Zoom
        wrapper.addEventListener('wheel', (e) => this.onWheel(e), { passive: false });

        // Middle-mouse pan
        wrapper.addEventListener('mousedown', (e) => {
            if (e.button === 1) {
                e.preventDefault();
                this.startPan(e);
            }
        });

        // Prevent default drag on canvas
        wrapper.addEventListener('dragstart', (e) => e.preventDefault());
    },

    /**
     * Bind keyboard shortcuts.
     */
    bindKeyboardEvents() {
        document.addEventListener('keydown', (e) => {
            // Delete/Backspace to remove selected
            if ((e.key === 'Delete' || e.key === 'Backspace') && this.state.selectedIds.size > 0) {
                // Don't trigger when typing in an input
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                e.preventDefault();
                this.deleteSelected();
            }
            // Escape to deselect
            if (e.key === 'Escape') {
                this.deselectAll();
            }
            // Ctrl+Z undo
            if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                this.state.undo();
            }
            // Ctrl+Shift+Z or Ctrl+Y redo
            if ((e.ctrlKey && e.key === 'y') || (e.ctrlKey && e.key === 'z' && e.shiftKey)) {
                e.preventDefault();
                this.state.redo();
            }
            // Space for pan mode
            if (e.key === ' ' && !e.repeat) {
                this.canvasWrapper.style.cursor = 'grab';
            }
        });

        document.addEventListener('keyup', (e) => {
            if (e.key === ' ') {
                this.canvasWrapper.style.cursor = '';
            }
        });
    },

    /**
     * Handle mouse down on canvas.
     */
    onMouseDown(e) {
        if (e.button === 1) return; // Middle mouse handled separately
        if (e.button !== 0) return; // Left click only

        // Click on empty canvas = start panning (works even in read-only)
        if (e.target === this.canvas || e.target.classList.contains('portfolio-canvas-wrapper')) {
            this.deselectAll();
            this.startPan(e);
            return;
        }

        if (this.state.readOnly) return;

        // Space + drag = pan (handled in keydown)
        if (e.target.closest('.portfolio-handle-rotate')) {
            e.stopPropagation();
            this.startRotate(e);
            return;
        }

        if (e.target.closest('.portfolio-handle')) {
            e.stopPropagation();
            this.startResize(e);
            return;
        }

        const pieceEl = e.target.closest('.portfolio-piece');
        if (pieceEl && !e.target.closest('.portfolio-selection-handles')) {
            e.stopPropagation();
            const id = pieceEl.dataset.pieceId;

            if (e.shiftKey || e.ctrlKey) {
                if (this.state.selectedIds.has(id)) {
                    this.state.selectedIds.delete(id);
                    pieceEl.classList.remove('portfolio-piece-selected');
                    PortfolioRenderer.removeSelectionHandles(pieceEl);
                } else {
                    this.state.selectedIds.add(id);
                    pieceEl.classList.add('portfolio-piece-selected');
                    PortfolioRenderer.createSelectionHandles(pieceEl);
                }
            } else {
                if (!this.state.selectedIds.has(id)) {
                    this.deselectAll();
                    this.state.selectedIds.add(id);
                    pieceEl.classList.add('portfolio-piece-selected');
                    PortfolioRenderer.createSelectionHandles(pieceEl);
                }
            }

            this.updateChannelBox();
            this.startDrag(e);
            return;
        }

    },

    /**
     * Start dragging pieces.
     */
    startDrag(e) {
        if (this.state.selectedIds.size === 0) return;
        this.isDragging = true;
        this.dragState = {
            startX: e.clientX,
            startY: e.clientY,
            offsets: []
        };

        // Store initial positions of all selected pieces
        for (const id of this.state.selectedIds) {
            const piece = this.state.pieces.get(id);
            if (piece) {
                this.dragState.offsets.push({
                    id,
                    origX: piece.x,
                    origY: piece.y
                });
            }
        }
    },

    /**
     * Start resize from a handle.
     */
    startResize(e) {
        const handle = e.target.closest('.portfolio-handle');
        if (!handle) return;

        const pieceEl = handle.closest('.portfolio-piece');
        if (!pieceEl) return;

        const id = pieceEl.dataset.pieceId;
        const piece = this.state.pieces.get(id);
        if (!piece) return;

        this.resizeState = {
            id,
            handle: handle.dataset.handle,
            startX: e.clientX,
            startY: e.clientY,
            origScaleX: piece.scaleX,
            origScaleY: piece.scaleY,
            origX: piece.x,
            origY: piece.y,
            piece
        };
    },

    /**
     * Start rotation.
     */
startRotate(e) {
    const handle = e.target.closest('.portfolio-handle-rotate');
    if (!handle) return;

    const pieceEl = handle.closest('.portfolio-piece');
    if (!pieceEl) return;

    const id = pieceEl.dataset.pieceId;
    const piece = this.state.pieces.get(id);
    if (!piece) return;

    // Get the piece's center in screen coordinates
    const pieceRect = pieceEl.getBoundingClientRect();
    const pieceCenterX = pieceRect.left + pieceRect.width / 2;
    const pieceCenterY = pieceRect.top + pieceRect.height / 2;

    // Calculate initial angle from piece center to mouse
    const startAngle = Math.atan2(
        e.clientY - pieceCenterY,
        e.clientX - pieceCenterX
    ) * (180 / Math.PI);

    this.rotateState = {
        id,
        pieceCenterX,
        pieceCenterY,
        startAngle,
        origRot: piece.rot,
        piece
    };
},


    /**
     * Handle mouse move.
     */
    onMouseMove(e) {
        if (this.isPanning) {
            this.doPan(e);
            return;
        }

        if (this.rotateState) {
            this.doRotate(e);
            return;
        }

        if (this.resizeState) {
            this.doResize(e);
            return;
        }

        if (this.isDragging && this.dragState) {
            this.doDrag(e);
            return;
        }
    },

    /**
     * Execute drag.
     */
    doDrag(e) {
        const dx = e.clientX - this.dragState.startX;
        const dy = e.clientY - this.dragState.startY;

        // Divide by zoom for canvas-space movement
        const z = this.state.zoom;
        const canvasDx = dx / z;
        const canvasDy = dy / z;

        for (const offset of this.dragState.offsets) {
            const piece = this.state.pieces.get(offset.id);
            if (piece) {
                piece.x = offset.origX + canvasDx;
                piece.y = offset.origY + canvasDy;
                const el = this.canvas.querySelector(`[data-piece-id="${offset.id}"]`);
                if (el) {
                    PortfolioRenderer.updatePieceTransform(el, piece);
                }
            }
        }

        this.updateChannelBox();
        this.state.markDirty();
    },

    /**
     * Execute resize.
     */
    doResize(e) {
        const rs = this.resizeState;
        const dx = (e.clientX - rs.startX) / this.state.zoom;
        const dy = (e.clientY - rs.startY) / this.state.zoom;
        const piece = rs.piece;
        const shift = e.shiftKey;

        let newSx = rs.origScaleX;
        let newSy = rs.origScaleY;

        switch (rs.handle) {
            case 'se':
                newSx = Math.max(0.05, rs.origScaleX + dx / 100);
                newSy = Math.max(0.05, rs.origScaleY + dy / 100);
                if (shift) {
                    const avg = (newSx + newSy) / 2;
                    newSx = avg;
                    newSy = avg;
                }
                break;
            case 'sw':
                newSx = Math.max(0.05, rs.origScaleX - dx / 100);
                newSy = Math.max(0.05, rs.origScaleY + dy / 100);
                if (shift) {
                    const avg = (newSx + newSy) / 2;
                    newSx = avg;
                    newSy = avg;
                }
                break;
            case 'ne':
                newSx = Math.max(0.05, rs.origScaleX + dx / 100);
                newSy = Math.max(0.05, rs.origScaleY - dy / 100);
                if (shift) {
                    const avg = (newSx + newSy) / 2;
                    newSx = avg;
                    newSy = avg;
                }
                break;
            case 'nw':
                newSx = Math.max(0.05, rs.origScaleX - dx / 100);
                newSy = Math.max(0.05, rs.origScaleY - dy / 100);
                if (shift) {
                    const avg = (newSx + newSy) / 2;
                    newSx = avg;
                    newSy = avg;
                }
                break;
            case 'e':
                newSx = Math.max(0.05, rs.origScaleX + dx / 100);
                if (shift) newSy = newSx / (rs.origScaleX / rs.origScaleY || 1);
                break;
            case 'w':
                newSx = Math.max(0.05, rs.origScaleX - dx / 100);
                if (shift) newSy = newSx / (rs.origScaleX / rs.origScaleY || 1);
                break;
            case 'n':
                newSy = Math.max(0.05, rs.origScaleY - dy / 100);
                if (shift) newSx = newSy * (rs.origScaleX / rs.origScaleY || 1);
                break;
            case 's':
                newSy = Math.max(0.05, rs.origScaleY + dy / 100);
                if (shift) newSx = newSy * (rs.origScaleX / rs.origScaleY || 1);
                break;
        }

        piece.scaleX = newSx;
        piece.scaleY = newSy;

        const el = this.canvas.querySelector(`[data-piece-id="${rs.id}"]`);
        if (el) {
            PortfolioRenderer.updatePieceTransform(el, piece);
        }

        this.updateChannelBox();
        this.state.markDirty();
    },


    /**
     * Execute rotation.
     */
doRotate(e) {
    const rs = this.rotateState;

    // Calculate current angle from piece center to mouse
    const currentAngle = Math.atan2(
        e.clientY - rs.pieceCenterY,
        e.clientX - rs.pieceCenterX
    ) * (180 / Math.PI);

    // Delta from initial angle
    let newRot = rs.origRot + (currentAngle - rs.startAngle);

    // Snap to 15° if shift held
    if (e.shiftKey) {
        newRot = Math.round(newRot / 15) * 15;
    }

    rs.piece.rot = newRot;
    const el = this.canvas.querySelector(`[data-piece-id="${rs.id}"]`);
    if (el) {
        PortfolioRenderer.updatePieceTransform(el, rs.piece);
    }

    this.updateChannelBox();
    this.state.markDirty();
},

    /**
     * Handle mouse up.
     */
    onMouseUp(e) {
        if (this.isDragging && this.dragState) {
            this.isDragging = false;
            this.dragState = null;
            this.pushUndoState();
        }
        if (this.resizeState) {
            this.resizeState = null;
            this.pushUndoState();
        }
        if (this.rotateState) {
            this.rotateState = null;
            this.pushUndoState();
        }
        if (this.isPanning) {
            this.isPanning = false;
            this.canvasWrapper.style.cursor = '';
        }
    },

    /**
     * Start panning.
     */
    startPan(e) {
        this.isPanning = true;
        this.panStart = {
            x: e.clientX - (this.state.panX || 0),
            y: e.clientY - (this.state.panY || 0)
        };
        this.canvasWrapper.style.cursor = 'grabbing';
    },

    /**
     * Execute pan.
     */
    doPan(e) {
        if (!this.panStart) return;
        this.state.panX = e.clientX - this.panStart.x;
        this.state.panY = e.clientY - this.panStart.y;
        PortfolioRenderer.applyViewTransform(this.canvas, this.state.zoom, this.state.panX, this.state.panY);
    },

    /**
     * Handle wheel zoom.
     */
    onWheel(e) {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.05 : 0.05;
            const newZoom = Math.max(0.1, Math.min(5, this.state.zoom + delta));
            this.state.zoom = newZoom;
            PortfolioRenderer.applyViewTransform(this.canvas, this.state.zoom, this.state.panX, this.state.panY);
            PortfolioRenderer.updateZoomIndicator(this.state.zoom);
        }
    },

    /**
     * Handle context menu.
     */
    onContextMenu(e) {
        const pieceEl = e.target.closest('.portfolio-piece');
        if (!pieceEl || this.state.readOnly) return;

        e.preventDefault();
        const id = pieceEl.dataset.pieceId;

        // Ensure it's selected
        if (!this.state.selectedIds.has(id)) {
            this.deselectAll();
            this.state.selectedIds.add(id);
            pieceEl.classList.add('portfolio-piece-selected');
            PortfolioRenderer.createSelectionHandles(pieceEl);
            this.updateChannelBox();
        }

        this.showContextMenu(e.clientX, e.clientY, id);
    },

    /**
     * Show the right-click context menu.
     */
    showContextMenu(x, y, pieceId) {
        // Remove existing menu
        const existing = document.querySelector('.portfolio-context-menu');
        if (existing) existing.remove();

        const menu = document.createElement('div');
        menu.className = 'portfolio-context-menu';
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';

        const items = [
            { label: 'Delete', action: () => this.deletePiece(pieceId) },
            { label: 'Duplicate', action: () => this.duplicatePiece(pieceId) },
            { label: 'Bring to Front', action: () => this.bringToFront(pieceId) },
            { label: 'Send to Back', action: () => this.sendToBack(pieceId) },
            { label: 'Bring Forward', action: () => this.bringForward(pieceId) },
            { label: 'Send Backward', action: () => this.sendBackward(pieceId) }
        ];

        for (const item of items) {
            const div = document.createElement('div');
            div.className = 'portfolio-context-item';
            div.textContent = item.label;
            div.addEventListener('click', () => {
                item.action();
                menu.remove();
            });
            menu.appendChild(div);
        }

        document.body.appendChild(menu);

        // Close on click outside
        setTimeout(() => {
            document.addEventListener('click', () => menu.remove(), { once: true });
        }, 0);
    },

    /**
     * Delete selected piece.
     */
    deletePiece(id) {
        if (this.state.readOnly) return;
        const piece = this.state.pieces.get(id);
        if (!piece) return;

        if (confirm('Delete this piece from the canvas?') === false) return;

        // Ask about file deletion
        if (piece.filename) {
            if (confirm('Delete file "' + piece.filename + '" from storage too?')) {
                // Delete file via AJAX
                fetch('index.php?action=portfolio_delete_file', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ filename: piece.filename })
                }).catch(err => console.error('Failed to delete file:', err));
            }
        }

        this.state.pieces.delete(id);
        this.state.selectedIds.delete(id);

        const el = this.canvas.querySelector(`[data-piece-id="${id}"]`);
        if (el) el.remove();

        this.updateChannelBox();
        this.state.markDirty();
        this.pushUndoState();
    },

    /**
     * Delete all selected pieces.
     */
    deleteSelected() {
        const ids = Array.from(this.state.selectedIds);
        for (const id of ids) {
            this.deletePiece(id);
        }
    },

    /**
     * Duplicate a piece.
     */
    duplicatePiece(id) {
        if (this.state.readOnly) return;
        const original = this.state.pieces.get(id);
        if (!original) return;

        const newPiece = {
            ...original,
            id: PortfolioSerializer.generateId(),
            x: original.x + 20,
            y: original.y + 20,
            z: this.getMaxZ() + 1,
            galleryOrder: this.state.pieces.size + 1
        };

        this.state.pieces.set(newPiece.id, newPiece);
        const el = PortfolioRenderer.createPieceElement(newPiece, this.state);
        this.canvas.appendChild(el);

        this.deselectAll();
        this.state.selectedIds.add(newPiece.id);
        el.classList.add('portfolio-piece-selected');
        PortfolioRenderer.createSelectionHandles(el);
        this.updateChannelBox();
        this.state.markDirty();
        this.pushUndoState();
    },

    bringToFront(id) {
        if (this.state.readOnly) return;
        const maxZ = this.getMaxZ();
        const piece = this.state.pieces.get(id);
        if (piece) {
            piece.z = maxZ + 1;
            const el = this.canvas.querySelector(`[data-piece-id="${id}"]`);
            if (el) el.style.zIndex = piece.z;
            this.state.markDirty();
            this.updateChannelBox();
            this.pushUndoState();
        }
    },

    sendToBack(id) {
        if (this.state.readOnly) return;
        const minZ = this.getMinZ();
        const piece = this.state.pieces.get(id);
        if (piece) {
            piece.z = minZ - 1;
            const el = this.canvas.querySelector(`[data-piece-id="${id}"]`);
            if (el) el.style.zIndex = piece.z;
            this.state.markDirty();
            this.updateChannelBox();
            this.pushUndoState();
        }
    },

    bringForward(id) {
        if (this.state.readOnly) return;
        const piece = this.state.pieces.get(id);
        if (piece) {
            piece.z += 1;
            const el = this.canvas.querySelector(`[data-piece-id="${id}"]`);
            if (el) el.style.zIndex = piece.z;
            this.state.markDirty();
            this.updateChannelBox();
            this.pushUndoState();
        }
    },

    sendBackward(id) {
        if (this.state.readOnly) return;
        const piece = this.state.pieces.get(id);
        if (piece) {
            piece.z -= 1;
            const el = this.canvas.querySelector(`[data-piece-id="${id}"]`);
            if (el) el.style.zIndex = piece.z;
            this.state.markDirty();
            this.updateChannelBox();
            this.pushUndoState();
        }
    },

    getMaxZ() {
        let max = 0;
        for (const piece of this.state.pieces.values()) {
            if (piece.z > max) max = piece.z;
        }
        return max;
    },

    getMinZ() {
        let min = 0;
        for (const piece of this.state.pieces.values()) {
            if (piece.z < min) min = piece.z;
        }
        return min;
    },

    /**
     * Deselect all pieces.
     */
    deselectAll() {
        for (const id of this.state.selectedIds) {
            const el = this.canvas.querySelector(`[data-piece-id="${id}"]`);
            if (el) {
                el.classList.remove('portfolio-piece-selected');
                PortfolioRenderer.removeSelectionHandles(el);
            }
        }
        this.state.selectedIds.clear();
        this.updateChannelBox();
    },

    /**
     * Update channel box with selected piece properties.
     */
    updateChannelBox() {
        if (typeof PortfolioChannelBox !== 'undefined') {
            PortfolioChannelBox.updateFromSelection(this.state, this.canvas);
        }
    },

    /**
     * Push current state to undo stack.
     */
    pushUndoState() {
        if (typeof window.__pushUndoState === 'function') {
            window.__pushUndoState();
        }
    }
};
