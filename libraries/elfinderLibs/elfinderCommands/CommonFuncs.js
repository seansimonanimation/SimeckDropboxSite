
//
    this.GetAllLockedFilesInProjects = function(projectPath) {
        var fm = this.fm;
        $.post('libraries/elfinderLibs/endpoints/GetLockedSubfilesInProjectEndpoint.php', {
            directory: projectPath
        }, function(response) {
            if (response.success) {
                fm.cache.lockedPaths = response.lockedPaths;
            }
        }, 'json');
    }


    this.GetCommentLockStatus = function(fileUrl) {
        var fm = this.fm;
        if (fm.cache && fm.cache.lockedPaths && fm.cache.lockedPaths[fileUrl]) {
            return fm.cache.lockedPaths[fileUrl].commentlock;
        }
        return false;
    };

    SimeckRefresh = function(){
        window.onbeforeunload = null;
        $(window).off('beforeunload');  // In case it was attached via jQuery
        location.reload();
    }
function populateLockCache(fm) {
    $.post('libraries/elfinderLibs/endpoints/GetLockedfilesInProjectEndpoint.php', {}, function(response) {
        if (!response.success || !response.lockedFiles) return;
        if (!fm.cache) fm.cache = {};
        fm.cache.lockedPaths = {};
        response.lockedFiles.forEach(function(lock) {
            var normalizedPath = normalizeSimeckFilePath(lock.filepath);
            fm.cache.lockedPaths[normalizedPath] = {
                assetlock: lock.assetlock,
                commentlock: lock.commentlock,
                deliverable: lock.deliverable == 1
            };
        });
        refreshLockOverrides(fm);
    }, 'json');
}


function normalizeSimeckFilePath(path) {
    if (!path) return '';
    path = path.replace(/\\/g, '/');
    path = path.replace(/\+/g, ' ');
    try {
        path = decodeURIComponent(path);
    } catch (e) {
        // leave as-is if decode fails
    }
    return path;
}

function getSimeckLockFilePath(fm, hash) {
    var relPath = fm.path(hash) || '';
    relPath = relPath.replace(/\\/g, '/').replace(/^\/+/, '');
    relPath = normalizeSimeckFilePath(relPath);
    return '/files/Projects/' + relPath;
}

// ── elFinder Hash Decode ──────────────────────────────────────────
// Converts elFinder's custom base64 hash to a filesystem path string
function decodeElfinderHash(hash) {
    // Strip the volume ID prefix (e.g., "s1_", "s2_", "t1_", "l1_")
    var underscoreIndex = hash.indexOf('_');
    if (underscoreIndex === -1) return null;
    var b64 = hash.substring(underscoreIndex + 1);
    b64 = b64.replace(/-/g, '+').replace(/_/g, '/').replace(/\./g, '=');
    try { return atob(b64); } catch(e) { return null; }
}



// ── elFinder Path Encode ──────────────────────────────────────────
// Converts a filesystem path to elFinder's custom base64 hash format
function encodeElfinderPath(path) {
    return btoa(path)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '.');
}

// ── Clipboard Copy with Fallback ──────────────────────────────────
// Copies text to clipboard. Falls back to prompt() if clipboard API unavailable.
// fm parameter is optional — omitting it skips notify messages.
function copyToClipboard(text, successMsg, fm) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            if (fm) fm.notify({ type: 'info', msg: successMsg || 'Copied to clipboard!' });
        }).catch(function() {
            prompt('Copy this text (Ctrl+C, then Enter):', text);
        });
    } else {
        prompt('Copy this text (Ctrl+C, then Enter):', text);
    }
}

// ── Normalize File Path ───────────────────────────────────────────
function normalizeSimeckFilePath(path) {
    if (!path) return '';
    path = path.replace(/\\/g, '/');
    path = path.replace(/\+/g, ' ');
    try { path = decodeURIComponent(path); } catch (e) {}
    return path;
}

// ── Get Lock File Path from Hash ──────────────────────────────────
function getSimeckLockFilePath(fm, hash) {
    var relPath = fm.path(hash) || '';
    relPath = relPath.replace(/\\/g, '/').replace(/^\/+/, '');
    relPath = normalizeSimeckFilePath(relPath);
    return '/files/Projects/' + relPath;
}

// ── PoC Requirement Check ─────────────────────────────────────────
function hasPoCRequirementForHash(hash) {
    var session = window.simeckSession;
    if (!session || !session.projectLeaders) return false;

    var path = decodeElfinderHash(hash);
    if (!path) return false;

    var match = path.match(/clientProjects\/([^\/]+)/);
    if (!match) return false;

    return session.projectLeaders[match[1]];
}

// ── Deliverable File Check ────────────────────────────────────────
function isDeliverableFile(hash, fm) {
    if (!fm.cache || !fm.cache.lockedPaths) return false;

    var relPath = decodeElfinderHash(hash);
    relPath = relPath.replace(/\\/g, '/').replace(/^\/+/, '');
    var fileUrl = '/files/Projects/' + relPath;

    if (fm.cache.lockedPaths[fileUrl]) {
        return fm.cache.lockedPaths[fileUrl].deliverable;
    }
    return false;
}

// ── Deliverable Cache Populator ───────────────────────────────────
function populateDeliverableCache(fm) {
    $.post('libraries/elfinderLibs/endpoints/GetLockedfilesInProjectEndpoint.php', {}, function(response) {
        if (!response.success || !response.lockedFiles) return;
        if (!fm.cache) fm.cache = {};
        fm.cache.lockedPaths = {};
        response.lockedFiles.forEach(function(lock) {
            var normalizedPath = normalizeSimeckFilePath(lock.filepath);
            fm.cache.lockedPaths[normalizedPath] = {
                assetlock: lock.assetlock,
                commentlock: lock.commentlock,
                deliverable: lock.deliverable == 1
            };
        });
    }, 'json');
}
