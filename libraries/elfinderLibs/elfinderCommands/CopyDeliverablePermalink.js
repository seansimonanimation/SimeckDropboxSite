/**
 * CopyDeliverablePermalink.js
 *
 * @commandID CopyDeliverablePermalink
 * @nicename Copy Permalinks///Deliverable
 * @role client
 * @availableToHigherRoles true
 * @loc files
 * @order 9
 * @contextMenuDividers none
 */
elFinder.prototype.commands.CopyDeliverablePermalink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Deliverable Permalink'; };
    this.exec = function(hashes) {
        SimeckClipboardCommands.CopyDeliverablePermalink(this.fm, hashes[0]);
        return $.Deferred().resolve().promise();
    };
this.getstate = function() {
    var fm = this.fm;
    if (!fm) return -1;
    this.variants = [];  // rebuild each time
    for (var k = 0; k < kids.length; k++) {
        var cmdName = kids[k].commandID;
        var cmd = fm.getCommand(cmdName);
        if (cmd && typeof cmd.getstate === 'function') {
            try {
                if (cmd.getstate() === 0) {
                    this.variants.push([cmdName, kids[k].label]);
                }
            } catch(e) {}
        }
    }
    return this.variants.length > 0 ? 0 : -1;
};

};
