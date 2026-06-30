<?php
/**
 * portfolioLib.php — Portfolio Editor backend for Simeck Entertainment Dropbox.
 * Handles all file operations for the artist portfolio canvas.
 */

/**
 * Get the filesystem path to an artist's portfolio directory.
 */
function GetPortfolioPath($username): string {
    return __ROOT__ . '/files/Corporate/PortfolioDocuments/' . $username;
}

/**
 * Get the web-accessible path for an artist's portfolio (for display).
 */
function GetPortfolioWebPath($username): string {
    return '/files/Corporate/PortfolioDocuments/' . $username;
}

/**
 * Ensure the portfolio directory exists, create if missing.
 */
function EnsurePortfolioDirectory($username): string {
    $path = GetPortfolioPath($username);
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
    return $path;
}

/**
 * Load and decode portfolio.json for a given artist.
 * Returns the decoded array, or a default structure if no file exists.
 */
function LoadPortfolio($username): array {
    $path = GetPortfolioPath($username) . '/portfolio.json';
    if (!file_exists($path)) {
        return [
            'version' => 1,
            'publish_portfolio' => 0,
            'last_modified' => date('Y-m-d H:i:s'),
            'artist' => [
                'username' => $username,
                'display_name' => '',
                'bio' => ''
            ],
            'links' => [],
            'pieces' => []
        ];
    }
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    if (!is_array($data)) {
        return [
            'version' => 1,
            'publish_portfolio' => 0,
            'last_modified' => date('Y-m-d H:i:s'),
            'artist' => [
                'username' => $username,
                'display_name' => '',
                'bio' => ''
            ],
            'links' => [],
            'pieces' => []
        ];
    }
    return $data;
}

/**
 * Validate and save portfolio.json.
 * Returns ['success' => bool, 'error' => string|null].
 */
function SavePortfolio($username, $jsonData): array {
    // Validate structure
    $validation = ValidatePortfolioJson($jsonData);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }

    // Ensure directory
    $dir = EnsurePortfolioDirectory($username);

    // Update timestamp
    $jsonData['last_modified'] = date('Y-m-d H:i:s');

    $path = $dir . '/portfolio.json';
    $written = file_put_contents($path, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    if ($written === false) {
        return ['success' => false, 'error' => 'Failed to write portfolio.json'];
    }

    return ['success' => true, 'error' => null];
}

/**
 * Upload a file to the portfolio directory.
 * Handles filename collisions by appending _1, _2 etc.
 * Returns ['success' => bool, 'filename' => string|null, 'error' => string|null].
 */
function UploadPortfolioFile($username, $file): array {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'error' => 'No file uploaded'];
    }

    $dir = EnsurePortfolioDirectory($username);
    $originalName = basename($file['name']);
    $pathinfo = pathinfo($originalName);
    $ext = strtolower($pathinfo['extension'] ?? '');
    $base = $pathinfo['filename'];

    // Validate file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm', 'pdf', 'txt'];
    if (!in_array($ext, $allowedTypes)) {
        // Try to detect by mime
        $mime = $file['type'] ?? '';
        $mimeMap = [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
            'image/webp' => 'webp', 'image/svg+xml' => 'svg',
            'video/mp4' => 'mp4', 'video/webm' => 'webm',
            'application/pdf' => 'pdf', 'text/plain' => 'txt'
        ];
        if (isset($mimeMap[$mime])) {
            $ext = $mimeMap[$mime];
        } else {
            return ['success' => false, 'filename' => null, 'error' => 'File type not supported'];
        }
    }

    // Collision handling
    $filename = $originalName;
    $counter = 1;
    while (file_exists($dir . '/' . $filename)) {
        $filename = $base . '_' . $counter . '.' . $ext;
        $counter++;
    }

    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'filename' => null, 'error' => 'Failed to save file'];
    }

    // Generate video thumbnail if applicable
    if (in_array($ext, ['mp4', 'webm'])) {
        GenerateVideoThumbnail($dest);
    }

    return ['success' => true, 'filename' => $filename, 'error' => null];
}

/**
 * Upload a profile picture — always saved as pfp.{ext}.
 */
function UploadPortfolioPfp($username, $file): array {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    $dir = EnsurePortfolioDirectory($username);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedImg = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowedImg)) {
        return ['success' => false, 'error' => 'Profile picture must be an image'];
    }

    // Remove old PFP files
    $pfpFiles = glob($dir . '/pfp.*');
    foreach ($pfpFiles as $pfp) {
        unlink($pfp);
    }

    $dest = $dir . '/pfp.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'error' => 'Failed to save profile picture'];
    }

    return ['success' => true, 'error' => null, 'ext' => $ext];
}

/**
 * Find existing PFP file — returns the filename or null.
 */
function FindPortfolioPfp($username): ?string {
    $dir = GetPortfolioPath($username);
    $files = glob($dir . '/pfp.*');
    if (!empty($files)) {
        return basename($files[0]);
    }
    return null;
}

/**
 * Delete a portfolio file from disk.
 */
function DeletePortfolioFile($username, $filename): bool {
    // Safety: prevent directory traversal
    $filename = basename($filename);
    $path = GetPortfolioPath($username) . '/' . $filename;
    if (file_exists($path) && is_file($path)) {
        // Don't delete portfolio.json
        if ($filename === 'portfolio.json') {
            return false;
        }
        return unlink($path);
    }
    return false;
}

/**
 * Generate a video thumbnail using FFmpeg.
 * Saves as filename.thumb.jpg alongside the video.
 */
function GenerateVideoThumbnail($filepath): bool {
    if (!file_exists($filepath)) {
        return false;
    }
    $thumbPath = $filepath . '.thumb.jpg';
    $escaped = escapeshellarg($filepath);
    $escapedThumb = escapeshellarg($thumbPath);
    $cmd = "ffmpeg -i {$escaped} -ss 00:00:01 -vframes 1 -y {$escapedThumb} 2>/dev/null";
    exec($cmd, $output, $returnCode);
    return $returnCode === 0 && file_exists($thumbPath);
}

/**
 * Save text content to a .txt file in the portfolio directory.
 */
function SavePortfolioTextFile($username, $filename, $content): array {
    $filename = basename($filename);
    $dir = EnsurePortfolioDirectory($username);
    $path = $dir . '/' . $filename;

    // Only allow .txt files
    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'txt') {
        return ['success' => false, 'error' => 'Only .txt files can be edited'];
    }

    $written = file_put_contents($path, $content);
    if ($written === false) {
        return ['success' => false, 'error' => 'Failed to save text file'];
    }

    return ['success' => true, 'error' => null];
}

/**
 * Validate portfolio.json structure.
 * Returns ['valid' => bool, 'error' => string|null].
 */
function ValidatePortfolioJson($json): array {
    if (!is_array($json)) {
        return ['valid' => false, 'error' => 'Root must be an object'];
    }

    // version
    if (!isset($json['version'])) {
        return ['valid' => false, 'error' => 'Missing version'];
    }

    // artist
    if (!isset($json['artist']) || !is_array($json['artist'])) {
        return ['valid' => false, 'error' => 'Missing or invalid artist'];
    }
    if (!isset($json['artist']['username'])) {
        return ['valid' => false, 'error' => 'Missing artist username'];
    }

    // links
    if (!isset($json['links']) || !is_array($json['links'])) {
        return ['valid' => false, 'error' => 'Missing or invalid links'];
    }
    foreach ($json['links'] as $i => $link) {
        if (!isset($link['label']) || !isset($link['url'])) {
            return ['valid' => false, 'error' => "Link at index {$i} missing label or url"];
        }
    }

    // pieces
    if (!isset($json['pieces']) || !is_array($json['pieces'])) {
        return ['valid' => false, 'error' => 'Missing or invalid pieces'];
    }
    foreach ($json['pieces'] as $i => $piece) {
        if (!isset($piece['id'])) {
            return ['valid' => false, 'error' => "Piece at index {$i} missing id"];
        }
        if (!isset($piece['type'])) {
            return ['valid' => false, 'error' => "Piece at index {$i} missing type"];
        }
        $validTypes = ['image', 'video', 'pdf', 'text'];
        if (!in_array($piece['type'], $validTypes)) {
            return ['valid' => false, 'error' => "Piece at index {$i} has invalid type: {$piece['type']}"];
        }
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Get a list of files in the portfolio directory (excluding portfolio.json and thumbnails).
 */
function ListPortfolioFiles($username): array {
    $dir = GetPortfolioPath($username);
    if (!is_dir($dir)) {
        return [];
    }
    $files = scandir($dir);
    $result = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if ($file === 'portfolio.json') continue;
        // Skip generated thumbnails (they'll be matched with their video)
        if (str_ends_with($file, '.thumb.jpg')) continue;
        $result[] = $file;
    }
    return $result;
}

/**
 * Check if the current session can edit a given artist's portfolio.
 * Admins are read-only (rely on IsImpersonating check in the handler).
 */
function CanEditPortfolio($targetUsername): bool {
    if (IsImpersonating()) return false;
    if (GetRole() === 'admin') return false;
    if (GetRole() === 'client') return false;
    // Artist can only edit their own
    return $_SESSION['username'] === $targetUsername;
}
