/**
 * CopyInternalShortlink.js
 *
 * @commandID CopyInternalShortlink
 * @nicename Copy Shortlinks///Internal
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 7
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyInternalShortlink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Internal Shortlink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyInternalShortlink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
