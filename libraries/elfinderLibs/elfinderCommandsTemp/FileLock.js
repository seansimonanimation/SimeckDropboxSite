/**
 * @commandID lockFile
 * @nicename Lock File
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

    this.exec = function(){
        var fm = this.fm;
        $.post('libraries/elfinderLibs/endpoints/LockFileEndpoint.php', {
            filepath: fm.url(fm.selectedFiles()[0].hash)
        }, function(response){
            if (response.success) {
                fm.exec('reload');
            } else {
                fm.error(response.error || 'File was already locked.');
            }
            return $.Deferred().resolve();
        });
    };
    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var role = fm.options.role || '';
        if (role !== 'admin' && role !== 'artist') return -1;
        var url = fm.url(sel[0].hash);
        if (fm.cache?.lockedPaths?.[url]) return -1;
        return 0;
    };



}//