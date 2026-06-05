<?php
/**
 * SimeckVolumeDriver - Custom elFinder volume driver with path-based thumbnail naming
 *
 * Overrides tmbname() to generate deterministic thumbnail filenames based on
 * the real filesystem path, rather than elFinder's volume-ID-dependent hash.
 * This ensures thumbnails persist correctly across page navigations regardless
 * of volume mount order.
 */

class elFinderVolumeSimeckVolume extends elFinderVolumeLocalFileSystem
{
    /**
     * Driver id prefix (used as part of the volume ID).
     * Distinguishes this driver from the base LocalFileSystem ('l').
     *
     * @var string
     */
    protected $driverId = 's';

    /**
     * Generate a thumbnail filename based on the file's real filesystem path.
     *
     * The default tmbname() uses elFinder's internal hash, which incorporates
     * the volume ID (e.g., l1_, l2_). Since volume IDs can shift depending on
     * mount order, thumbnail names become unpredictable across page loads.
     *
     * This override uses md5() of the decoded real path, which is stable
     * regardless of which volume mounts first. The file's modification timestamp
     * is appended so thumbnails are regenerated when the source file changes.
     *
     * @param  array  $stat  File stat array from elFinder (requires 'hash' key)
     * @return string        Thumbnail filename (e.g., "a1b2c3d4e5f6...1234567890.png")
     */
    protected function tmbname($stat)
    {
        // Decode elFinder's hash back to the real filesystem path
        $path = $this->decode($stat['hash']);

        // Deterministic hash of the real path + timestamp for cache invalidation
        return md5($path) . (isset($stat['ts']) ? $stat['ts'] : '0') . '.png';
    }
}
