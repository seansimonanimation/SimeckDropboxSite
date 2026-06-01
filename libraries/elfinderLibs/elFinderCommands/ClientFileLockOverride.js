/**
 * @commandID clientLockOverride
 * @nicename File Lock Override
 */
elFinder.prototype.commands.clientLockOverride = function() {
    var OverrideCount = 0;
    //Sets this as a context menu item, but only if the user has overrides available.
    this.contextMenu = true;


    this.init = function(){
        var fm = this.fm;
        OverrideCount = fm.cache?.clientOverrides;
        var files = fm.selectedFiles();
        //If there's only one file selected,
        if(files.length === 1){
            var url = fm.url(files[0].hash);
            var hash = files[0].hash;
            var $node = fm.getFile(hash);
            var filepath = url;
            this.title = fm.cache?.['lockedPaths']?.[filepath]?.commentlock
                ? 'Lock Override (' + (OverrideCount ?? 0) + ' remaining)' 
                : '';
        }
        this.contextMenu = OverrideCount > 0;
    };
    this.exec = function(){
        var fm= this.fm;
        var files = fm.selectedFiles();
        if(files.length !== 1){
            fm.error('You can only override one file at a time.');
            return $.Deferred().resolve();
        }
        if (!confirm('Use one lock override (' + OverrideCount + ' remaining) to unlock comments on "' + files[0].name + '"?')) {
            return $.Deferred().resolve();
        }
        $.post('libraries/elfinderLibs/endpoints/ClientUseOverrideTokenEndpoint.php', {
            filepath: fm.url(files[0].hash)
        }, function(response) {
            if (response.success) {
                OverrideCount -= 1;
                fm.exec('reload');
            } else {
                fm.error(response.error || 'Override failed.');
            }
        }, 'json').fail(function() {
            fm.error('Server error while performing override.');
        });

        return $.Deferred().resolve();
    };
};