/**
 * @commandID clientlockoverride
 * @nicename File Lock Override
 */
elFinder.prototype.commands.clientlockoverride = function() {
    this.contextmenu = true;

    this.init = function(){
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var fileUrl = fm.url(files[0].hash);
            var lockInfo = fm.cache?.lockedPaths?.[fileUrl];
            if (lockInfo && lockInfo.commentlock) {
                var overrides = fm.cache?.clientOverrides ?? 0;
                this.title = 'Lock Override (' + overrides + ' remaining)';
            } else {
                this.title = 'Lock Override';
            }
        } else {
            this.title = 'Lock Override';
        }
    };

    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length !== 1) {
            fm.error('You can only override one file at a time.');
            return $.Deferred().resolve();
        }
        var fileUrl = fm.url(files[0].hash);
        
        var overrides = fm.cache?.clientOverrides ?? 0;
        if (!confirm('Use one lock override (' + overrides + ' remaining) to unlock comments on "' + files[0].name + '"?')) {
            return $.Deferred().resolve();
        }

        $.post('libraries/elfinderLibs/endpoints/lockedFilesEndpoint.php', {
            action: 'override',
            filepath: fileUrl
        }, function(response) {
            if (response.success) {
                if (fm.cache?.lockedPaths?.[fileUrl]) {
                    fm.cache.lockedPaths[fileUrl].commentlock = 0;
                }
                if (typeof response.remaining_overrides !== 'undefined') {
                    fm.cache.clientOverrides = response.remaining_overrides;
                }
                fm.exec('reload');
            } else {
                fm.error(response.error || 'Override failed.');
            }
        }, 'json').fail(function() {
            fm.error('Server error while performing override.');
        });

        return $.Deferred().resolve();
    };

    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var role = fm.options.role || '';
        if (role !== 'client') return -1;
        
        var url = fm.url(sel[0].hash);
        var lockInfo = fm.cache?.lockedPaths?.[url];
        if (!lockInfo) return -1;
        if (!lockInfo.commentlock) return -1;
        return 0;
    };
};