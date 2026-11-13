<?php
//The module responsible for dashboard content on the client portal.

/**
 * @module clientDashboard
 * @name Dashboard
 * @role client
 * @nav-text Dashboard
 * @nav-icon dashboard
 * @nav-order 1
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/dashboardlib.php';
include_once __ROOT__ . '/libraries/artistmanagementlib.php';
include_once __ROOT__ . '/libraries/elfinderLibs/lockHelpers.php';
include_once __ROOT__ . '/libraries/logging.php';

$clientUsername   = $_SESSION['username']   ?? '';
$clientFirstname  = $_SESSION['firstname']  ?? '';
$clientTimezone   = $_SESSION['timezone']   ?? 'UTC';
$projectAssignments = $_SESSION['project_assignments'] ?? '';
$clientLockOverrides  = (int)($_SESSION['lock_overrides'] ?? 0);
$clientPointOfContact = $_SESSION['point_of_contact'] ?? '';

// Default-password check: matches the client DEFAULT password seed in schema.sql
$DEFAULT_CLIENT_PW_HASH = '$2a$12$ptYB7ciliHwMH7VtkyYu5.nUDVVqo.9rVBmxVB/PtRmkCAFH6Qipq';
$mustChangePassword = ($_SESSION['password'] ?? '') === $DEFAULT_CLIENT_PW_HASH;

// Row 2 card 1 — active / total assigned projects
$projectStats = GetClientProjectStats($projectAssignments);

// Row 2 card 3 — point of contact + availability
$pocData = GetClientPointOfContactCardData($clientPointOfContact);

// Row 2 card 4 — pre-fill date/time "now" in the client's own time zone
$nowLocal = new DateTime('now', new DateTimeZone($clientTimezone));
$defaultDate = $nowLocal->format('Y-m-d');
$defaultTime = $nowLocal->format('H:i');
?>
<link rel="stylesheet" href="/css/moduleStyle.css" />
<script src="/libraries/dashboardLib.js"></script>

<div class="module">
    <div class="module-header">
        <h1 class="module-title">Welcome to the Simeck Client Portal!</h1>
    </div>
    <div class="module-grid">

        <!-- Row 1: Welcome card, columns 2 and 3 -->
        <div class="module-card module-card--welcome">
            <center><h2>Welcome to the Simeck Client Portal, <?php echo htmlspecialchars($clientFirstname ?: $clientUsername); ?>!</h2>
            <?php if ($mustChangePassword): ?>
                <div class="dc-notice dc-notice--warning">
                    <strong>Heads up:</strong> you are still using the default password.
                    Please <a href="index.php?module=Settings">change your password in Settings</a>.
                </div>
            <?php endif; ?>
            <p style="margin-top:10px; font-size:0.9em; color:var(--color-text-muted, #6c7280);">
                Check your projects, lock overrides, point of contact, and time zones from the cards below.</center>
            </p>
        </div>
<div class="module-card module-card--placeholder"></div>
        <!-- Row 2, Card 1: Active projects / total projects -->
        <div class="module-card">
            <h3>Projects</h3>
            <p style="font-size:1.5rem; font-weight:bold; margin:6px 0;">
                <strong><?php echo $projectStats['active']; ?></strong>
                <span style="font-size:0.9em; font-weight:normal; color:var(--color-text-muted, #6c7280);">
                    of <strong><?php echo $projectStats['total']; ?></strong> assigned project(s) active
                </span>
            </p>
            <p style="font-size:0.85em; color:var(--color-text-muted, #6c7280);">
                Browse files: <a href="index.php?module=ProjectManagement">Project Files</a>
            </p>
        </div>

        <!-- Row 2, Card 2: Lock Overrides Available -->
        <div class="module-card">
            <h3>Lock Overrides Available</h3>
            <p style="font-size:2rem; font-weight:bold; margin:6px 0;">
                <?php echo $clientLockOverrides; ?>
            </p>
            <p style="font-size:0.85em; color:var(--color-text-muted, #6c7280);">
                Lock overrides are available for unlocking and revisiting locked/finalized assets.<br /> The first one is free.<br />Additional Lock overrides are $100 per override.
            </p>
        </div>

        <!-- Row 2, Card 3: My Point of Contact + availability -->
        <div class="module-card" id="cd-poc-card">
            <h3>My Point of Contact</h3>
            <p id="cd-poc-name" style="margin:6px 0; font-size:1.1rem;">
                <?php echo $pocData ? htmlspecialchars($pocData['name']) : 'Not assigned'; ?>
            </p>
            <p id="cd-poc-availability" style="margin:4px 0;">
                <?php if ($pocData): ?>
                    <span id="cd-poc-status" style="font-weight:bold;">
                        <?php echo htmlspecialchars($pocData['available_now'] === 'Yes' ? '✅ Available now' : '❌ Not available now'); ?>
                    </span>
                    <br /><span style="font-size:0.85em; color:var(--color-text-muted, #6c7280);">
                        <?php echo htmlspecialchars($pocData['timezone']); ?>
                    </span>
                <?php else: ?>
                    <span style="color:var(--color-text-muted, #6c7280);">No point of contact assigned.</span>
                <?php endif; ?>
            </p>
            <input type="hidden" id="cd-poc-username" value="<?php echo htmlspecialchars($clientPointOfContact); ?>">
        </div>

        <!-- Row 2, Card 4: Time zone converter (Point of Contact or a time zone only) -->
        <div class="module-card" id="cd-converter-card">
            <h3>Time Zone Converter</h3>
            <p style="font-size:0.85em; color:var(--color-text-muted, #6c7280); margin-bottom:10px;">
                Check a date/time against your Point of Contact or another time zone.
            </p>

            <div class="module-toggle" id="cd-calcModeToggle">
                <input type="radio" name="cd_calc_mode" id="cdCalcModePoc" value="poc" checked hidden>
                <input type="radio" name="cd_calc_mode" id="cdCalcModeTz" value="timezone" hidden>
                <label for="cdCalcModePoc" class="module-toggle__option module-toggle__option--active" data-value="poc">
                    <?php echo $pocData ? htmlspecialchars($pocData['name']) : 'Point of Contact'; ?>
                </label>
                <label for="cdCalcModeTz" class="module-toggle__option" data-value="timezone">Time Zone</label>
                <div class="module-toggle__slider"></div>
            </div>

            <div id="cd-poc-section" style="margin-top:10px;">
                <p style="font-size:0.85em; color:var(--color-text-muted, #6c7280); margin-bottom:8px;">
                    Converting to
                    <?php echo htmlspecialchars($pocData ? $pocData['name'] : 'your Point of Contact'); ?>
                    's time zone (
                    <?php echo htmlspecialchars($pocData ? $pocData['timezone'] : 'n/a'); ?>
                    ).
                </p>
            </div>

            <div id="cd-tz-section" style="display:none; margin-top:10px;">
                <div style="margin-bottom:8px;">
                    <label for="cd-tz-select" style="font-size:0.85em; display:block; margin-bottom:4px;">Time Zone:</label>
                    <select id="cd-tz-select" class="module-input" style="width:100%;">
                        <option value="">-- Select Time Zone --</option>
                        <?php
                        $currentTz = $_SESSION['timezone'] ?? 'UTC';
                        foreach (DateTimeZone::listIdentifiers() as $tz):
                        ?>
                            <option value="<?php echo htmlspecialchars($tz); ?>" <?php echo $tz === $currentTz ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tz); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:8px;">
                <label for="cd-date" style="font-size:0.85em; display:block; margin-bottom:4px;">Date:</label>
                <input type="date" id="cd-date" class="module-input" style="width:100%;" value="<?php echo htmlspecialchars($defaultDate); ?>">
            </div>
            <div style="margin-bottom:8px;">
                <label for="cd-time" style="font-size:0.85em; display:block; margin-bottom:4px;">Time:</label>
                <input type="time" id="cd-time" class="module-input" style="width:100%;" value="<?php echo htmlspecialchars($defaultTime); ?>">
            </div>
            <div id="cd-convert-result" style="margin-top:10px; padding:8px; border:1px solid var(--color-border, #ccc); border-radius:var(--radius-sm, 6px); font-size:0.9em; min-height:44px; background:var(--color-bg-raised, #f5f5f5);"></div>
            <input type="hidden" id="cd-poc-tz" value="<?php echo htmlspecialchars($pocData ? $pocData['timezone'] : 'UTC'); ?>">
        </div>

        <!-- Row 3: Changelog, full width -->
        <div class="module-card module-card--full">
            <h2>Changelog</h2>
            <div style="white-space: pre-wrap; font-size:0.9em; line-height:1.5; border:1px solid var(--color-border, #ccc); background:var(--color-bg-raised, #f5f5f5); padding:12px; border-radius:var(--radius-sm, 6px); max-height:420px; overflow-y:auto;">
                <?php echo DisplayChangelog(); ?>
            </div>
        </div>

    </div>
</div>

<script>
    ClientDashboard.init({
        pocUsername: <?php echo json_encode($clientPointOfContact); ?>,
        viewerTimezone: <?php echo json_encode($clientTimezone); ?>,
        defaultDate: <?php echo json_encode($defaultDate); ?>,
        defaultTime: <?php echo json_encode($defaultTime); ?>
    });
</script>
