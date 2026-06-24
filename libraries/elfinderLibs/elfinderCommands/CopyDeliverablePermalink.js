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
        var fm = this.fm, session = window.simeckSession, sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        if (session.tempRole === 'admin') return 0;
        if (!isDeliverableFile(sel[0].hash, fm)) return -1;
        if(session.tempRole === 'artist'){if (!hasPoCRequirementForHash(sel[0].hash)) return -1;}
        if (session.tempRole === 'client') return 0;
        return 0;
    };
};
