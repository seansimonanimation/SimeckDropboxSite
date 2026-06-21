/**
 * CopyThumbnailLink.js
 *
 * Generates a download link with the &thumb=1 parameter appended,
 * then copies it to the clipboard.
 *
 * @commandID copyThumbnailLink
 * @nicename Copy Permalinks///Thumbnail
 * @role admin
 * @loc files
 * @order 7
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.copyThumbnailLink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Thumbnail Permalink'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        if (files.length === 0) { fm.notify({ type: 'error', msg: 'No files selected.' }); return dfrd.reject(); }
        var fileHashes = [];
        $.each(files, function(i, f) { fileHashes.push(f.hash); });
        fm.notify({ type: 'info', msg: 'Generating ' + fileHashes.length + ' thumbnail link(s)...', cnt: 1, progress: 0 });
        $.post('/libraries/elfinderLibs/endpoints/getElfinderDownloadLinksEndpoint.php', { hashes: fileHashes }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });
            if (response.success && response.urls) copyToClipboard(response.urls.join('\n'), 'Thumbnail permalink(s) copied to clipboard!', fm);
            else fm.notify({ type: 'error', msg: response.error || 'Failed to generate links.' });
        }, 'json').fail(function() { fm.notify({ type: 'info', cnt: -1 }); fm.notify({ type: 'error', msg: 'Server request failed.' }); }).always(function() { dfrd.resolve(); });
        return dfrd.promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
