<?php
//The module responsible for dashboard content on the admin portal. 
// yep

/**
 * @module artistDashboard
 * @name Dashboard
 * @role artist
 * @nav-text Artist Dashboard
 * @nav-icon dashboard
 * @nav-order 1
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/dashboardlib.php';
include_once __ROOT__ . '/libraries/artistmanagementlib.php';
include_once __ROOT__ . '/libraries/logging.php';
$currentUser = $_SESSION['username'];




if($_SESSION['password'] == '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy'){
    SetActiveModule(('Settings'));
}

?>
<link rel="stylesheet" href="/css/moduleStyle.css" />

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Welcome to the Simeck Artist Portal!</h1>
        <br />
    </div>
    <div class="module-grid">
        <!-- Row 1: 4 cards, each 1 column (no span class needed) -->
        <div class="module-card"><center><h3> Number of active clients</h3>
        <p><?php echo GetClientCount(false); ?></p></center>
    </div>
        <div class="module-card">
            <center><h1>Join the team Discord!</H1></center>
                <iframe src="https://discord.com/widget?id=303707251619921930&theme=dark"
                    width="100%"
                    height="80%"
                    allowtransparency="true"
                    frameborder="0"
                    sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                    style="display:block;border:none;">
            </iframe>
    </div>
        <?php $artistList = GetAllActiveArtists(); ?>
        <div class="module-card module-card--span-1" id="av-checker-card">
            <h2>Team Member Availability Checker</h2>
            <div style="margin-bottom:12px;">
                <label for="av-artist-select">Select an artist:</label>
                <select id="av-artist-select" class="module-input" style="width:100%;">
                    <option value="">-- My Availability --</option>
                    <?php foreach($artistList as $a): ?>
                        <option value="<?php echo htmlspecialchars($a['username']); ?>">
                            <?php echo htmlspecialchars($a['firstname'] . ' ' . $a['lastname'] . ' (' . $a['username'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="av-results" style="margin-top:12px;">
                <!-- Results populate here -->
            </div>
        </div>

        <div class="module-card module-card--span-1" id="datetime-calculator-card">
            <h2>Date/Time Calculator</h2>
            <p style="font-size:0.85em;margin-bottom:12px;">
                Enter a date and time in your timezone to see what it is for another person.
            </p>

            <!-- Two-sided toggle -->
            <div class="module-toggle" id="calcModeToggle">
                <input type="radio" name="calc_mode" id="calcModeArtist" value="artist" checked hidden>
                <input type="radio" name="calc_mode" id="calcModeTz" value="timezone" hidden>
                <label for="calcModeArtist" class="module-toggle__option module-toggle__option--active" data-value="artist">Compare with Artist</label>
                <label for="calcModeTz" class="module-toggle__option" data-value="timezone">Compare with Timezone</label>
                <div class="module-toggle__slider"></div>
            </div>

            <div id="calc-artist-section">
                <div style="margin-bottom:12px;">
                    <label for="dtc-artist-select">Select Artist:</label>
                    <select id="dtc-artist-select" class="module-input" style="width:100%;">
                        <option value="">-- Select Artist --</option>
                        <?php foreach($artistList as $a): ?>
                            <option value="<?php echo htmlspecialchars($a['username']); ?>">
                                <?php echo htmlspecialchars($a['firstname'] . ' ' . $a['lastname'] . ' (' . $a['username'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="calc-tz-section" style="display:none;">
                <div style="margin-bottom:12px;">
                    <label for="dtc-timezone-select">Select Timezone:</label>
                    <select id="dtc-timezone-select" class="module-input" style="width:100%;">
                        <option value="">-- Select Timezone --</option>
                        <?php
                            $tzs = DateTimeZone::listIdentifiers();
                            $currentTz = $_SESSION['timezone'] ?? 'UTC';
                            foreach($tzs as $tz):
                        ?>
                            <option value="<?php echo htmlspecialchars($tz); ?>" <?php echo $tz === $currentTz ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tz); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label for="dtc-date">Date:</label>
                <input type="date" id="dtc-date" class="module-input" style="width:100%;">
            </div>
            <div style="margin-bottom:12px;">
                <label for="dtc-time">Time:</label>
                <input type="time" id="dtc-time" class="module-input" style="width:100%;">
            </div>
            <div id="dtc-result" style="margin-top:12px;padding:12px;border-radius:6px;border:1px solid #ccc;min-height:20px;">
                Select an artist or timezone and enter a date &amp; time to convert.
            </div>
        </div>



<script>
// Two-sided toggle interaction
document.addEventListener('click', function(e) {
    const toggleOption = e.target.closest('.module-toggle__option');
    if (!toggleOption) return;
    const toggle = toggleOption.closest('.module-toggle');
    toggle.querySelectorAll('.module-toggle__option').forEach(opt => {
        opt.classList.remove('module-toggle__option--active');
    });
    toggleOption.classList.add('module-toggle__option--active');
    const value = toggleOption.dataset.value;
    const radio = toggle.querySelector('input[value="' + value + '"]');
    if (radio) radio.checked = true;
    const changeEvent = new Event('change');
    if (radio) radio.dispatchEvent(changeEvent);
});

document.addEventListener('DOMContentLoaded', function(){
    // ── Availability Checker ──
    const select = document.getElementById('av-artist-select');
    const results = document.getElementById('av-results');
    const currentUser = '<?php echo $currentUser; ?>';

    loadAvailability(currentUser);

    select.addEventListener('change', function(){
        loadAvailability(this.value || currentUser);
    });

    function loadAvailability(username){
        fetch('?action=get_artist_availability&username=' + encodeURIComponent(username))
            .then(r => r.json())
            .then(data => {
                const avail = data.available_now === 'Yes' ? '✅ Yes' : '❌ No';
                results.innerHTML = '<div style="border:1px solid #ccc;padding:12px;border-radius:6px;">'
                    + '<strong>Available now: ' + avail + '</strong>'
                    + '<div style="margin-top:8px;">' + data.availability_html + '</div>'
                    + '</div>';
            });
    }

    // ── Date/Time Calculator ──
    const calcModeArtist = document.getElementById('calcModeArtist');
    const calcModeTz = document.getElementById('calcModeTz');
    const calcArtistSection = document.getElementById('calc-artist-section');
    const calcTzSection = document.getElementById('calc-tz-section');
    const dtcSelect = document.getElementById('dtc-artist-select');
    const dtcTzSelect = document.getElementById('dtc-timezone-select');
    const dtcDate = document.getElementById('dtc-date');
    const dtcTime = document.getElementById('dtc-time');
    const dtcResult = document.getElementById('dtc-result');

    function updateDateTimeConversion(){
        const date = dtcDate.value;
        const time = dtcTime.value;
        const isArtistMode = calcModeArtist.checked;

        if(isArtistMode){
            const artist = dtcSelect.value;
            if(!artist || !date || !time){
                dtcResult.innerHTML = 'Select an artist and enter a date &amp; time to convert.';
                dtcResult.style.borderColor = '#ccc';
                return;
            }
            const datetime = date + ' ' + time;
            fetch('?action=convert_datetime&artist=' + encodeURIComponent(artist) + '&datetime=' + encodeURIComponent(datetime))
                .then(r => r.json())
                .then(data => {
                    if(data.error){
                        dtcResult.innerHTML = '<span style="color:red;">Error: ' + data.error + '</span>';
                        dtcResult.style.borderColor = '#e74c3c';
                        return;
                    }
                    dtcResult.innerHTML = '<strong>That\'s ' + data.display + '</strong><br>'
                        + '<span style="font-size:0.9em;">for ' + data.artist_name + ' (' + data.artist_timezone + ')</span>';
                    dtcResult.style.borderColor = '#2ecc71';
                })
                .catch(err => {
                    dtcResult.innerHTML = '<span style="color:red;">Request failed.</span>';
                    dtcResult.style.borderColor = '#e74c3c';
                });
        } else {
            const tz = dtcTzSelect.value;
            if(!tz || !date || !time){
                dtcResult.innerHTML = 'Select a timezone and enter a date &amp; time to convert.';
                dtcResult.style.borderColor = '#ccc';
                return;
            }
            const datetime = date + ' ' + time;
            fetch('?action=convert_datetime&timezone=' + encodeURIComponent(tz) + '&datetime=' + encodeURIComponent(datetime))
                .then(r => r.json())
                .then(data => {
                    if(data.error){
                        dtcResult.innerHTML = '<span style="color:red;">Error: ' + data.error + '</span>';
                        dtcResult.style.borderColor = '#e74c3c';
                        return;
                    }
                    dtcResult.innerHTML = '<strong>That\'s ' + data.display + '</strong><br>'
                        + '<span style="font-size:0.9em;">for ' + data.target_timezone + '</span>';
                    dtcResult.style.borderColor = '#2ecc71';
                })
                .catch(err => {
                    dtcResult.innerHTML = '<span style="color:red;">Request failed.</span>';
                    dtcResult.style.borderColor = '#e74c3c';
                });
        }
    }

    // Toggle sections
    calcModeArtist.addEventListener('change', function(){
        calcArtistSection.style.display = 'block';
        calcTzSection.style.display = 'none';
        updateDateTimeConversion();
    });
    calcModeTz.addEventListener('change', function(){
        calcArtistSection.style.display = 'none';
        calcTzSection.style.display = 'block';
        updateDateTimeConversion();
    });

    dtcSelect.addEventListener('change', updateDateTimeConversion);
    dtcTzSelect.addEventListener('change', updateDateTimeConversion);
    dtcDate.addEventListener('change', updateDateTimeConversion);
    dtcTime.addEventListener('change', updateDateTimeConversion);
});
</script>


        
        <!-- Row 3: 1 card, spanning all 4 columns -->
        <div class="module-card module-card--full"><h1>Changelog</h1>
    <p><?php echo DisplayChangelog(); ?></p></div>
    </div>
</div>
