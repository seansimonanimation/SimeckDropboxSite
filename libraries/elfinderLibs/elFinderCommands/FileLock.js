/**
 * @commandID togglelock
 * @nicename Lock File
 */
elFinder.prototype.commands.togglelock = function() {
    this.contextmenu = true;

    this.init = function(){
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var url = fm.url(files[0].hash);
            var hash = files[0].hash;
            var $node = fm.getFile(hash);
            var filepath = url;
            this.title = fm.cache?.['lockedPaths']?.[filepath] 
                ? 'Unlock File' : 'Lock File';
        } else {
            this.title = 'Lock File';
        }
    };

    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length !== 1) {
            fm.error('You can only lock/unlock one file at a time.');
            return $.Deferred().resolve();
        }
        var fileUrl = fm.url(files[0].hash);
        if (fileUrl.indexOf('/files/') !== 0) {
            fm.error('Can only lock/unlock files under /files/');
            return $.Deferred().resolve();
        }

        var isLocked = fm.cache && fm.cache.lockedPaths && fm.cache.lockedPaths[fileUrl];
        var action = isLocked ? 'unlock' : 'lock';

        $.post('libraries/elfinderLibs/endpoints/lockedFilesEndpoint.php', {
            action: action,
            filepath: fileUrl
        }, function(response) {
            if (response.success) {
                fm.exec('reload');
            } else {
                fm.error(response.error || 'Failed to ' + action + ' file.');
            }
        }, 'json').fail(function() {
            fm.error('Server error while ' + action + 'ing file.');
        });

        return $.Deferred().resolve();
    };

    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var role = fm.options.role || '';
        if (role !== 'admin' && role !== 'artist') return -1;
        return 0;
    };
};