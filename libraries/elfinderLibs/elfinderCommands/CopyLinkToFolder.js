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
 * @nicename Copy link to this folder
 * @role artist
 * @loc cwd, files
 * @order 5
 * @contextMenuDividers below
 */

elFinder.prototype.commands.CopyLinkToFolder = function() {
    this.contextmenu = true;

    this.init = function() {
        this.title = 'Copy Link to This Folder';
    };

    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        var dfrd = $.Deferred();

        // Collect unique parent folder hashes
        var parentHashes = [];
        $.each(files, function(i, f) {
            if (f.phash && parentHashes.indexOf(f.phash) === -1) {
                parentHashes.push(f.phash);
            }
        });

        if (parentHashes.length === 0) {
            // Fallback to the current working directory
            parentHashes.push(fm.cwd().hash);
        }

        var baseUrl = window.location.protocol + '//' + window.location.host;
        var session = window.simeckSession;
        var links = [];
        $.each(parentHashes, function(i, phash) {
            var adjustedHash = phash;
            if (phash.startsWith('s1_')) {
                var raw = phash.substring(3);
                var b64 = raw.replace(/-/g, '+').replace(/_/g, '/').replace(/\./g, '=');
                var path = atob(b64);
                var userName = session.lastname + ', ' + session.firstname;
                var reEncoded = btoa(userName + '/' + path)
                    .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.');
                adjustedHash = 's2_' + reEncoded;
            }
            links.push(baseUrl + '/viewfolder.php?folderid=' + encodeURIComponent(adjustedHash));
        });
        var linkText = links.join('\n');

        // Copy to clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(linkText).then(function() {
                var message = '';
                if (links.length === 1) {
                    message = 'Folder link copied to clipboard!';
                } else {
                    message = links.length + ' folder link(s) copied to clipboard!';
                }
                fm.notify({ type: 'info', msg: message });
            }).catch(function() {
                prompt('Copy these folder link(s) (Ctrl+C, then Enter):', linkText);
            });
        } else {
            prompt('Copy these folder link(s) (Ctrl+C, then Enter):', linkText);
        }

        return dfrd.promise();
    };

    this.getstate = function() {
        return 0;
    };
};
//