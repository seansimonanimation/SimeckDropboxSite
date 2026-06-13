/**
 * @commandID lockFile
 * @nicename Lock File
 * @role admin
 * @loc files
 * @order 2
 * @contextMenuDividers below
 */


//Only admins and project leads should see this option.
//It sets both the asset lock AND the comment lock.
//It refuses to run if the file is already locked.


//CURRENTLY WORKING ON THIS.
elFinder.prototype.commands.lockFile = function() {

    //Sets this as a context menu item.
    if(window.simeckSession && window.simeckSession.tempRole === 'admin'){this.contextmenu = true;} else {this.contextmenu = false;}

    
    this.init = function(){
        this.title = 'Lock File';
    };

    this.exec = function() {
        var fm = this.fm;
        var dfrd = $.Deferred();
        var sel = fm.selectedFiles();

        if (sel.length !== 1) {
            fm.error('You must select exactly one file to lock.');
            return dfrd.reject();
        }

        var filepath = getSimeckLockFilePath(fm, sel[0].hash);
        $.post('libraries/elfinderLibs/endpoints/LockFileEndpoint.php', {
            filepath: filepath
        }, function(response) {
            if (response.success) {
                populateLockCache(fm);
                if (fm.selectedFiles().length === 1) {
                    updatePreviewPane(fm);
                }
                dfrd.resolve();
            } else {
                fm.error(response.error || 'File was already locked.');
                dfrd.reject();
            }
        }, 'json').fail(function() {
            fm.error('Server error while locking file.');
            dfrd.reject();
        });

        return dfrd.promise();
    };
    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var role = fm.options.role || '';
        if (role !== 'admin' && role !== 'artist') return -1;
        var url = getSimeckLockFilePath(fm, sel[0].hash);
        if (fm.cache?.lockedPaths?.[url]) return -1;
        return 0;
    };



}//