/**
 * @name SimeckClipboardCommands
 * @description Clipboard utilities for elFinder, using DropboxLink for link generation
 */
var SimeckClipboardCommands = window.SimeckClipboardCommands || {};

(function() {
    'use strict';

    /**
     * Copy a download link to clipboard
     * @param {string} hash - elFinder file hash
     * @param {string} [mode] - Link mode
     */
    SimeckClipboardCommands.CopyDownloadLink = function(hash, mode) {
        var link = DropboxLink.MakeLink(hash, mode);
        SimeckClipboardCommands.CopyToClipboard(link);
    };

    /**
     * Copy text to clipboard using modern API with fallback
     * @param {string} text - Text to copy
     * @param {function} [callback] - Optional callback on success
     */
    SimeckClipboardCommands.CopyToClipboard = function(text, callback) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (callback) callback(true);
            }).catch(function() {
                SimeckClipboardCommands._fallbackCopy(text, callback);
            });
        } else {
            SimeckClipboardCommands._fallbackCopy(text, callback);
        }
    };

    /**
     * Fallback copy using textarea selection + execCommand
     * @private
     */
    SimeckClipboardCommands._fallbackCopy = function(text, callback) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            if (callback) callback(true);
        } catch (e) {
            if (callback) callback(false);
        }
        document.body.removeChild(textarea);
    };

    /**
     * Copy text to clipboard with optional elFinder notify
     * @param {string} text - The text to copy
     * @param {string} [successMsg] - Optional success message for elFinder notify
     * @param {object} [fm] - elFinder instance (optional)
     */
    SimeckClipboardCommands.CopyToClipboard = function(text, successMsg, fm) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (fm) {
                    fm.notify({ type: 'info', msg: successMsg || 'Copied to clipboard!' });
                }
            }).catch(function() {
                prompt('Copy this text (Ctrl+C, then Enter):', text);
            });
        } else {
            prompt('Copy this text (Ctrl+C, then Enter):', text);
        }
    };

    // ─── Private helper: AJAX link gen + clipboard ────────────────────────────

    function _copyLinkViaEndpoint(fm, hash, type, mode, successMsg) {
        Helpers.post('/libraries/elfinderLibs/endpoints/generateLinkEndpoint.php', {
            hash: hash,
            type: type,
            mode: mode
        }).then(function(response) {
            if (response.success && response.url) {
                SimeckClipboardCommands.CopyToClipboard(response.url, successMsg, fm);
            } else {
                if (fm) {
                    fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
                }
            }
        }).catch(function() {
            if (fm) {
                fm.notify({ type: 'error', msg: 'Network error generating link.' });
            }
        });
    }

    // ─── Permalink commands ───────────────────────────────────────────────────

    SimeckClipboardCommands.CopyInternalPermalink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'permalink', 'internal', 'Internal link copied!');
    };

    SimeckClipboardCommands.CopyInternalShortlink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'shortlink', 'internal', 'Internal shortlink copied!');
    };

    SimeckClipboardCommands.CopyWatermarkedPermalink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'permalink', 'clientPreview', 'Watermarked link copied!');
    };

    SimeckClipboardCommands.CopyWatermarkedShortlink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'shortlink', 'clientPreview', 'Watermarked shortlink copied!');
    };

    SimeckClipboardCommands.CopyThumbnailPermalink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'permalink', 'thumbnail', 'Thumbnail link copied!');
    };

    SimeckClipboardCommands.CopyThumbnailShortlink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'shortlink', 'thumbnail', 'Thumbnail shortlink copied!');
    };

    SimeckClipboardCommands.CopyDeliverablePermalink = function(fm, hash) {
        _copyLinkViaEndpoint(fm, hash, 'permalink', 'deliverable', 'Deliverable link copied!');
    };

    // ─── Folder link ──────────────────────────────────────────────────────────

    SimeckClipboardCommands.CopyFolderLink = function(fm, phash) {
        var adjustedHash = phash;
        if (phash.startsWith('s1_')) {
            var path = decodeElfinderHash(phash);
            var session = window.simeckSession;
            var userName = session.lastname + ', ' + session.firstname;
            var reEncoded = encodeElfinderPath(userName + '/' + path);
            adjustedHash = 's2_' + reEncoded;
        }
        var baseUrl = window.location.protocol + '//' + window.location.host;
        var link = baseUrl + '/viewfolder.php?folderid=' + encodeURIComponent(adjustedHash);
        SimeckClipboardCommands.CopyToClipboard(link, 'Folder link copied!', fm);
    };

})();
