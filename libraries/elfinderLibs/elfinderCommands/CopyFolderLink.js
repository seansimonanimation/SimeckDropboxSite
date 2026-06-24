/**
 * CopyFolderLink.js
 *
 * @commandID CopyFolderLink
 * @nicename Copy Folder Link
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 5
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyFolderLink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Folder Link'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyFolderLink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 0 : -1; };
};
