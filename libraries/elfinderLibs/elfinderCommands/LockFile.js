/**
 * @commandID lockFile
 * @nicename Lock File
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 3
 * @contextMenuDividers none
 */
elFinder.prototype.commands.lockFile = function() {
    this.contextmenu = true;
    this.init = function(){ this.title = 'Lock File'; };
    this.exec = function() {
        var fm = this.fm, dfrd = $.Deferred(), sel = fm.selectedFiles();
        if (sel.length !== 1) { fm.error('You must select exactly one file to lock.'); return dfrd.reject(); }
        var filepath = getSimeckLockFilePath(fm, sel[0].hash);
        $.post('libraries/elfinderLibs/endpoints/LockFileEndpoint.php', { filepath: filepath }, function(response) {
            if (response.success) {
                populateLockCache(fm);
                if (fm.selectedFiles().length === 1) updatePreviewPane(fm);
                dfrd.resolve();
            } else { fm.error(response.error || 'File was already locked.'); dfrd.reject(); }
        }).fail(function() { fm.error('Server request failed.'); dfrd.reject(); });
        return dfrd.promise();
    };
this.getstate = function() {
    var fm = this.fm, sel = fm.selectedFiles();
    if (sel.length !== 1) return -1;
    if (fm.role === 'admin') return 0;  // admins always have access
    var decodedPath = decodeElfinderHash(sel[0].hash);
    var projectFolder = decodedPath && decodedPath.match(/^clientProjects\/([^\/]+)/);
    if (!projectFolder) return -1;
    var session = window.simeckSession;
    var isLeader = session.projectLeaders && session.projectLeaders[projectFolder[1]];
    return isLeader ? 0 : -1;
};

};
