// ── AJAX helper ──
async function ajaxGet(url) {
    return fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
}

// ── Refresh #content in-place ──
async function refreshContent() {
    const resp = await fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const html = await resp.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newContent = doc.querySelector('#content');
    if (newContent) {
        document.querySelector('#content').innerHTML = newContent.innerHTML;
    }
    initClientPageListeners();
}

// ── Inline field save (triggered on change) ──
async function saveClientField(email, field, value) {
    await ajaxGet('?field=' + encodeURIComponent(field)
        + '&email=' + encodeURIComponent(email)
        + '&value=' + encodeURIComponent(value));
    await refreshContent();
}

// ── Toggle active status ──
async function toggleClientActive(email) {
    await ajaxGet('?toggleActive=' + encodeURIComponent(email));
    await refreshContent();
}

// ── Project assignment ──
async function addClientProject(email, pid) {
    if (!pid) return;
    await ajaxGet('?addClientToProject=' + encodeURIComponent(email) + ',' + encodeURIComponent(pid));
    await refreshContent();
}

async function removeClientProject(email, pid) {
    await ajaxGet('?removeClientFromProject=' + encodeURIComponent(email) + ',' + encodeURIComponent(pid));
    await refreshContent();
}

// ── Delete document ──
async function deleteClientDocument(docId) {
    await ajaxGet('?deleteDoc=' + docId);
    await refreshContent();
}

// ── Upload document via AJAX ──
async function uploadClientDocument(clientId, file) {
    const formData = new FormData();
    formData.append('uploaded_file', file);
    formData.append('client_id', clientId);
    await fetch(window.location.href, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    });
    await refreshContent();
}

// ── Bind all event listeners ──
function initClientPageListeners() {
    // Inline editable fields (text inputs, selects)
    document.querySelectorAll('.client-editable').forEach(el => {
        // Remove old listener to avoid duplicates by cloning
        const newEl = el.cloneNode(true);
        el.parentNode.replaceChild(newEl, el);
        newEl.addEventListener('change', function() {
            const email = this.dataset.email;
            const field = this.dataset.field;
            const value = this.value;
            saveClientField(email, field, value);
        });
    });

    // Toggle active status
    document.querySelectorAll('.toggle-client-status').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleClientActive(this.dataset.email);
        });
    });

    // Delete document
    document.querySelectorAll('.delete-client-document').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            deleteClientDocument(this.dataset.docId);
        });
    });

    // Upload file button
    document.querySelectorAll('.upload-file-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.dataset.clientId;
            const fileInput = document.getElementById('clientFileUploadInput');
            fileInput.dataset.clientId = clientId;
            fileInput.click();
        });
    });

    // File selected → upload
    const fileInput = document.getElementById('clientFileUploadInput');
    if (fileInput) {
        const newInput = fileInput.cloneNode(true);
        fileInput.parentNode.replaceChild(newInput, fileInput);
        newInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadClientDocument(this.dataset.clientId, this.files[0]);
            }
            this.value = '';
        });
    }

    // Create client form
    const createForm = document.getElementById('createClientForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = this.querySelector('[name="email"]').value;
            const firstname = this.querySelector('[name="firstname"]').value;
            const lastname = this.querySelector('[name="lastname"]').value;
            const poc = this.querySelector('[name="point_of_contact"]').value;
            const pid = this.querySelector('[name="pid"]').value;
            await ajaxGet('?CreateClient=1&email=' + encodeURIComponent(email)
                + '&firstname=' + encodeURIComponent(firstname)
                + '&lastname=' + encodeURIComponent(lastname)
                + '&point_of_contact=' + encodeURIComponent(poc)
                + '&pid=' + encodeURIComponent(pid));
            await refreshContent();
        });
    }

    // Search form (AJAX)
    const searchForm = document.querySelector('.client-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const searchTerm = this.querySelector('[name="searchClient"]').value;
            await ajaxGet('?searchClient=' + encodeURIComponent(searchTerm));
            await refreshContent();
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initClientPageListeners();
});