/**
 * @commandID clientLockOverride
 * @nicename Use Lock Override
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

    this.exec = function(){
        var fm= this.fm;
        var files = fm.selectedFiles();
        if(files.length !== 1){
            fm.error('You can only override one file at a time.');
            return $.Deferred().resolve();
        }
        if (!confirm('Use one lock override to unlock comments on "' + files[0].name + '"?')) {
            return $.Deferred().resolve();
        }
        $.post('libraries/elfinderLibs/endpoints/ClientUseOverrideTokenEndpoint.php', {
            filepath: fm.url(files[0].hash)
        }, function(response) {
            if (response.success) {
                fm.simeckSession.lock_overrides -= 1;
                location.reload();
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
        var url = fm.url(sel[0].hash);
        // Only show if the file has a comment lock
        if (!fm.cache?.lockedPaths?.[url]?.commentlock) return -1;
        // Only show if the user has overrides
        if (!fm.simeckSession || typeof fm.simeckSession.lock_overrides === 'undefined' || fm.simeckSession.lock_overrides <= 0) return -1;
        return 0;
    };

};