<?php
/**
 * SimeckVolumeDriver - Custom elFinder volume driver
 *
 * Features:
 *   - Deterministic thumbnail naming (tmbname) based on filesystem path
 *   - Client image previews routed through download.php with watermarking (url)
 */

// Load token generation for the url() override if not already loaded
if (!function_exists('GenerateElfinderDownloadToken')) {
    require_once __DIR__ . '/../tokenlib.php';
}

class elFinderVolumeSimeckVolume extends elFinderVolumeLocalFileSystem
{
    /**
     * Driver id prefix.
     * @var string
     */
    protected $driverId = 's';

    /**
     * Generate a deterministic thumbnail filename based on the file's
     * real filesystem path (not elFinder's volume-ID-dependent hash).
     *
     * @param  array  $stat  File stat array from elFinder
     * @return string        Thumbnail filename
     */
    protected function tmbname($stat)
    {
        $path = $this->decode($stat['hash']);
        return md5($path) . (isset($stat['ts']) ? $stat['ts'] : '0') . '.png';
    }

    /**
     * Generate the file URL that elFinder returns to the frontend.
     *
     * When clientMode is enabled and the file is an image, returns a V2
     * download.php URL with mode='clientPreview' (watermarked, 800px max).
     *
     * @param  string  $path  Absolute filesystem path
     * @return string         URL for the file
     */
    public function url($path)
    {
        if ($this->getOption('clientMode')) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];
            if (in_array($ext, $imageExts)) {
                $token = GenerateElfinderDownloadToken($path, 'clientPreview');
                if ($token !== false) {
                    return '/download.php?download=' . urlencode($token);
                }
            }
        }
        return parent::url($path);
    }
}
