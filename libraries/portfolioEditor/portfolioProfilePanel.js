/**
 * portfolioProfilePanel.js — Profile section (PFP, name, bio, links)
 */

const PortfolioProfilePanel = {
    /**
     * Initialize the profile panel.
     */
    init(state) {
        this.state = state;

        this.bindPfpUpload();
        this.bindDisplayName();
        this.bindBio();
        this.bindLinks();
        this.renderLinks();
    },

    /**
     * Bind profile picture upload.
     */
    bindPfpUpload() {
        const pfpInput = document.getElementById('portfolio-pfp-input');
        const pfpArea = document.getElementById('portfolio-pfp-area');
        if (!pfpInput || !pfpArea) return;

        pfpArea.addEventListener('click', () => {
            if (this.state.readOnly) return;
            pfpInput.click();
        });

        pfpInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            fetch('index.php?action=portfolio_upload_pfp', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                if (!result.success) {
                    alert('Failed to upload profile picture: ' + (result.error || 'Unknown error'));
                    return;
                }

                // Update the PFP display
                const img = document.getElementById('portfolio-pfp-img');
                if (img) {
                    img.outerHTML = `<img src="${result.url}?t=${Date.now()}" id="portfolio-pfp-img" class="portfolio-pfp-img" alt="Profile">`;
                }
                this.state.markDirty();
            })
            .catch(err => alert('Upload error: ' + err.message));
        });
    },

    /**
     * Bind display name input.
     */
    bindDisplayName() {
        const input = document.getElementById('portfolio-display-name');
        if (!input) return;

        input.addEventListener('input', () => {
            this.state.profile.displayName = input.value;
            this.state.markDirty();
        });
    },

    /**
     * Bind bio textarea.
     */
    bindBio() {
        const textarea = document.getElementById('portfolio-bio');
        if (!textarea) return;

        textarea.addEventListener('input', () => {
            this.state.profile.bio = textarea.value;
            this.state.markDirty();
        });
    },

    /**
     * Bind links add button.
     */
    bindLinks() {
        const addBtn = document.getElementById('portfolio-link-add-btn');
        if (!addBtn) return;

        addBtn.addEventListener('click', () => {
            this.state.profile.links.push({ label: '', url: '' });
            this.renderLinks();
            this.state.markDirty();
        });
    },

    /**
     * Render the links list.
     */
    renderLinks() {
        const container = document.getElementById('portfolio-links-list');
        if (!container) return;

        container.innerHTML = '';

        for (let i = 0; i < this.state.profile.links.length; i++) {
            const link = this.state.profile.links[i];
            const row = document.createElement('div');
            row.className = 'portfolio-link-row';

            const labelInput = document.createElement('input');
            labelInput.type = 'text';
            labelInput.className = 'portfolio-input portfolio-link-label';
            labelInput.placeholder = 'Label (e.g. ArtStation)';
            labelInput.value = link.label;
            labelInput.disabled = this.state.readOnly;
            labelInput.addEventListener('input', () => {
                this.state.profile.links[i].label = labelInput.value;
                this.state.markDirty();
            });

            const urlInput = document.createElement('input');
            urlInput.type = 'url';
            urlInput.className = 'portfolio-input portfolio-link-url';
            urlInput.placeholder = 'URL (e.g. https://...)';
            urlInput.value = link.url;
            urlInput.disabled = this.state.readOnly;
            urlInput.addEventListener('input', () => {
                this.state.profile.links[i].url = urlInput.value;
                this.state.markDirty();
            });

            row.appendChild(labelInput);
            row.appendChild(urlInput);

            if (!this.state.readOnly) {
                const removeBtn = document.createElement('button');
                removeBtn.className = 'portfolio-link-remove';
                removeBtn.textContent = '\u00d7';
                removeBtn.addEventListener('click', () => {
                    this.state.profile.links.splice(i, 1);
                    this.renderLinks();
                    this.state.markDirty();
                });
                row.appendChild(removeBtn);
            }

            container.appendChild(row);
        }
    }
};
