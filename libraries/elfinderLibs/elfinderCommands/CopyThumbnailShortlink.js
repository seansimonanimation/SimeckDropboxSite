/**
 * CopyThumbnailShortlink.js
 *
 * @commandID CopyThumbnailShortlink
 * @nicename Copy Shortlinks///Thumbnail
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 7
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyThumbnailShortlink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Thumbnail Shortlink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyThumbnailShortlink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
