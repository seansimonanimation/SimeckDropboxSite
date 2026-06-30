/**
 * portfolioChannelBox.js — Properties panel (Maya-style channel box)
 */

const PortfolioChannelBox = {
    scrubState: null,

    /**
     * Initialize channel box bindings.
     */
    init(state) {
        this.state = state;
        this.bindInputs();
        this.bindRemoveButton();
        this.bindScrubLabels();
    },

    /**
     * Bind channel box inputs to update pieces in real time.
     */
    bindInputs() {
        const fields = ['x', 'y', 'z', 'rot', 'scalex', 'scaley', 'caption', 'gallery-order', 'fontsize'];
        for (const field of fields) {
            const input = document.getElementById(`channel-${field}`);
            if (!input) continue;

            input.addEventListener('input', () => {
                if (this.state.readOnly) return;
                if (this.state.selectedIds.size !== 1) return;

                const id = this.state.selectedIds.values().next().value;
                const piece = this.state.pieces.get(id);
                if (!piece) return;

                const numericFields = ['x', 'y', 'z', 'rot', 'scalex', 'scaley', 'gallery-order', 'fontsize'];

                if (field === 'caption') {
                    piece.caption = input.value;
                } else if (field === 'fontsize') {
                    const val = parseInt(input.value);
                    if (val > 0) {
                        piece.fontSize = val;
                        // Update the text element's font-size directly
                        if (piece.type === 'text') {
                            const el = document.querySelector(`.portfolio-piece[data-piece-id="${id}"]`);
                            if (el) {
                                PortfolioRenderer.updatePieceTransform(el, piece);
                            }
                        }
                    }
                } else {
                    const val = parseFloat(input.value);
                    if (!isNaN(val)) {
                        switch (field) {
                            case 'x': piece.x = val; break;
                            case 'y': piece.y = val; break;
                            case 'z': piece.z = val; break;
                            case 'rot': piece.rot = val; break;
                            case 'scalex': piece.scaleX = Math.max(0.01, val); break;
                            case 'scaley': piece.scaleY = Math.max(0.01, val); break;
                            case 'gallery-order': piece.galleryOrder = Math.max(1, Math.round(val)); break;
                        }
                    }
                }

                // Update transform on canvas for transform fields
                const el = document.querySelector(`.portfolio-piece[data-piece-id="${id}"]`);
                if (el && !['caption', 'gallery-order'].includes(field) && field !== 'fontsize') {
                    PortfolioRenderer.updatePieceTransform(el, piece);
                }

                this.state.markDirty();
            });
        }
    },

    /**
     * Bind the remove piece button.
     */
    bindRemoveButton() {
        const btn = document.getElementById('portfolio-remove-piece-btn');
        if (!btn) return;

        btn.addEventListener('click', () => {
            if (this.state.readOnly) return;
            if (this.state.selectedIds.size === 0) return;

            const id = this.state.selectedIds.values().next().value;
            if (typeof PortfolioInteraction.deletePiece === 'function') {
                PortfolioInteraction.deletePiece(id);
            }
        });
    },

    /**
     * Bind Scrub Labels (Maya-style click-drag to adjust values)
     */
    bindScrubLabels() {
        const channelBox = document.getElementById('portfolio-channel-box');
        if (!channelBox) return;

        const sensitivities = {
            'channel-x': 1,
            'channel-y': 1,
            'channel-z': 1,
            'channel-rot': 0.1,
            'channel-scalex': 0.01,
            'channel-scaley': 0.01,
            'channel-fontsize': 0.5
        };

        const labelMap = {
            'X': 'channel-x',
            'Y': 'channel-y',
            'Z-Depth': 'channel-z',
            'Rot': 'channel-rot',
            'Scale X': 'channel-scalex',
            'Scale Y': 'channel-scaley',
            'Font Size': 'channel-fontsize'
        };

        channelBox.addEventListener('mousedown', (e) => {
            const label = e.target.closest('.portfolio-channel-row label');
            if (!label) return;
            if (this.state.readOnly) return;
            if (this.state.selectedIds.size !== 1) return;

            const inputId = labelMap[label.textContent.trim()];
            if (!inputId) return;

            const input = document.getElementById(inputId);
            if (!input) return;

            // If it's Font Size and the selected piece isn't text, ignore
            if (inputId === 'channel-fontsize') {
                const pieceId = this.state.selectedIds.values().next().value;
                const piece = this.state.pieces.get(pieceId);
                if (!piece || piece.type !== 'text') return;
            }

            const sensitivity = sensitivities[inputId] || 1;
            const startVal = parseFloat(input.value) || 0;

            this.scrubState = {
                inputId,
                startX: e.clientX,
                startVal,
                sensitivity,
                pieceId: this.state.selectedIds.values().next().value
            };

            label.style.cursor = 'ew-resize';
            document.body.style.cursor = 'ew-resize';
            e.preventDefault();
        });

        // Show ew-resize cursor on hover for scrub-able labels
        channelBox.addEventListener('mouseover', (e) => {
            const label = e.target.closest('.portfolio-channel-row label');
            if (!label) return;
            if (this.state.readOnly) return;
            if (labelMap[label.textContent.trim()]) {
                // For Font Size, check if piece is text
                if (label.textContent.trim() === 'Font Size') {
                    if (this.state.selectedIds.size !== 1) return;
                    const pieceId = this.state.selectedIds.values().next().value;
                    const piece = this.state.pieces.get(pieceId);
                    if (!piece || piece.type !== 'text') return;
                }
                label.style.cursor = 'ew-resize';
            }
        });

        channelBox.addEventListener('mouseout', (e) => {
            const label = e.target.closest('.portfolio-channel-row label');
            if (!label) return;
            label.style.cursor = '';
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.scrubState) return;

            const dx = e.clientX - this.scrubState.startX;
            const newVal = this.scrubState.startVal + dx * this.scrubState.sensitivity;

            const input = document.getElementById(this.scrubState.inputId);
            if (input) {
                input.value = Math.round(newVal * 100) / 100;
                input.dispatchEvent(new Event('input'));
            }
        });

        document.addEventListener('mouseup', () => {
            if (this.scrubState) {
                const labels = document.querySelectorAll('.portfolio-channel-row label');
                for (const lbl of labels) lbl.style.cursor = '';
                document.body.style.cursor = '';
                this.scrubState = null;
                if (typeof window.__pushUndoState === 'function') {
                    window.__pushUndoState();
                }
            }
        });
    },

    /**
     * Update channel box inputs from the currently selected piece.
     */
    updateFromSelection(state, canvas) {
        const channelBox = document.getElementById('portfolio-channel-box');
        const profileSection = document.getElementById('portfolio-profile-section');
        const fontsizeRow = document.getElementById('channel-fontsize-row');
        if (!channelBox || !profileSection) return;

        if (state.selectedIds.size === 0) {
            channelBox.style.display = 'none';
            profileSection.style.display = 'block';
            return;
        }

        profileSection.style.display = 'none';
        channelBox.style.display = 'block';

        const id = state.selectedIds.values().next().value;
        const piece = state.pieces.get(id);
        if (!piece) {
            channelBox.style.display = 'none';
            profileSection.style.display = 'block';
            return;
        }

        // Show/hide Font Size row based on piece type
        if (fontsizeRow) {
            fontsizeRow.style.display = (piece.type === 'text') ? 'flex' : 'none';
        }

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal('channel-x', Math.round(piece.x * 100) / 100);
        setVal('channel-y', Math.round(piece.y * 100) / 100);
        setVal('channel-z', piece.z);
        setVal('channel-rot', Math.round(piece.rot * 10) / 10);
        setVal('channel-scalex', Math.round(piece.scaleX * 100) / 100);
        setVal('channel-scaley', Math.round(piece.scaleY * 100) / 100);
        setVal('channel-caption', piece.caption || '');
        setVal('channel-gallery-order', piece.galleryOrder || 1);
        setVal('channel-fontsize', piece.fontSize || 14);
    }
};
