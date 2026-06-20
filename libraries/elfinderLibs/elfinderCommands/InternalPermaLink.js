/**
 * InternalPermaLink.js
 *
 * Creates a permanent download link (no expiry) for the selected file with
 * full resolution, no watermark. Copies the URL to clipboard.
 *
 * @commandID InternalPermalink
 * @nicename Copy Internal Permalink
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 6
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.InternalPermalink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Internal Permalink'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        if (files.length === 0) { fm.notify({ type: 'error', msg: 'No files selected.' }); return dfrd.reject(); }
        if (files.length > 1) { fm.notify({ type: 'error', msg: 'Please select only one file.' }); return dfrd.reject(); }
        fm.notify({ type: 'info', msg: 'Generating permalink...', cnt: 1, progress: 0 });
        $.post('/libraries/elfinderLibs/endpoints/generateLinkEndpoint.php', { hash: files[0].hash, type: 'permalink', mode: 'internal' }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });
            if (response.success && response.url) copyToClipboard(response.url, 'Permalink copied to clipboard!', fm);
            else fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
        }, 'json').fail(function() { fm.notify({ type: 'info', cnt: -1 }); fm.notify({ type: 'error', msg: 'Server request failed.' }); }).always(function() { dfrd.resolve(); });
        return dfrd.promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
