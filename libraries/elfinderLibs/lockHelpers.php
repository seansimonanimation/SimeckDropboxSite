<?php
/**
 * Lock-related helpers and access control callbacks for elFinder.
 * Enforces file locking under /files/Projects/ and handles lock override logic.
 */

/**
 * Check if a file is locked.
 * @param string $filepath  Root-relative path, e.g. "/files/Projects/..."
 * @return array|false  Lock row if locked, false otherwise.
 */
function IsFileLocked($filepath) {
    $stmt = $GLOBALS['db']->prepare(
        'SELECT lockid, locktime, assetlock, commentlock 
         FROM lockedfiles WHERE filepath = ?'
    );
    $stmt->execute([$filepath]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
}

/**
 * Get all locked file info under a given directory.
 * @param string $directory  Root-relative dir, e.g. "/files/Projects/internal/P01_C City/"
 * @return array  Array of lock rows (lockid, filepath, locktime, assetlock, commentlock).
 */


/**
 * Get a client's available lock overrides.
 * @param string $username  Client's username.
 * @return int
 */
function GetClientLockOverrides($username) {
    $stmt = $GLOBALS['db']->prepare(
        'SELECT lock_overrides FROM clients WHERE username = ?'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['lock_overrides'] : 0;
}

/**
 * Consume (decrement by 1) a client's lock override.
 * @param string $username  Client's username.
 * @return void
 */
function ConsumeClientLockOverride($username) {
    $stmt = $GLOBALS['db']->prepare(
        'UPDATE clients SET lock_overrides = lock_overrides - 1 
         WHERE username = ? AND lock_overrides > 0'
    );
    $stmt->execute([$username]);
}

/**
 * Normalize an absolute filesystem path to a root-relative /files/… path.
 * @param string $absPath  Absolute path, e.g. "C:/xampp/htdocs/files/Projects/…"
 * @return string|false    e.g. "/files/Projects/…" or false if outside __ROOT__.
 */
function NormalizeFilePath($absPath) {
    $absPath = str_replace('\\', '/', $absPath);
    $root    = str_replace('\\', '/', __ROOT__);
    if (strpos($absPath, $root) !== 0) {
        return false;
    }
    $relative = substr($absPath, strlen($root));
    if (strpos($relative, '/') !== 0) {
        $relative = '/' . $relative;
    }
    return $relative;
}

/**
 * elFinder accessControl callback for dot-file hiding and impersonation mode.
 * Called per-file/dir during elFinder operations.
 */
function access($attr, $path, $data, $volume, $isDir, $relpath) {
    $basename = basename($path);
    
    // Deny write operations when impersonating
    if (isset($_SESSION['impersonating']) && $_SESSION['impersonating'] === true) {
        if ($attr == 'write' || $attr == 'locked') {
            return false;
        }
        return $attr == 'read' ? true : null;
    }
    
    // ── File locking enforcement (only on write, for files, under /files/Projects) ──
    if ($attr === 'write' && !$isDir) {
        $normalized = NormalizeFilePath($path);
        if ($normalized && strpos($normalized, '/files/Projects') === 0) {
            $lock = IsFileLocked($normalized);
            if ($lock) {
                $role = $_SESSION['role'] ?? '';
                if ($role === 'admin' || $role === 'artist') {
                    // allow — fall through to dotfile check
                } elseif ($role === 'client') {
                    $overrides = GetClientLockOverrides($_SESSION['username']);
                    if ($overrides > 0) {
                        ConsumeClientLockOverride($_SESSION['username']);
                        // allow — fall through
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
    }
    
    // Dot-file hiding
    return $basename[0] === '.'
             && strlen($relpath) !== 1
        ? !($attr == 'read' || $attr == 'write')
        :  null;
}
function GetLockedFilesForDirectoryRecursively($directory) {
    $directory = rtrim($directory, '/') . '/%';
    $stmt = $GLOBALS['db']->prepare(
        'SELECT lockid, filepath, locktime, assetlock, commentlock FROM lockedfiles WHERE filepath LIKE ?'
    );
    $stmt->execute([$directory]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function GetLockedFilesForDirectory($directory) {
    $directory = rtrim($directory, '/') . '/%';
    $stmt = $GLOBALS['db']->prepare(
        'SELECT lockid, filepath, locktime, assetlock, commentlock FROM lockedfiles WHERE filepath LIKE ? AND filepath NOT LIKE ?'
    );
    $stmt->execute([$directory, $directory . '%/%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}