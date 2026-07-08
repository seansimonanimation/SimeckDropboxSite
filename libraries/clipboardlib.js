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
     * Copy text to clipboard with fallback to prompt()
     * @param {string} text - The text to copy
     * @param {string} [successMsg] - Optional success message for elFinder notify
     * @param {object} [fm] - elFinder instance (optional, omitting skips notify)
     *
     * @example
     * SimeckClipboardCommands.CopyToClipboard('https://example.com', 'Link copied!', fm);
     */
    this.CopyToClipboard = function(text, successMsg, fm) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (fm) fm.notify({ type: 'info', msg: successMsg || 'Copied to clipboard!' });
            }).catch(function() {
                prompt('Copy this text (Ctrl+C, then Enter):', text);
            });
        } else {
            prompt('Copy this text (Ctrl+C, then Enter):', text);
        }
    };

})();
