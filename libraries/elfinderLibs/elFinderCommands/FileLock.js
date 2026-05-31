/**
 * @commandID lockFile
 * @nicename Lock File
 */


//Only admins and project leads should see this option.
//It sets both the asset lock AND the comment lock.
//It refuses to run if the file is already locked.


//CURRENTLY WORKING ON THIS.
elFinder.prototype.commands.LockFile = function() {

    //Sets this as a context menu item.
    if(role === 'admin'){this.contextmenu = true;} else {this.contextmenu = false;}
    this.contextMenu = OverrideCount > 0;
    
    this.init = function(){
        var role = this.fm.options.role || '';
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var url = fm.url(files[0].hash);
            var hash = files[0].hash;
            var $node = fm.getFile(hash);
            var filepath = url;
            this.title = fm.cache?.['lockedPaths']?.[filepath] ? '' : 'Lock File';
        } else {            
            var url, hash, node, filepath = '';
            this.title = '';
        }
    };

    if(url === ''){return;}

    this.exec = function(){
        var fm = this.fm;
        var files = fm.selectedFiles;
        $.post('/libraries/elfinderLibs/endpoints/LockFileEndpoint.php', {
            filepath: fm.selectedFiles[0].filepath
        }, function(response){
            if (response.success) {
                fm.exec('reload');
            } else {
                fm.error(response.error || 'File was already locked.');
            }
            return $.Deferred().resolve();
        });

    };
}