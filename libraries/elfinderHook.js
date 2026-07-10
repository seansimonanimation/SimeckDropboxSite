/**
 * @name ElfinderHook
 * @description Data bridge between elFinder and the portal
 * @usage Provides a clean API for non-elfinder code to interact with elFinder instances
 */
var ElfinderHook = window.ElfinderHook || {};

(function() {
    'use strict';

    /**
     * Get the active elFinder instance
     * @returns {object|null} The elFinder instance, or null if not initialized
     */
    ElfinderHook.getInstance = function() {
        if (typeof jQuery !== 'undefined' && jQuery().elfinder) {
            var instances = jQuery('.elfinder').elfinder('instance');
            return instances || null;
        }
        return null;
    };

    /**
     * Get the currently selected file(s) in elFinder
     * @returns {Array} Array of selected file objects, or empty array
     *
     * @example
     * var files = ElfinderHook.getSelected();
     * if (files.length > 0) {
     *   console.log(files[0].name, files[0].path);
     * }
     */
    ElfinderHook.getSelected = function() {
        var fm = ElfinderHook.getInstance();
        if (!fm) return [];
        return fm.selectedFiles() || [];
    };

    /**
     * Get the first selected file (convenience)
     * @returns {object|null} File object or null
     */
    ElfinderHook.getFirstSelected = function() {
        var files = ElfinderHook.getSelected();
        return files.length > 0 ? files[0] : null;
    };

    /**
     * Get the current working directory in elFinder
     * @returns {object|null} Current directory file object, or null
     */
    ElfinderHook.getCurrentDir = function() {
        var fm = ElfinderHook.getInstance();
        if (!fm) return null;
        return fm.cwd() || null;
    };

    /**
     * Get the current directory path
     * @returns {string|null} Path string, or null
     */
    ElfinderHook.getCurrentPath = function() {
        var cwd = ElfinderHook.getCurrentDir();
        return cwd ? cwd.path : null;
    };

    /**
     * Open a specific directory in elFinder by hash
     * @param {string} hash - elFinder volume hash
     */
    ElfinderHook.openDir = function(hash) {
        var fm = ElfinderHook.getInstance();
        if (fm) {
            fm.exec('open', hash);
        }
    };

    /**
     * Refresh the current elFinder view
     */
    ElfinderHook.refresh = function() {
        var fm = ElfinderHook.getInstance();
        if (fm) {
            fm.reload();
        }
    };

    /**
     * Select a file by its hash
     * @param {string} hash - elFinder file hash
     */
    ElfinderHook.selectFile = function(hash) {
        var fm = ElfinderHook.getInstance();
        if (fm) {
            fm.selectFiles({ selected: [hash] });
        }
    };

    /**
     * Get file info by hash
     * @param {string} hash - elFinder file hash
     * @returns {object|null} File object or null
     */
    ElfinderHook.getFileInfo = function(hash) {
        var fm = ElfinderHook.getInstance();
        if (!fm) return null;
        return fm.file(hash) || null;
    };

    /**
     * Execute an elFinder command
     * @param {string} command - Command name (e.g. 'copy', 'mkdir', 'upload')
     * @param {*} [args] - Command arguments
     * @returns {Promise|null}
     */
    ElfinderHook.exec = function(command, args) {
        var fm = ElfinderHook.getInstance();
        if (fm) {
            return fm.exec(command, args);
        }
        return null;
    };

})();
