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
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 0) {
            return $.Deferred().resolve();
        }

        var f = files[0];
        $.post('libraries/elfinderLibs/endpoints/notifyClientEndpoint.php', {
            filepath: fm.url(f.hash)
        }, function(html) {
            $('body').append(html);
        }, 'html').fail(function() {
            fm.notify({ type: 'error', msg: 'Failed to load Notify Client dialog.' });
        });

        return $.Deferred().resolve();
    };
    
this.getstate = function() {
    var fm = this.fm;
    var session = window.simeckSession;
    
    function getProjectFolder(decodedPath) {
        if (!decodedPath) return null;
        var match = decodedPath.match(/^clientProjects\/([^\/]+)/);
        return match ? match[1] : null;
    }
    
    function hasClientLead(folderName) {
        return session.projectLeaders && 
               session.projectLeaders[folderName] ? true : false;
    }
    
    // Check selected files first (for context menu on items)
    var selected = fm.selectedFiles();
    if (selected.length > 0) {
        for (var i = 0; i < selected.length; i++) {
            var fileHash = selected[i] && selected[i].hash;
            if (!fileHash) continue;
            var path = decodeElfinderHash(fileHash);
            if (!path) continue;
            var folder = getProjectFolder(path);
            if (folder && hasClientLead(folder)) {
                return 0;
            }
        }
        return -1;
    }
    
    // Check cwd (for right-click on empty space in the folder)
    var cwd = fm.cwd();
    if (!cwd || !cwd.hash) return -1;
    var cwdPath = decodeElfinderHash(cwd.hash);
    if (!cwdPath) return -1;
    var cwdFolder = getProjectFolder(cwdPath);
    if (cwdFolder && hasClientLead(cwdFolder)) {
        return 0;
    }
    
    return -1;
};


};
