/**
 * @commandID MakeFileDeliverable
 * @nicename Convert File to Deliverable
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 4
 * @contextMenuDividers below
 */
//
elFinder.prototype.commands.MakeFileDeliverable = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Convert File to Deliverable'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        if (files.length === 0) { fm.notify({ type: 'error', msg: 'No files selected.' }); return dfrd.reject(); }
        if (files.length > 1) { fm.notify({ type: 'error', msg: 'Please select only one file.' }); return dfrd.reject(); }
        var fileHash = files[0].hash;
        fm.notify({ type: 'info', msg: 'Toggling deliverable status...', cnt: 1, progress: 0 });
        $.post('/libraries/elfinderLibs/endpoints/toggleDeliverableEndpoint.php', { hash: fileHash }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });
            if (response.success) {
                var status = response.deliverable ? 'enabled' : 'disabled';
                fm.notify({ type: 'info', msg: 'Deliverable status ' + status + ' for this file.' });
                if (typeof populateDeliverableCache === 'function') populateDeliverableCache(fm);
                dfrd.resolve();
            } else { fm.notify({ type: 'error', msg: response.error || 'Failed to toggle deliverable.' }); dfrd.reject(); }
        }).fail(function() { fm.notify({ type: 'error', msg: 'Server request failed.' }); dfrd.reject(); });
        return dfrd.promise();
    };
this.getstate = function() {
    var fm = this.fm, sel = fm.selectedFiles();
    if (sel.length !== 1) return -1;
    if (fm.role === 'admin') return 0;
    var decodedPath = decodeElfinderHash(sel[0].hash);
    var projectFolder = decodedPath && decodedPath.match(/^clientProjects\/([^\/]+)/);
    if (!projectFolder) return -1;
    var session = window.simeckSession;
    var isLeader = session.projectLeaders && session.projectLeaders[projectFolder[1]];
    return isLeader ? 0 : -1;
};

};
