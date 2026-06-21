/**
 * @commandID notifyClient
 * @nicename Send Notification to Client
 * @role artist
 * @availableToHigherRoles true
 * @loc files, cwd
 * @order 14
 * @contextMenuDividers above
 */
//
elFinder.prototype.commands.notifyClient = function() {
    this.contextmenu = true;
    this.init = function(){ this.title = 'Send Notification to Client'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles();
        if (files.length === 0) return $.Deferred().resolve();
        var f = files[0];
        $.post('libraries/elfinderLibs/endpoints/notifyClientEndpoint.php', { filepath: fm.url(f.hash) }, function(html) { $('body').append(html); }, 'html').fail(function() { fm.notify({ type: 'error', msg: 'Failed to load Notify Client dialog.' }); });
        return $.Deferred().resolve();
    };
    this.getstate = function() {
        var fm = this.fm, session = window.simeckSession;
        function getProjectFolder(decodedPath) { if (!decodedPath) return null; var match = decodedPath.match(/^clientProjects\/([^\/]+)/); return match ? match[1] : null; }
        function hasClientLead(folderName) { return session.projectLeaders && session.projectLeaders[folderName] ? true : false; }
        var sel = fm.selectedFiles(); if (sel.length === 0) return 0;
        var filePath = fm.url(sel[0].hash), decoded = decodeElfinderHash(sel[0].hash), projectFolder = getProjectFolder(decoded);
        if (!projectFolder || !hasClientLead(projectFolder)) return -1;
        return 0;
    };
};
