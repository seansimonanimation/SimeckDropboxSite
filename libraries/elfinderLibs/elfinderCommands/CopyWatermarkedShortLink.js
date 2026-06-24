/**
 * CopyWatermarkedShortlink.js
 *
 * @commandID CopyWatermarkedShortlink
 * @nicename Copy Shortlinks///Watermarked
 * @role client
 * @availableToHigherRoles true
 * @loc files
 * @order 7
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyWatermarkedShortlink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Watermarked Shortlink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyWatermarkedShortlink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
