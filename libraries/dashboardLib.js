/**
 * dashboardLib.js — Client dashboard interactions.
 * Depends on Helpers (libraries/helpers.js, loaded in index.php <head>).
 */
var ClientDashboard = (function() {
    'use strict';

    var config = {
        pocUsername: '',
        viewerTimezone: 'UTC',
        defaultDate: '',
        defaultTime: ''
    };

    var els = {};

    function cacheElements() {
        els.pocStatus      = document.getElementById('cd-poc-status');
        els.pocSection     = document.getElementById('cd-poc-section');
        els.tzSection      = document.getElementById('cd-tz-section');
        els.calcModePoc    = document.getElementById('cdCalcModePoc');
        els.calcModeTz     = document.getElementById('cdCalcModeTz');
        els.tzSelect       = document.getElementById('cd-tz-select');
        els.dateInput      = document.getElementById('cd-date');
        els.timeInput      = document.getElementById('cd-time');
        els.result         = document.getElementById('cd-convert-result');
    }

    function setResult(html, ok) {
        if (!els.result) return;
        els.result.innerHTML = html;
        els.result.style.borderColor = ok ? '#2ecc71' : '#e74c3c';
    }

    function loadPoCAvailability() {
        if (!config.pocUsername || !els.pocStatus) return;
        Helpers.get('?action=get_artist_availability', { username: config.pocUsername })
            .then(function(data) {
                var available = data.available_now === 'Yes';
                els.pocStatus.textContent = available ? '✅ Available now' : '❌ Not available now';
                els.pocStatus.style.color = available ? 'var(--color-success, #2ecc71)' : 'var(--color-text, #e74c3c)';
                if (data.availability_html) {
                    var tip = document.createElement('div');
                    tip.style.fontSize = '0.8em';
                    tip.style.marginTop = '4px';
                    tip.style.color = 'var(--color-text-muted, #6c7280)';
                    tip.innerHTML = data.availability_html;
                    els.pocStatus.parentElement.appendChild(tip);
                }
            })
            .catch(function() {
                if (els.pocStatus) {
                    els.pocStatus.textContent = '❓ Availability unknown';
                    els.pocStatus.style.color = 'var(--color-text-muted, #6c7280)';
                }
            });
    }

    function updateConversion() {
        if (!els.dateInput || !els.timeInput || !els.result) return;
        var date = els.dateInput.value;
        var time = els.timeInput.value;
        if (!date || !time) {
            setResult('Pick a date and time to convert.', false);
            return;
        }
        var datetime = date + ' ' + time;
        var params = { datetime: datetime };
        if (els.calcModePoc.checked) {
            if (!config.pocUsername) {
                setResult('No Point of Contact assigned.', false);
                return;
            }
            params.artist = config.pocUsername;
        } else {
            var tz = els.tzSelect ? els.tzSelect.value : '';
            if (!tz) {
                setResult('Select a time zone.', false);
                return;
            }
            params.timezone = tz;
        }
        Helpers.get('?action=convert_datetime', params)
            .then(function(data) {
                if (data.error) {
                    setResult('Error: ' + data.error, false);
                    return;
                }
                var isPoc = els.calcModePoc.checked;
                var opt = els.tzSelect ? els.tzSelect.options[els.tzSelect.selectedIndex] : null;
                var targetLabel = isPoc ? config.pocUsername : (opt ? opt.text : params.timezone);
                var targetTz = isPoc ? (data.artist_timezone || config.pocUsername) : (data.target_timezone || params.timezone);
                var targetName = isPoc ? (data.artist_name || config.pocUsername) : targetLabel;
                setResult(
                    '<strong>That is ' + data.display + '</strong>' +
                    '<br><span style="font-size:0.85em; color:var(--color-text-muted, #6c7280);">' +
                    'for ' + htmlEscape(targetName) + ' (' + htmlEscape(targetTz) + ')' +
                    '</span>',
                    true
                );
            })
            .catch(function(err) {
                setResult('Conversion failed: ' + (err.message || err), false);
            });
    }

    function htmlEscape(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function initToggle() {
        if (!els.calcModePoc || !els.calcModeTz || !els.pocSection || !els.tzSection) return;
        function toggleSections() {
            var pocMode = els.calcModePoc.checked;
            els.pocSection.style.display = pocMode ? 'block' : 'none';
            els.tzSection.style.display  = pocMode ? 'none' : 'block';
            updateConversion();
        }
        els.calcModePoc.addEventListener('change', toggleSections);
        els.calcModeTz.addEventListener('change', toggleSections);
    }

    return {
        init: function(cfg) {
            config = Object.assign({}, config, cfg || {});
            cacheElements();
            loadPoCAvailability();
            initToggle();
            if (els.dateInput)  els.dateInput.addEventListener('change',  updateConversion);
            if (els.timeInput)  els.timeInput.addEventListener('change',  updateConversion);
            if (els.tzSelect)   els.tzSelect.addEventListener('change',   updateConversion);
            updateConversion();
        }
    };
})();
