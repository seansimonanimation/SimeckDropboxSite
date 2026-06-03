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
        <div class="module-card"><center><h3> Number of active artists</h3>
        <p><?php echo GetArtistCount(false); ?></p></center>
    </div>
        <div class="module-card">Card 3</div>
        <div class="module-card">Card 4</div>
        
        <!-- Row 2: 2 cards, each spanning 2 columns -->
        <div class="module-card module-card--span-2">Card 5</div>
        <?php $artistList = GetAllActiveArtists(); ?>
        <div class="module-card module-card--span-1" id="av-checker-card">
            <h2>Team Member Availability Checker</h2>
            <div style="margin-bottom:12px;">
                <label for="av-artist-select">Select an artist:</label>
                <select id="av-artist-select" class="module-input" style="width:100%;max-width:400px;">
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


<script>
document.addEventListener('DOMContentLoaded', function(){
    const select = document.getElementById('av-artist-select');
    const results = document.getElementById('av-results');
    const currentUser = '<?php echo $currentUser; ?>';

    // Show current user's availability by default
    loadAvailability(currentUser);

    // On selection change
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
});
</script>


        <div class="module-card module-card--span-1">Card 6</div>
        
        <!-- Row 3: 1 card, spanning all 4 columns -->
        <div class="module-card module-card--full"><h1>Changelog</h1>
    <p><?php echo DisplayChangelog(); ?></p></div>
    </div>
</div>