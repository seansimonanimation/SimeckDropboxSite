/**
 * CopyWatermarkedPermalink.js
 *
 * Creates a permanent watermarked download link for the selected file
 * and copies it to the clipboard.
 *
 * @commandID CopyWatermarkedPermalink
 * @nicename Copy Permalinks///Watermarked
 * @role admin
 * @loc files
 * @order 8
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.CopyWatermarkedPermalink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Watermarked Permalink'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        if (files.length === 0) {
            fm.notify({ type: 'error', msg: 'No files selected.' });
            return dfrd.reject();
        }
        if (files.length > 1) {
            fm.notify({ type: 'error', msg: 'Please select only one file.' });
            return dfrd.reject();
        }
        fm.notify({ type: 'info', msg: 'Generating watermarked permalink...', cnt: 1, progress: 0 });
        $.post('/libraries/elfinderLibs/endpoints/generateLinkEndpoint.php', {
            hash: files[0].hash,
            type: 'permalink',
            mode: 'clientPreview'
        }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });
            if (response.success && response.url) {
                copyToClipboard(response.url, 'Watermarked permalink copied to clipboard!', fm);
            } else {
                fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
            }
        }, 'json').fail(function() {
            fm.notify({ type: 'info', cnt: -1 });
            fm.notify({ type: 'error', msg: 'Server request failed.' });
        }).always(function() {
            dfrd.resolve();
        });
        return dfrd.promise();
    };
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 0 : -1;
    };
};