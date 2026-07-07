/**
 * portfolioUploader.js — File upload and drag-and-drop for the portfolio canvas
 */

const PortfolioUploader = {
    /**
     * Initialize upload handlers.
     */
    init(state, canvas, canvasWrapper) {
        this.state = state;
        this.canvas = canvas;
        this.canvasWrapper = canvasWrapper;

        this.bindImportButton();
        this.bindDragDrop();
    },

    /**
     * Bind the Add Piece / Import button.
     */
    bindImportButton() {
        const importBtn = document.getElementById('portfolio-import-btn');
        const fileInput = document.getElementById('portfolio-file-input');
        if (!importBtn || !fileInput) return;

        importBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files.length === 0) return;

            for (const file of files) {
                this.uploadFile(file, null, null);
            }

            fileInput.value = '';
        });
    },

    /**
     * Bind drag-and-drop on the canvas wrapper.
     */
    bindDragDrop() {
        const wrapper = this.canvasWrapper;

        wrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            wrapper.classList.add('portfolio-canvas-dragover');
        });

        wrapper.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            wrapper.classList.remove('portfolio-canvas-dragover');
        });

        wrapper.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            wrapper.classList.remove('portfolio-canvas-dragover');

            const files = e.dataTransfer.files;
            if (files.length === 0) return;

            for (const file of files) {
                this.uploadFile(file, null, null);
            }

        });
    },

    /**
     * Upload a file to the portfolio directory and create a piece.
     */
    uploadFile(file, dropX, dropY) {
        if (this.state.readOnly) return;

        const formData = new FormData();
        formData.append('file', file);

        // Show uploading state
        const wrapper = this.canvasWrapper;
        wrapper.classList.add('portfolio-uploading');

        fetch('index.php?action=portfolio_upload', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            wrapper.classList.remove('portfolio-uploading');

            if (!result.success) {
                alert('Upload failed: ' + (result.error || 'Unknown error'));
                return;
            }

            const filename = result.filename;
            const ext = filename.split('.').pop().toLowerCase();
            const mimeMap = {
                'jpg': 'image', 'jpeg': 'image', 'png': 'image', 'gif': 'image',
                'webp': 'image', 'svg': 'image',
                'mp4': 'video', 'webm': 'video',
                'pdf': 'pdf',
                'txt': 'text',
                'mp3': 'audio', 'wav': 'audio', 'ogg': 'audio',
                'flac': 'audio', 'aac': 'audio', 'wma': 'audio'
            };
            const type = mimeMap[ext] || 'image';

            const piece = {
                id: PortfolioSerializer.generateId(),
                type: type,
                filename: type === 'text' ? null : filename,
                x: dropX !== null ? dropX : 0,
                y: dropY !== null ? dropY : 0,
                z: this.getNextZ(),
                rot: 0,
                scaleX: 1.0,
                scaleY: 1.0,
                galleryOrder: this.state.pieces.size + 1,
                caption: '',
                textContent: type === 'text' ? '(new text piece)' : null
            };

            // If text file, read content
            if (type === 'text') {
                piece.filename = filename;
                const reader = new FileReader();
                reader.onload = (e) => {
                    piece.textContent = e.target.result;
                    this.finalizePiece(piece);
                };
                reader.readAsText(file);
                return;
            }

            this.finalizePiece(piece);
        })
        .catch(err => {
            wrapper.classList.remove('portfolio-uploading');
            alert('Upload error: ' + err.message);
        });
    },

    /**
     * Add the piece to the canvas after upload.
     */
    finalizePiece(piece) {
        this.state.pieces.set(piece.id, piece);
        const el = PortfolioRenderer.createPieceElement(piece, this.state);
        this.canvas.appendChild(el);

        // Select the new piece
        PortfolioInteraction.deselectAll();
        this.state.selectedIds.add(piece.id);
        el.classList.add('portfolio-piece-selected');
        PortfolioRenderer.createSelectionHandles(el);
        PortfolioInteraction.updateChannelBox();
        this.state.markDirty();
        // Push undo state
        if (typeof window.__pushUndoState === 'function') {
            window.__pushUndoState();
        }
    },

    /**
     * Get next available z-index.
     */
    getNextZ() {
        let max = 0;
        for (const piece of this.state.pieces.values()) {
            if (piece.z > max) max = piece.z;
        }
        return max + 1;
    }
};
