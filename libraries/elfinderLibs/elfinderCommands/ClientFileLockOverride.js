/**
 * @commandID clientLockOverride
 * @nicename Use Lock Override
 * @role clientOnly
 * @loc files
 * @order 1
 * @contextMenuDividers below
 */
elFinder.prototype.commands.clientLockOverride = function() {
    //Sets this as a context menu item, but only if the user has overrides available.
    if(window.simeckSession && window.simeckSession.tempRole === 'client'){this.contextmenu = true;} else {this.contextmenu = false;}
//


this.init = function(){
    var fm = this.fm;
    this.title = 'Use Lock Override';  // Default fallback
    var files = fm.selectedFiles();
    if(files.length === 1){
        var url = fm.url(files[0].hash);
        var filepath = url;
    }
};

    this.exec = function() {
        var fm = this.fm;
        var dfrd = $.Deferred();
        var files = fm.selectedFiles();

        if (files.length !== 1) {
            fm.error('You can only override one file at a time.');
            return dfrd.reject();
        }
        if (!confirm('Use one lock override to unlock comments on "' + files[0].name + '"?')) {
            return dfrd.reject();
        }
        var filepath = getSimeckLockFilePath(fm, files[0].hash);

        $.post('libraries/elfinderLibs/endpoints/ClientUseOverrideTokenEndpoint.php', {
            filepath: filepath
        }, function(response) {
            if (response.success) {
                if (fm.simeckSession) {
                    fm.simeckSession.lock_overrides = Math.max(0, (fm.simeckSession.lock_overrides || 0) - 1);
                }
                populateLockCache(fm);
                if (fm.selectedFiles().length === 1) {
                    updatePreviewPane(fm);
                }
                dfrd.resolve();
            } else {
                fm.error(response.error || 'Override failed.');
                dfrd.reject();
            }
        }, 'json').fail(function() {
            fm.error('Server error while performing override.');
            dfrd.reject();
        });

        return dfrd.promise();
    };
    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var url = getSimeckLockFilePath(fm, sel[0].hash);
        // Only show if the file has a comment lock
        if (!fm.cache?.lockedPaths?.[url]?.commentlock) return -1;
        // Only show if the user has overrides
        if (!fm.simeckSession || typeof fm.simeckSession.lock_overrides === 'undefined' || fm.simeckSession.lock_overrides <= 0) return -1;
        return 0;
    };

};