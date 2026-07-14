<?php
/**
 * portfolioLib.php — Portfolio Editor backend for Simeck Entertainment Dropbox.
 * Handles all file operations for the artist portfolio canvas.
 */
require_once __ROOT__ . '/libraries/tokenlib.php';

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
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm', 'pdf', 'txt', 'mp3', 'wav', 'ogg', 'flac', 'aac', 'wma'];
    if (!in_array($ext, $allowedTypes)) {
        // Try to detect by mime
        $mime = $file['type'] ?? '';
        $mimeMap = [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
            'image/webp' => 'webp', 'image/svg+xml' => 'svg',
            'video/mp4' => 'mp4', 'video/webm' => 'webm',
            'application/pdf' => 'pdf', 'text/plain' => 'txt',
            'audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/ogg' => 'ogg',
            'audio/flac' => 'flac', 'audio/aac' => 'aac', 'audio/x-ms-wma' => 'wma'

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
    try {
        if (in_array($ext, ['mp4', 'webm'])) {
            GenerateVideoThumbnail($dest);
        }
        // Extract embedded cover art from audio files
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'wma'])) {
            ExtractAudioCoverArt($dest);
        }
    } catch (Exception $e) {
        // Processing failed — clean up the file that was already written
        if (file_exists($dest)) {
            unlink($dest);
        }
        return ['success' => false, 'filename' => null, 'error' => 'Failed to process uploaded file: ' . $e->getMessage()];
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
        // Clean up associated cover art if deleting an audio file
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'wma'])) {
            $coverPath = $path . '.cover.jpg';
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }
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
    
    // Use elFinder's ffmpeg path if available
    $ffmpeg = defined('ELFINDER_FFMPEG_PATH') ? ELFINDER_FFMPEG_PATH : 'ffmpeg';
    
    // Check if ffmpeg is available (same check elFinder uses)
    $testCmd = $ffmpeg . ' -version 2>&1';
    exec($testCmd, $testOutput, $testReturn);
    if ($testReturn !== 0) {
        return false; // ffmpeg not available
    }
    
    // Use elFinder's default capture time (6 seconds)
    $escaped = escapeshellarg($filepath);
    $escapedThumb = escapeshellarg($thumbPath);
    $cmd = "{$ffmpeg} -i {$escaped} -ss 00:00:06 -vframes 1 -y {$escapedThumb} 2>&1";
    exec($cmd, $output, $returnCode);
    
    if ($returnCode !== 0 || !file_exists($thumbPath)) {
        // Fallback: try at 1 second
        $cmd = "{$ffmpeg} -i {$escaped} -ss 00:00:01 -vframes 1 -y {$escapedThumb} 2>&1";
        exec($cmd, $output, $returnCode);
    }
    
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
        $validTypes = ['image', 'video', 'pdf', 'text', 'audio'];
        if (!in_array($piece['type'], $validTypes)) {
            return ['valid' => false, 'error' => "Piece at index {$i} has invalid type: {$piece['type']}"];
        }
    }

    return ['valid' => true, 'error' => null];
}
// ════════════════════════════════════════════════════════
// AUDIO COVER ART FUNCTIONS
// ════════════════════════════════════════════════════════

/**
 * Extract cover art from an audio file using FFmpeg.
 * Saves as filename.cover.jpg alongside the audio file.
 * Returns the cover filename on success, null on failure/missing.
 */
function ExtractAudioCoverArt($filepath): ?string {
    if (!file_exists($filepath)) {
        return null;
    }
    
    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'wma'])) {
        return null;
    }
    
    $coverPath = $filepath . '.cover.jpg';
    
    // Use elFinder's ffmpeg path if available
    $ffmpeg = defined('ELFINDER_FFMPEG_PATH') ? ELFINDER_FFMPEG_PATH : 'ffmpeg';
    
    // Check if ffmpeg is available
    $testCmd = $ffmpeg . ' -version 2>&1';
    exec($testCmd, $testOutput, $testReturn);
    if ($testReturn !== 0) {
        return null;
    }
    
    // Extract cover art (single frame, first attached pic)
    $escaped = escapeshellarg($filepath);
    $escapedCover = escapeshellarg($coverPath);
    $cmd = "{$ffmpeg} -i {$escaped} -an -vcodec copy -y {$escapedCover} 2>&1";
    exec($cmd, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($coverPath) && filesize($coverPath) > 0) {
        return basename($coverPath);
    }
    
    // Clean up empty file if ffmpeg wrote a 0-byte file
    if (file_exists($coverPath) && filesize($coverPath) === 0) {
        unlink($coverPath);
    }
    
    return null;
}

/**
 * Embed an image as cover art into an audio file using FFmpeg.
 * Returns ['success' => bool, 'error' => string|null, 'cover' => string|null].
 */
function EmbedAudioCoverArtIntoFile($audioFilepath, $imageFilepath): array {
    if (!file_exists($audioFilepath)) {
        return ['success' => false, 'error' => 'Audio file not found', 'cover' => null];
    }
    if (!file_exists($imageFilepath)) {
        return ['success' => false, 'error' => 'Image file not found', 'cover' => null];
    }
    
    $ffmpeg = defined('ELFINDER_FFMPEG_PATH') ? ELFINDER_FFMPEG_PATH : 'ffmpeg';
    
    // Test ffmpeg availability
    exec($ffmpeg . ' -version 2>&1', $testOutput, $testReturn);
    if ($testReturn !== 0) {
        return ['success' => false, 'error' => 'FFmpeg not available', 'cover' => null];
    }
    
    $dir = dirname($audioFilepath);
    $tempFile = $dir . '/_temp_cover_' . basename($audioFilepath);
    
    $escapedAudio = escapeshellarg($audioFilepath);
    $escapedImage = escapeshellarg($imageFilepath);
    $escapedTemp = escapeshellarg($tempFile);
    
    // Embed cover: keep audio stream, add image as attached pic, output to temp
    $cmd = "{$ffmpeg} -i {$escapedAudio} -i {$escapedImage} -map 0:0 -map 1:0 -c copy -id3v2_version 3 -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (front)\" -y {$escapedTemp} 2>&1";
    exec($cmd, $output, $returnCode);
    
    if ($returnCode !== 0 || !file_exists($tempFile)) {
        if (file_exists($tempFile)) unlink($tempFile);
        return ['success' => false, 'error' => 'Failed to embed cover art', 'cover' => null];
    }
    
    // Replace original with new file
    if (!rename($tempFile, $audioFilepath)) {
        unlink($tempFile);
        return ['success' => false, 'error' => 'Failed to replace audio file', 'cover' => null];
    }
    
    // Re-extract the cover for display alongside
    $coverBasename = ExtractAudioCoverArt($audioFilepath);
    
    return ['success' => true, 'error' => null, 'cover' => $coverBasename];
}

/**
 * Strip embedded cover art from an audio file and delete the extracted .cover.jpg.
 * Returns ['success' => bool, 'error' => string|null].
 */
function StripAudioCoverArt($audioFilepath): array {
    if (!file_exists($audioFilepath)) {
        return ['success' => false, 'error' => 'Audio file not found'];
    }
    
    // Delete the extracted cover thumbnail if it exists
    $coverPath = $audioFilepath . '.cover.jpg';
    if (file_exists($coverPath)) {
        unlink($coverPath);
    }
    
    $ffmpeg = defined('ELFINDER_FFMPEG_PATH') ? ELFINDER_FFMPEG_PATH : 'ffmpeg';
    
    // Test ffmpeg availability
    exec($ffmpeg . ' -version 2>&1', $testOutput, $testReturn);
    if ($testReturn !== 0) {
        return ['success' => true, 'error' => null]; // Already deleted the cover file
    }
    
    $dir = dirname($audioFilepath);
    $tempFile = $dir . '/_temp_strip_' . basename($audioFilepath);
    
    $escapedAudio = escapeshellarg($audioFilepath);
    $escapedTemp = escapeshellarg($tempFile);
    
    // Strip all metadata including cover art
    $cmd = "{$ffmpeg} -i {$escapedAudio} -map 0:0 -c copy -write_id3v2 0 -y {$escapedTemp} 2>&1";
    exec($cmd, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($tempFile)) {
        rename($tempFile, $audioFilepath);
    } elseif (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    return ['success' => true, 'error' => null];
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
        // Skip extracted cover art (they'll be matched with their audio)
        if (str_ends_with($file, '.cover.jpg')) continue;

        // Skip generated thumbnails (they'll be matched with their video)
        if (str_ends_with($file, '.thumb.jpg')) continue;
        $result[] = $file;
    }
    return $result;
}
/**
 * Delete files in the portfolio directory that are not referenced by portfolio.json.
 * This runs on editor load to clean up orphaned files from failed uploads or forgotten saves.
 */
function CleanupOrphanedPortfolioFiles($username): void {
    $dir = GetPortfolioPath($username);
    if (!is_dir($dir)) {
        return;
    }

    // Get filenames referenced in portfolio.json
    $portfolio = LoadPortfolio($username);
    $referencedFiles = [];
    foreach ($portfolio['pieces'] as $piece) {
        if (!empty($piece['filename'])) {
            $referencedFiles[] = $piece['filename'];
        }
    }

    // Scan the directory
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if ($file === 'portfolio.json') continue;             // never delete the JSON itself
        if (str_starts_with($file, 'pfp.')) continue;         // never delete profile pictures

        $fullPath = $dir . '/' . $file;
        if (!is_file($fullPath)) continue;

        $isOrphan = false;

        // Handle generated sidecar files (thumbnails, cover art)
        if (str_ends_with($file, '.thumb.jpg')) {
            $baseFile = substr($file, 0, -10); // remove '.thumb.jpg'
            if (!in_array($baseFile, $referencedFiles, true)) {
                $isOrphan = true;
            }
        } elseif (str_ends_with($file, '.cover.jpg')) {
            $baseFile = substr($file, 0, -10); // remove '.cover.jpg'
            if (!in_array($baseFile, $referencedFiles, true)) {
                $isOrphan = true;
            }
        } else {
            if (!in_array($file, $referencedFiles, true)) {
                $isOrphan = true;
            }
        }

        if ($isOrphan) {
            unlink($fullPath);
        }
    }
}
/**
 * Generate a V2 download token for a portfolio file.
 * Used to serve portfolio files through download.php for consistent access control.
 *
 * @param string $username  Portfolio owner's username
 * @param string $filename  The portfolio file filename
 * @param string $mode      Token mode: internal, clientPreview, thumbnail, deliverable
 * @return string|false     Base64-encoded token, or false on failure
 */
function GeneratePortfolioFileToken($username, $filename, $mode = 'internal') {
    // Ensure DB_ENCRYPTION_KEY is loaded (not loaded in normal page lifecycle)
    if (!defined('DB_ENCRYPTION_KEY')) {
        if (file_exists('/var/www/dbconfig.php')) {
            require_once '/var/www/dbconfig.php';
        } elseif (file_exists(__ROOT__ . '/dbconfig.php')) {
            require_once __ROOT__ . '/dbconfig.php';
        } elseif (file_exists(__ROOT__ . '/../dbconfig.php')) {
            require_once __ROOT__ . '/../dbconfig.php';
        }
    }
    
    $filepath = GetPortfolioPath($username) . '/' . $filename;
    if (!file_exists($filepath)) {
        return false;
    }
    return GenerateElfinderDownloadToken($filepath, $mode);
}

