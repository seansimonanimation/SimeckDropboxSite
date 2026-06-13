/**
 * CopyDirectLink.js
 * 
 * Generates HMAC-signed download links for selected files and copies them
 * to the clipboard. Supports multi-file selection.
 *
 * @commandID CopyDirectLink
 * @nicename Copy Download Link
 * @role client
 * @loc files
 * @order 4
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.CopyDirectLink = function() {
    this.contextmenu = true;

    this.init = function() {
        this.title = 'Copy Download Link';
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
            msg: 'Generating ' + fileHashes.length + ' download link(s)...',
            cnt: 1,
            progress: 0
        });

        // Request signed download URLs from the server
        $.post('/libraries/elfinderLibs/endpoints/getElfinderDownloadLinksEndpoint.php', {
            hashes: fileHashes
        }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });

            if (response.success && response.urls && response.urls.length > 0) {
                var linkText = response.urls.join('\n');

                // Copy to clipboard
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(linkText).then(function() {
                        var msg = response.urls.length + ' download link(s) copied to clipboard!';
                        if (response.errors && response.errors.length > 0) {
                            msg += ' (' + response.errors.length + ' file(s) failed)';
                        }
                        fm.notify({ type: 'info', msg: msg });
                    }).catch(function() {
                        // Clipboard API failed (e.g., not in secure context), fallback to prompt
                        prompt('Copy these download link(s) (Ctrl+C, then Enter):', linkText);
                    });
                } else {
                    // Fallback for browsers without clipboard API
                    prompt('Copy these download link(s) (Ctrl+C, then Enter):', linkText);
                }
            } else {
                fm.notify({ type: 'error', msg: response.error || 'Failed to generate download links.' });
            }
        }, 'json').fail(function() {
            fm.notify({ type: 'info', cnt: -1 });
            fm.notify({ type: 'error', msg: 'Server request failed. Check your connection.' });
        }).always(function() {
            dfrd.resolve();
        });

        return dfrd.promise();
    };

    this.getstate = function() {
        return this.fm.selectedFiles().length ? 0 : -1;
    };
};
