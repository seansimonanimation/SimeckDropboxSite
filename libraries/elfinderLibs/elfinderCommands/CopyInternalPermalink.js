/**
 * CopyInternalPermalink.js
 *
 * @commandID CopyInternalPermalink
 * @nicename Copy Permalinks///Internal
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 6
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyInternalPermalink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Internal Permalink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyInternalPermalink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
