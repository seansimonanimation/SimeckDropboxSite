/**
 * CopyThumbnailPermalink.js
 *
 * @commandID CopyThumbnailPermalink
 * @nicename Copy Permalinks///Thumbnail
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 7
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyThumbnailPermalink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Thumbnail Permalink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyThumbnailPermalink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
