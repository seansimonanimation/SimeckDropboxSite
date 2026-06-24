/**
 * CopyWatermarkedPermalink.js
 *
 * @commandID CopyWatermarkedPermalink
 * @nicename Copy Permalinks///Watermarked
 * @role client
 * @availableToHigherRoles true
 * @loc files
 * @order 8
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyWatermarkedPermalink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Watermarked Permalink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyWatermarkedPermalink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
