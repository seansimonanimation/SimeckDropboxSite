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
    initVendorPageListeners();
}

// ── Inline field save ──
async function saveVendorField(username, field, value) {
    await ajaxGet('?field=' + encodeURIComponent(field)
        + '&vendor=' + encodeURIComponent(username)
        + '&value=' + encodeURIComponent(value));
    await refreshContent();
}

// ── Toggle active status ──
async function toggleVendorActive(username) {
    await ajaxGet('?toggleActive=' + encodeURIComponent(username));
    await refreshContent();
}

// ── Project assignment ──
async function addVendorProject(username, pid) {
    if (!pid) return;
    await ajaxGet('?addVendorToProject=' + encodeURIComponent(username) + ',' + encodeURIComponent(pid));
    await refreshContent();
}

async function removeVendorProject(username, pid) {
    await ajaxGet('?removeVendorFromProject=' + encodeURIComponent(username) + ',' + encodeURIComponent(pid));
    await refreshContent();
}

// ── Delete document ──
async function deleteVendorDocument(docId) {
    await ajaxGet('?deleteDoc=' + docId);
    await refreshContent();
}

// ── Open floating island for vendor document upload ──
async function openVendorUploadIsland(vendorId) {
    // Remove any existing upload island
    document.querySelectorAll('.floating-island').forEach(el => el.remove());

    const resp = await fetch('libraries/endpoints/vendorUploadIslandEndpoint.php?vendor_id=' + encodeURIComponent(vendorId), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const html = await resp.text();
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    document.body.appendChild(wrapper.firstElementChild);
}

// ── Bind all event listeners ──
function initVendorPageListeners() {
    document.querySelectorAll('.vendor-editable').forEach(el => {
        const newEl = el.cloneNode(true);
        el.parentNode.replaceChild(newEl, el);
        newEl.addEventListener('change', function() {
            const vendor = this.dataset.vendor;
            const field = this.dataset.field;
            const value = this.value;
            saveVendorField(vendor, field, value);
        });
    });

    document.querySelectorAll('.toggle-vendor-status').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleVendorActive(this.dataset.vendor);
        });
    });

    document.querySelectorAll('.delete-vendor-document').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            deleteVendorDocument(this.dataset.docId);
        });
    });

    document.querySelectorAll('.upload-file-button').forEach(btn => {
        btn.addEventListener('click', function() {
            const vendorId = this.dataset.vendorId;
            openVendorUploadIsland(vendorId);
        });
    });

    const createForm = document.getElementById('createVendorForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = this.querySelector('[name="username"]').value;
            const company_name = this.querySelector('[name="company_name"]').value;
            const poc_firstname = this.querySelector('[name="poc_firstname"]').value;
            const poc_lastname = this.querySelector('[name="poc_lastname"]').value;
            const poc = this.querySelector('[name="point_of_contact"]').value;
            const pid = this.querySelector('[name="pid"]').value;
            await ajaxGet('?CreateVendor=1'
                + '&username=' + encodeURIComponent(username)
                + '&company_name=' + encodeURIComponent(company_name)
                + '&poc_firstname=' + encodeURIComponent(poc_firstname)
                + '&poc_lastname=' + encodeURIComponent(poc_lastname)
                + '&point_of_contact=' + encodeURIComponent(poc)
                + '&pid=' + encodeURIComponent(pid));
            await refreshContent();
        });
    }

    const searchForm = document.querySelector('.vendor-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const searchTerm = this.querySelector('[name="searchVendor"]').value;
            await ajaxGet('?searchVendor=' + encodeURIComponent(searchTerm));
            await refreshContent();
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initVendorPageListeners();
});
