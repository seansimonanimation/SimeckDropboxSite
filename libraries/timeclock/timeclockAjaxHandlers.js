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
    initTimeclockPageListeners();
}

// ── Inline field save ──
async function saveTimeclockField(shiftId, field, mysqlDatetime) {
    await ajaxGet('?update_shift_field=1'
        + '&shift_id=' + encodeURIComponent(shiftId)
        + '&field=' + encodeURIComponent(field)
        + '&value=' + encodeURIComponent(mysqlDatetime));
    await refreshContent();
}

// ── Clock out a specific shift (used in admin table) ──
async function clockArtistOut(shiftId) {
    await ajaxGet('libraries/timeclock/clockout.php?shift_id=' + shiftId);
    await refreshContent();
}

// ── Format a Date object to MySQL datetime string ──
function formatToMySQL(d) {
    const pad = n => String(n).padStart(2, '0');
    return d.getFullYear() + '-'
        + pad(d.getMonth() + 1) + '-'
        + pad(d.getDate()) + ' '
        + pad(d.getHours()) + ':'
        + pad(d.getMinutes()) + ':'
        + pad(d.getSeconds());
}

// ── Start inline editing on a timecell with Flatpickr ──
function startInlineEdit(td) {
    if (td.classList.contains('editing')) return;
    td.classList.add('editing');

    const mysqlValue   = td.dataset.mysql || '';
    const shiftId      = td.dataset.shiftId;
    const field        = td.dataset.field;
    const display      = td.querySelector('.atc-display');
    const humanReadable = display.textContent.trim();
    display.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'atc-input';
    input.value = humanReadable;
    td.appendChild(input);

    input.focus();

    // Parse MySQL value for Flatpickr default
    let fpDefault = null;
    if (mysqlValue) {
        const parts = mysqlValue.split(' ');
        fpDefault = new Date(parts[0] + 'T' + (parts[1] || '00:00:00'));
    }

    const fp = flatpickr(input, {
        enableTime: true,
        enableSeconds: false,
        dateFormat: "F j, Y h:i K",
        time_24hr: false,
        defaultDate: fpDefault,
        position: "auto",
        static: true,
        onClose: function(selectedDates, dateStr) {
            if (selectedDates.length > 0 && dateStr && dateStr !== humanReadable) {
                saveTimeclockField(shiftId, field, formatToMySQL(selectedDates[0]));
            } else {
                // No change or cancelled — restore display
                fp.destroy();
                input.remove();
                display.style.display = '';
                td.classList.remove('editing');
            }
        }
    });
    fp.open();

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            fp.close();  // triggers onClose which calls saveTimeclockField
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            fp.destroy();
            input.remove();
            display.style.display = '';
            td.classList.remove('editing');
        }
    });
}

// ── Bind / re-bind all page event listeners ──
function initTimeclockPageListeners() {
    // Inline editable cells
    document.querySelectorAll('.atc-editable').forEach(el => {
        const newEl = el.cloneNode(true);
        el.parentNode.replaceChild(newEl, el);
        newEl.addEventListener('click', function() {
            startInlineEdit(this);
        });
    });

    // Clock-out links (if any exist on this page)
    document.querySelectorAll('.clockout-artist').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            clockArtistOut(this.dataset.shiftId);
        });
    });

    // DataTable init (only if jQuery + DataTable available)
    if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable && document.getElementById('ShiftList') && !$.fn.DataTable.isDataTable('#ShiftList')) {
        $('#ShiftList').DataTable({
            "paging": false,
            "info": false,
            "searching": true,
            "order": [[0, "asc"]]
        });
        $('#artistFilter').off('keyup').on('keyup', function () {
            $('#ShiftList').DataTable().search(this.value).draw();
        });
    }
    initShiftComments();
}
// ── Save shift comment on blur/change ──
async function saveShiftComment(shiftId, value) {
    await ajaxGet('?update_shift_field=1'
        + '&shift_id=' + encodeURIComponent(shiftId)
        + '&field=shift_comments'
        + '&value=' + encodeURIComponent(value));
    // No refreshContent needed — comment updates don't need a page redraw
}

// ── Bind comment textarea auto-save ──
function initShiftComments() {
    document.querySelectorAll('.shift-comment').forEach(textarea => {
        // Clone to remove existing listeners
        const newEl = textarea.cloneNode(true);
        textarea.parentNode.replaceChild(newEl, textarea);

        let saveTimer = null;
        newEl.addEventListener('input', function() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => {
                saveShiftComment(this.dataset.shiftId, this.value);
            }, 800); // 800ms debounce after typing stops
        });
    });
}

document.addEventListener('DOMContentLoaded', initTimeclockPageListeners);
