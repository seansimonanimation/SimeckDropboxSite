/**
 * @commandID notifyClient
 * @nicename Send client a Notification
 * @role artist
 * @loc files, cwd
 * @order 7
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.notifyClient = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send client a Notification';
    };
    
    this.exec = function(hashes) {
        return $.Deferred().resolve();
    };
    
this.getstate = function() {
    var fm = this.fm;
    var session = window.simeckSession;
    
    // Decode elFinder hash to filesystem path
    function hashToPath(hash) {
        if (!hash) return null;
        var b64 = hash.replace(/^s3_/, '')
                       .replace(/-/g, '+')
                       .replace(/_/g, '/')
                       .replace(/\./g, '=');
        try {
            return atob(b64);
        } catch(e) {
            return null;
        }
    }
    
    // Extract the project folder name from a decoded path
    // e.g. "clientProjects/C01_SetSail/subfolder" -> "C01_SetSail"
    function getProjectFolder(decodedPath) {
        if (!decodedPath) return null;
        var match = decodedPath.match(/^clientProjects\/([^\/]+)/);
        return match ? match[1] : null;
    }
    
    // Check if a project folder has a client lead assigned
    function hasClientLead(folderName) {
        return session.projectLeaders && 
               session.projectLeaders[folderName] ? true : false;
    }
    
    // Check selected files first (for context menu on items)
    var selected = fm.selectedFiles();
    if (selected.length > 0) {
        for (var i = 0; i < selected.length; i++) {
            var path = hashToPath(selected[i].hash);
            var folder = getProjectFolder(path);
            if (folder && hasClientLead(folder)) {
                return 0;
            }
        }
        return -1; // no selected item is in a client project with a lead
    }
    
    // Check cwd (for right-click on empty space in the folder)
    var cwd = fm.cwd();
    var cwdPath = hashToPath(cwd.hash);
    var cwdFolder = getProjectFolder(cwdPath);
    if (cwdFolder && hasClientLead(cwdFolder)) {
        return 0;
    }
    
    return -1;
};

};
