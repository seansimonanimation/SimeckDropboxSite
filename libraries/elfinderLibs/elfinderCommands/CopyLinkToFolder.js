/**
 * CopyLinkToFolder.js
 *
 * Copies a link to the parent folder of the selected file(s). When opened,
 * the link redirects through viewfolder.php which checks permissions and
 * opens elFinder at that folder location.
 *
 * For multiple files from different parent folders, generates separate links.
 *
 * @commandID CopyLinkToFolder
 * @nicename Copy Permalinks///Folder
 * @role artist
 * @loc cwd, files
 * @order 5
 * @contextMenuDividers above
 */

elFinder.prototype.commands.CopyLinkToFolder = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Link to This Folder'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        var parentHashes = [];
        $.each(files, function(i, f) {
            if (f.phash && parentHashes.indexOf(f.phash) === -1) parentHashes.push(f.phash);
        });
        if (parentHashes.length === 0) parentHashes.push(fm.cwd().hash);
        var baseUrl = window.location.protocol + '//' + window.location.host;
        var session = window.simeckSession, links = [];
        $.each(parentHashes, function(i, phash) {
            var adjustedHash = phash;
            if (phash.startsWith('s1_')) {
                var path = decodeElfinderHash(phash);
                var userName = session.lastname + ', ' + session.firstname;
                adjustedHash = '#elf_' + encodeURIComponent(userName) + path.replace(/^\//, '');
            }
            links.push(baseUrl + '/viewfolder.php?hash=' + encodeURIComponent(adjustedHash));
        });
        copyToClipboard(links.join('\n'), 'Folder link copied to clipboard!', fm);
        dfrd.resolve();
        return dfrd.promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
