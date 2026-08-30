
// ==========================================================
// Refreshes the #content area in-place (no redirect)
// ==========================================================
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
    // Re-bind event listeners since the DOM was replaced
    initPageListeners();
}


//Ajax Handler
// ── AJAX helper ──
async function ajaxGet(url) {
    return fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
}


// ==========================================================
// Project assignment
// ==========================================================
async function assignProject(username, pid) {
    if (!pid) return;
    await ajaxGet('?addArtistToProject=' + username + ',' + pid);
    await refreshContent();
}

async function removeProject(username, pid) {
    await ajaxGet('?removeArtistFromProject=' + username + ',' + pid);
    await refreshContent();
}

// ==========================================================
// Secondary Role assignment
// ==========================================================
async function assignSecondaryRole(username, roleName) {
    if (!roleName) return;
    await ajaxGet('?addSecondaryRoleToArtist=' + username + ',' + roleName);
    await refreshContent();
}

async function removeSecondaryRole(username, roleName) {
    await ajaxGet('?removeSecondaryRoleFromArtist=' + username + ',' + roleName);
    await refreshContent();
}

// ==========================================================
// Toggle artist active status
// ==========================================================
async function toggleArtistStatus(artistId, newStatus) {
    await ajaxGet('?artist_id=' + artistId + '&new_status=' + newStatus);
    await refreshContent();
}

// ==========================================================
// Reset artist password
// ==========================================================
async function resetPassword(artistId) {
    await ajaxGet('?reset_pw_for=' + artistId);
    await refreshContent();
}

// ==========================================================
// Delete an artist document
// ==========================================================
async function deleteDocument(docId) {
    await ajaxGet('?delete=' + docId);
    await refreshContent();
}

// ==========================================================
// Upload a document via AJAX with FormData
// ==========================================================
async function uploadDocument(artistId, file) {
    const formData = new FormData();
    formData.append('uploaded_file', file);
    formData.append('artist_id', artistId);

    await fetch(window.location.href, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    });
    await refreshContent();
}

// ==========================================================
// Inline field update — saves on blur / change
// ==========================================================
async function updateArtistField(userID, field, value) {
    await ajaxGet('?action=update_artist_field&user_id=' + userID
        + '&field=' + encodeURIComponent(field)
        + '&value=' + encodeURIComponent(value));
    await refreshContent();
}

// ==========================================================
// Binds all event listeners (called on load AND after refresh)
// ==========================================================
function initPageListeners() {
    // --- Toggle artist status ---
    document.querySelectorAll('.toggle-artist-status').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleArtistStatus(this.dataset.artistId, this.dataset.newStatus);
        });
    });

    // --- Reset password ---
    document.querySelectorAll('.reset-pw-button').forEach(btn => {
        btn.addEventListener('click', function() {
            resetPassword(this.dataset.artistId);
        });
    });

    // --- Delete document ---
    document.querySelectorAll('.delete-artist-document').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            deleteDocument(this.dataset.docId);
        });
    });

    // --- Upload file button (opens file picker) ---
    document.querySelectorAll('.upload-file-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const artistId = this.dataset.artistId;
            const fileInput = document.getElementById('fileUploadInput');
            fileInput.dataset.artistId = artistId;
            fileInput.click();
        });
    });

    // --- File selected → upload via AJAX ---
    const fileInput = document.getElementById('fileUploadInput');
    if (fileInput) {
        const newInput = fileInput.cloneNode(true);
        fileInput.parentNode.replaceChild(newInput, fileInput);
        newInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadDocument(this.dataset.artistId, this.files[0]);
            }
            this.value = '';
        });
    }

    // --- Create artist form ---
    const createForm = document.getElementById('createArtistForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = this.querySelector('[name="username"]').value;
            const firstname = this.querySelector('[name="firstname"]').value;
            const lastname = this.querySelector('[name="lastname"]').value;
            await ajaxGet('?CreateArtist=1&username=' + encodeURIComponent(username)
                + '&firstname=' + encodeURIComponent(firstname)
                + '&lastname=' + encodeURIComponent(lastname));
            await refreshContent();
        });
    }

    // --- Inline edit: text inputs (save on blur if changed) ---
    document.querySelectorAll('.inline-edit-field').forEach(input => {
        const origValue = input.value;
        input.addEventListener('blur', function() {
            if (this.value !== origValue) {
                updateArtistField(this.dataset.userId, this.dataset.field, this.value);
            }
        });
    });

    // --- Inline edit: selects (save on change) ---
    document.querySelectorAll('.inline-edit-select').forEach(sel => {
        sel.addEventListener('change', function() {
            updateArtistField(this.dataset.userId, this.dataset.field, this.value);
        });
    });
}

// ==========================================================
// Initial bind on page load
// ==========================================================
document.addEventListener('DOMContentLoaded', function() {
    initPageListeners();
});