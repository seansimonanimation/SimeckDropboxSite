/**
 * @commandID rm
 * @nicename Delete
 * @role client
 * @loc files
 */
//
elFinder.prototype.commands.rm = function() {
    this.contextmenu = true;
    
    this.init = function() {
        this.title = 'Delete';
    };
    
this.getstate = function() {
    var fm = this.fm;
    var sel = fm.selectedFiles();
    if (sel.length === 0) return -1;
    
    // For clients, hide delete if any selected file has ANY lock
    if (window.simeckSession && window.simeckSession.tempRole === 'client') {
        for (var i = 0; i < sel.length; i++) {
            var url = fm.url(sel[i].hash);
            if (fm.cache && fm.cache.lockedPaths && fm.cache.lockedPaths[url]) {
                var lock = fm.cache.lockedPaths[url];
                if (lock.assetlock == 1 || lock.commentlock == 1) {
                    return -1;
                }
            }
        }
    }
    
    return 0;
};

    
    this.exec = function() {
        var fm = this.fm;
        var files = fm.selectedFiles();
        
        if (files.length === 0) {
            fm.error('No files selected.');
            return $.Deferred().resolve();
        }
        
        // Build list of names for confirmation dialog
        var names = [];
        var hasFolders = false;
        $.each(files, function(i, file) {
            names.push(file.name);
            if (file.mime === 'directory') {
                hasFolders = true;
            }
        });
        
        // Build confirmation message
        var message = 'Delete ' + files.length + ' item(s)?\n\n';
        message += names.join('\n');
        if (hasFolders) {
            message += '\n\nWARNING: Folders will be deleted recursively, including all contents, comments, and locks.';
        }
        
        if (!confirm(message)) {
            return $.Deferred().resolve();
        }
        
        // Build array of file URLs (root-relative paths)
        var paths = [];
        $.each(files, function(i, file) {
            paths.push(fm.url(file.hash));
        });
        
        // Show loading indicator
        fm.notify({
            type: 'rm',
            cnt: files.length,
            hideCnt: true
        });
        
        // POST to the rm override endpoint
        $.post('libraries/elfinderLibs/endpoints/RmOverrideEndpoint.php', {
            paths: paths
        }, function(response) {
            fm.notify({
                type: 'rm',
                cnt: -files.length
            });
            
            if (response.success) {
                // Reload the current directory to reflect changes
                // Remove the beforeunload handler to bypass the "unsaved changes" prompt
                SimeckRefresh();


                
                // Show success message
                var deletedCount = response.deleted ? response.deleted.length : 0;
                fm.toast({
                    msg: deletedCount + ' item(s) deleted successfully.',
                    mode: 'success',
                    time: 3000
                });
            } else {
                // Show errors
                if (response.errors && response.errors.length > 0) {
                    fm.error(response.errors.join('\n'));
                } else {
                    fm.error(response.error || 'Delete operation failed.');
                }
                
                // Still reload in case partial success
                if (response.deleted && response.deleted.length > 0) {
                    // Remove the beforeunload handler to bypass the "unsaved changes" prompt
                    SimeckRefresh();

                }
            }
        }, 'json').fail(function(xhr, status, error) {
            fm.notify({
                type: 'rm',
                cnt: -files.length
            });
            fm.error('Server error during delete: ' + error);
        });
        
        return $.Deferred().resolve();
    };
};
