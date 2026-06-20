/**
 * CopyWatermarkedShortLink.js
 *
 * Creates a shortlink (14-day expiry) that serves a watermarked, 800px-max
 * preview of the selected file. Copies the URL to clipboard.
 *
 * @commandID CopyWatermarkedShortLink
 * @nicename Copy Watermarked Shortlink
 * @role client
 * @availableToHigherRoles true
 * @loc files
 * @order 8
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.CopyWatermarkedShortLink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Watermarked Shortlink'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        if (files.length === 0) { fm.notify({ type: 'error', msg: 'No files selected.' }); return dfrd.reject(); }
        if (files.length > 1) { fm.notify({ type: 'error', msg: 'Please select only one file.' }); return dfrd.reject(); }
        fm.notify({ type: 'info', msg: 'Generating watermarked shortlink...', cnt: 1, progress: 0 });
        $.post('/libraries/elfinderLibs/endpoints/generateLinkEndpoint.php', { hash: files[0].hash, type: 'shortlink', mode: 'clientPreview' }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });
            if (response.success && response.url) copyToClipboard(response.url, 'Watermarked shortlink copied (expires in 14 days)!', fm);
            else fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
        }, 'json').fail(function() { fm.notify({ type: 'info', cnt: -1 }); fm.notify({ type: 'error', msg: 'Server request failed.' }); }).always(function() { dfrd.resolve(); });
        return dfrd.promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
