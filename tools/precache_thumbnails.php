<?php
/**
 * elFinder Thumbnail Pre-Cache Script
 *
 * Walks all elFinder volume directories and pre-generates 48×48 PNG thumbnails
 * for image files so users don't lag when opening folders for the first time.
 *
 * Because thumbnail filenames depend on elFinder's internal hash (volume ID prefix
 * + path encoding), we can't replicate the naming from outside. Instead, this
 * script instantiates real elFinder LocalFileSystem volumes to generate the
 * thumbnails using elFinder's own code — ensuring the names match exactly.
 *
 * Usage:  php libraries/precache_thumbnails.php
 */

define('__ROOT__', realpath(__DIR__ . '/..'));
require_once __ROOT__ . '/libraries/elfinder/php/autoload.php';

// ── Define elFinder constants normally set by elFinder::start() ──
!defined('ELFINDER_TAR_PATH')     && define('ELFINDER_TAR_PATH', 'tar');
!defined('ELFINDER_GZIP_PATH')    && define('ELFINDER_GZIP_PATH', 'gzip');
!defined('ELFINDER_BZIP2_PATH')   && define('ELFINDER_BZIP2_PATH', 'bzip2');
!defined('ELFINDER_XZ_PATH')      && define('ELFINDER_XZ_PATH', 'xz');
!defined('ELFINDER_ZIP_PATH')     && define('ELFINDER_ZIP_PATH', 'zip');
!defined('ELFINDER_UNZIP_PATH')   && define('ELFINDER_UNZIP_PATH', 'unzip');
!defined('ELFINDER_RAR_PATH')     && define('ELFINDER_RAR_PATH', 'rar');
!defined('ELFINDER_UNRAR_PATH')   && define('ELFINDER_UNRAR_PATH', 'unrar');
!defined('ELFINDER_7Z_PATH')      && define('ELFINDER_7Z_PATH', (substr(PHP_OS, 0, 3) === 'WIN') ? '7z' : '7za');
!defined('ELFINDER_CONVERT_PATH') && define('ELFINDER_CONVERT_PATH', 'convert');
!defined('ELFINDER_IDENTIFY_PATH')&& define('ELFINDER_IDENTIFY_PATH', 'identify');
!defined('ELFINDER_EXIFTRAN_PATH')&& define('ELFINDER_EXIFTRAN_PATH', 'exiftran');
!defined('ELFINDER_JPEGTRAN_PATH')&& define('ELFINDER_JPEGTRAN_PATH', 'jpegtran');
!defined('ELFINDER_FFMPEG_PATH')  && define('ELFINDER_FFMPEG_PATH', 'ffmpeg');
!defined('ELFINDER_IMAGEMAGICK_PS')&& define('ELFINDER_IMAGEMAGICK_PS', false);

set_time_limit(0);
ini_set('memory_limit', '512M');

// ── Configuration ────────────────────────────────────────────────────

$volumes = [
    [
        'alias'  => 'Projects',
        'path'   => __ROOT__ . '/files/Projects',
        'tmbDir' => '.tmb',
    ],
    [
        'alias'  => 'Dropboxes',
        'path'   => __ROOT__ . '/files/Dropboxes',
        'tmbDir' => '.tmb',
    ],
    [
        'alias'  => 'Resources',
        'path'   => __ROOT__ . '/files/Resources',
        'tmbDir' => '.tmb',
    ],
    [
        'alias'  => 'Corporate',
        'path'   => __ROOT__ . '/files/Corporate',
        'tmbDir' => '.tmb',
    ],
];

// Only generate thumbnails for these MIME types (matches elFinder's canCreateTmb)
$imageMimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/x-ms-bmp',
    'image/webp',
];

// ── Stats ────────────────────────────────────────────────────────────
$stats = [
    'total_images' => 0,
    'generated' => 0,
    'errors' => 0,
    'error_files' => [],
];

// ── Main ─────────────────────────────────────────────────────────────
echo "=== elFinder Thumbnail Pre-Cache ===\n\n";

// We use a minimal session adapter so elFinder volume drivers don't crash
// (they require a session object, even though we don't need persistence here)
if (!class_exists('DummySession', false)) {
    class DummySession implements elFinderSessionInterface {
        public function start() { return $this; }
        public function close() { return $this; }
        public function get($key, $default = null) { return []; }
        public function set($key, $value) { return $this; }
        public function remove($key) { return $this; }
        public function clear() { return $this; }
    }
}
// Small adapter to expose elFinder's protected stat() method for pre-caching
class PrecacheVolume extends elFinderVolumeLocalFileSystem {
    public function publicStat($path) {
        return $this->stat($path);
    }
}

foreach ($volumes as $volCfg) {
    $rootPath = $volCfg['path'];
    $alias    = $volCfg['alias'];

    if (!is_dir($rootPath)) {
        echo "[SKIP] {$alias} — directory does not exist: {$rootPath}\n";
        continue;
    }

    echo "\n── Scanning: {$alias} ({$rootPath}) ──\n";

    // Ensure .tmb directory exists at root level
    $tmbRootPath = $rootPath . DIRECTORY_SEPARATOR . $volCfg['tmbDir'];
    if (!is_dir($tmbRootPath)) {
        @mkdir($tmbRootPath, 0777, true);
        echo "  Created root .tmb directory\n";
    }

    // Recursively find all image files
    $images = findImageFiles($rootPath, $imageMimes);

    if (empty($images)) {
        echo "  No image files found.\n";
        continue;
    }

    echo "  Found " . count($images) . " image files.\n";

    // Process images directory by directory so we can mount a volume
    // for each directory tree (elFinder volumes are per-root, not per-file).
    // Group images by their top-level subdirectory under the volume root.
    $dirGroups = groupByDirectory($images, $rootPath);

    $dirCount = 0;
    foreach ($dirGroups as $relativeDir => $files) {
        $dirCount++;
        $volumePath = $rootPath . DIRECTORY_SEPARATOR . $relativeDir;
        $tmbPath    = $volumePath . DIRECTORY_SEPARATOR . $volCfg['tmbDir'];

        // Ensure the .tmb directory exists
        if (!is_dir($tmbPath)) {
            @mkdir($tmbPath, 0777, true);
            // Also create a .tmb at the volume root if needed
            $parentTmb = dirname($volumePath) . DIRECTORY_SEPARATOR . $volCfg['tmbDir'];
            if (!is_dir($parentTmb)) {
                @mkdir($parentTmb, 0777, true);
            }
        }

        // Mount a LocalFileSystem volume on this exact directory
        $volume = new PrecacheVolume();
        $volume->setSession(new DummySession());

        $opts = [
            'driver'     => 'LocalFileSystem',
            'path'       => $volumePath,
            'tmbPath'    => $tmbPath,
            'tmbURL'     => '/',
            'tmbSize'    => 48,
            'tmbCrop'    => true,
            'tmbBgColor' => 'transparent',
            'imgLib'     => 'auto',
            'mimeDetect' => 'internal',
        ];

        if (!$volume->mount($opts)) {
            echo "  [WARN] Could not mount volume on: {$relativeDir}\n";
            continue;
        }

        $subStats = processImages($volume, $files, $imageMimes);
        $stats['total_images']    += $subStats['total'];
        $stats['generated']       += $subStats['generated'];
        $stats['errors']          += $subStats['errors'];
        $stats['error_files']      = array_merge($stats['error_files'], $subStats['error_files']);


        unset($volume);

        if ($dirCount % 10 === 0) {
            echo "  Processed {$dirCount}/" . count($dirGroups) . " directories...\n";
        }
    }

    echo "  Done with {$alias}. Processed " . count($dirGroups) . " directories.\n";
}

// ── Summary ──────────────────────────────────────────────────────────
echo "\n=== Summary ===\n";
echo "  Total images found:    {$stats['total_images']}\n";
echo "  Thumbnails generated:  {$stats['generated']}\n";
echo "  Errors:                {$stats['errors']}\n";

if (!empty($stats['error_files'])) {
    echo "\n  Files with errors:\n";
    foreach ($stats['error_files'] as $ef) {
        echo "    - {$ef}\n";
    }
}

echo "\nDone!\n";

// ── Functions ────────────────────────────────────────────────────────

/**
 * Recursively find all files with image MIME types under a directory.
 */
function findImageFiles(string $rootPath, array $allowedMimes): array
{
    $results = [];

    // Filter out .tmb directories before recursion
    $filter = new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        function ($current, $key, $iterator) {
            if ($current->isDir() && $current->getBasename() === '.tmb') {
                return false; // skip this directory entirely (no recursion)
            }
            return true;
        }
    );

    $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'];

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDir()) continue;

        $path = $fileInfo->getRealPath();
        if (!$path) continue;

        $ext = strtolower($fileInfo->getExtension());
        if (!in_array($ext, $imageExts)) continue;

        $mime = $finfo->file($path);
        if (in_array($mime, $allowedMimes)) {
            $results[] = $path;
        }
    }
    return $results;
}


/**
 * Group file paths by their relative directory under the volume root.
 * Returns [ 'relative/dir' => [ '/full/path/to/file1', ... ] ]
 */
function groupByDirectory(array $files, string $rootPath): array
{
    // Normalize root path
    $rootPath = rtrim(str_replace('\\', '/', $rootPath), '/') . '/';
    $groups   = [];

    foreach ($files as $path) {
        $normPath  = str_replace('\\', '/', $path);
        $relative  = substr($normPath, strlen($rootPath));
        $dirName   = dirname($relative);

        // Ensure we handle files at root level too
        if ($dirName === '.') {
            $dirName = '';
        }

        if (!isset($groups[$dirName])) {
            $groups[$dirName] = [];
        }
        $groups[$dirName][] = $path;
    }

    return $groups;
}

/**
 * Process a list of image files through an already-mounted elFinder volume.
 */
function processImages(PrecacheVolume $volume, array $filePaths, array $allowedMimes): array
{
    $result = [
        'total'       => count($filePaths),
        'cached'      => 0,
        'generated'   => 0,
        'errors'      => 0,
        'error_files' => [],
    ];

    foreach ($filePaths as $filePath) {
        $stat = @$volume->publicStat($filePath);
        if (!$stat || $stat['mime'] === 'directory') {
            continue;
        }

        if (!in_array($stat['mime'], $allowedMimes)) continue;

        // tmb() already handles both checking AND creating.
        // Returns filename string if cached/created, '1' if failed, false if N/A.
        $tmbResult = @$volume->tmb($stat['hash']);

        if ($tmbResult && $tmbResult !== '1') {
            // If it was already cached, gettmb() would've returned the name
            // without generating. We can't distinguish cached vs generated from
            // outside, but the end result is the same: thumbnail exists now.
            $result['generated']++;
        } else {
            $result['errors']++;
            $result['error_files'][] = $filePath;
        }
    }

    return $result;
}
