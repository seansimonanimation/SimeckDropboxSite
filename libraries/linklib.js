/**
 * @name DropboxLink
 * @description Download link generation for Simeck Entertainment Dropbox
 * @usage DropboxLink.MakeLink(hash, mode, type)
 */
var DropboxLink = window.DropboxLink || {};

(function() {
    'use strict';

    /**
     * Generate a download link for an elFinder file
     * @param {string} hash - elFinder file hash
     * @param {string} [mode] - 'download' (default) or 'preview'
     * @param {string} [type] - Optional type hint
     * @returns {string} The download URL
     *
     * @example
     * var link = DropboxLink.MakeLink('s1_L3Jvb3QvZmlsZS50eHQ');
     * // => '/download.php?token=...'
     */
    DropboxLink.MakeLink = function(hash, mode, type) {
        // Build the download URL using the elFinder hash
        // This generates a token-based download link via download.php
        var url = '/download.php?hash=' + encodeURIComponent(hash);
        if (mode) url += '&mode=' + encodeURIComponent(mode);
        if (type) url += '&type=' + encodeURIComponent(type);
        return url;
    };

    /**
     * Generate a direct download link for a known file path
     * @param {string} filepath - Root-relative file path
     * @returns {string} The download URL
     */
    DropboxLink.MakeDirectLink = function(filepath) {
        return '/download.php?path=' + encodeURIComponent(filepath);
    };

    /**
     * Generate a token-based download link (HMAC-signed)
     * @param {string} identifier - Username or file identifier
     * @param {number} docId - Document upload ID
     * @returns {string} The signed download URL
     */
    DropboxLink.MakeDocumentLink = function(identifier, docId) {
        return '/download.php?user=' + encodeURIComponent(identifier) +
               '&doc=' + encodeURIComponent(docId);
    };

})();
