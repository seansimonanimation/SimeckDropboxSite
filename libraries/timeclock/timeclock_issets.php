<?php
/**
 * Timeclock GET action handlers shared by admin + artist modules.
 * Call the appropriate function from module.php before any HTML output.
 */

function RunAdminTimeclockIssets() {
    if (isset($_GET['clockout_all'])) {
        ClockEveryoneOut();
    }
    if (isset($_GET['delete_shift_id'])) {
        DeleteShift($_GET['delete_shift_id']);
    }
    if (isset($_GET['update_shift_field'])) {
        $shiftId = (int)($_GET['shift_id'] ?? 0);
        $field   = $_GET['field'] ?? '';
        $value   = $_GET['value'] ?? '';
        if ($shiftId && $field) {
            UpdateTimeclockShiftField($shiftId, $field, $value);
            LogSimeckAction(
                'Updated timeclock shift',
                ($_SESSION['username'] ?? 'user') . " updated shift ID $shiftId field $field to value: $value",
                'System'
            );
        }
    }
}

function RunArtistTimeclockIssets() {
    if (!IsImpersonating()) {
        if (isset($_GET['clock_in'])) {
            ArtistClockIn($_SESSION['username']);
        }
        if (isset($_GET['clock_out'])) {
            ArtistClockOut($_SESSION['username']);
        }
    }
    if (isset($_GET['update_shift_field'])) {
        $shiftId = (int)($_GET['shift_id'] ?? 0);
        $field   = $_GET['field'] ?? '';
        $value   = $_GET['value'] ?? '';
        if ($shiftId && $field) {
            UpdateTimeclockShiftField($shiftId, $field, $value);
        }
    }
    if (isset($_GET['download_file'])) {
        InitiateDownload($_GET['download']);
    }
}

