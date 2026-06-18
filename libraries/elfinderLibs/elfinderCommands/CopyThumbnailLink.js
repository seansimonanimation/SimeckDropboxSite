/**
 * CopyThumbnailLink.js
 * 
 * Generates a download link with the &thumb=1 parameter appended,
 * then copies it to the clipboard.
 *
 * @commandID copyThumbnailLink
 * @nicename Copy Thumbnail Link
 * @role admin
 * @loc files
 * @order 5
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.copyThumbnailLink = function() {
    this.contextmenu = true;

    this.init = function() {
        this.title = 'Copy Thumbnail Link';
    };

    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        var dfrd = $.Deferred();

        if (files.length === 0) {
            fm.notify({ type: 'error', msg: 'No files selected.' });
            return dfrd.reject();
        }

        // Collect all file hashes
        var fileHashes = [];
        $.each(files, function(i, f) {
            fileHashes.push(f.hash);
        });

        // Show notification
        fm.notify({
            type: 'info',
            msg: 'Generating ' + fileHashes.length + ' thumbnail link(s)...',
            cnt: 1,
            progress: 0
        });

        // Request signed download URLs from the server
        $.post('/libraries/elfinderLibs/endpoints/getElfinderDownloadLinksEndpoint.php', {
            hashes: fileHashes
        }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });

            if (response.success && response.urls && response.urls.length > 0) {
                // Append &thumb=1 to each URL
                var thumbUrls = $.map(response.urls, function(url) {
                    return url + (url.indexOf('?') > -1 ? '&' : '?') + 'thumb=1';
                });
                var linkText = thumbUrls.join('\n');

                // Copy to clipboard
                var msg = thumbUrls.length + ' thumbnail link(s) copied to clipboard!';
                if (response.errors && response.errors.length > 0) {
                    msg += ' (' + response.errors.length + ' file(s) failed)';
                }
                copyToClipboard(linkText, msg, fm);
            } else {
                fm.notify({ type: 'error', msg: response.error || 'Failed to generate thumbnail links.' });
            }
        }, 'json').fail(function() {
            fm.notify({ type: 'info', cnt: -1 });
            fm.notify({ type: 'error', msg: 'Server request failed. Check your connection.' });
        }).always(function() {
            dfrd.resolve();
        });

        return dfrd.promise();
    };

    // Only enable for exactly one file that has a cached thumbnail
    this.getstate = function() {
        var sel = this.fm.selectedFiles();
        if (sel.length !== 1) return -1;
        return sel[0].tmb ? 0 : -1;
    };
};
