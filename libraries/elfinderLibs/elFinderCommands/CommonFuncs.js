

this.prototype.commands.CommonFuncs = function() {

    this.GetAllLockedFilesInProjects = function() {
        var fm = this.fm;
        $.post('libraries/elfinderLibs/endpoints/GetLockedSubfilesInProjectEndpoint.php', {
            directory: '/files/Projects'
        }, function(response) {
            if (response.success) {
                fm.cache.lockedPaths = response.lockedPaths;
            }
        }, 'json');
    }
    this.GetAllLockedFilesInProjects = function(projectPath) {
        var fm = this.fm;
        $.post('libraries/elfinderLibs/endpoints/GetLockedSubfilesInProjectEndpoint.php', {
            directory: projectPath
        }, function(response) {
            if (response.success) {
                fm.cache.lockedPaths = response.lockedPaths;
            }
        }, 'json');
    }


    this.GetCommentLockStatus = function(fileUrl) {
        var fm = this.fm;
        if (fm.cache && fm.cache.lockedPaths && fm.cache.lockedPaths[fileUrl]) {
            return fm.cache.lockedPaths[fileUrl].commentlock;
        }
        return false;
    };
};
