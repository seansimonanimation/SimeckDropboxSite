/**
 * @commandID ClientLockOverride
 * @nicename Use Lock Override
 * @role client
 * @availableToHigherRoles false
 * @loc files
 * @order 2
 * @contextMenuDividers below
 */
elFinder.prototype.commands.ClientLockOverride = function() {
    if(window.simeckSession && window.simeckSession.tempRole === 'client'){this.contextmenu = true;} else {this.contextmenu = false;}
    this.init = function(){
        this.title = 'Use Lock Override';
    };
    this.exec = function() {
        var fm = this.fm, dfrd = $.Deferred(), files = fm.selectedFiles();
        if (files.length !== 1) { fm.error('You can only override one file at a time.'); return dfrd.reject(); }
        if (!confirm('Use one lock override to unlock comments on "' + files[0].name + '"?')) { return dfrd.reject(); }
        var filepath = getSimeckLockFilePath(fm, files[0].hash);
        $.post('libraries/elfinderLibs/endpoints/ClientUseOverrideTokenEndpoint.php', { filepath: filepath }, function(response) {
            if (response.success) {
                if (fm.simeckSession) fm.simeckSession.lock_overrides = Math.max(0, (fm.simeckSession.lock_overrides || 0) - 1);
                populateLockCache(fm);
                if (fm.selectedFiles().length === 1) updatePreviewPane(fm);
                dfrd.resolve();
            } else { fm.error(response.error || 'Override failed.'); dfrd.reject(); }
        }).fail(function() { fm.error('Server request failed.'); dfrd.reject(); });
        return dfrd.promise();
    };
    this.getstate = function() {
        var fm = this.fm, files = fm.selectedFiles();
        if (files.length !== 1) return -1;
        var filepath = getSimeckLockFilePath(fm, files[0].hash);
        if (!filepath) return -1;
        var norm = normalizeSimeckFilePath(filepath);
        var lockInfo = null;
        if (fm.cache.lockedPaths) {
            for (var cachedPath in fm.cache.lockedPaths) {
                if (normalizeSimeckFilePath(cachedPath).endsWith(norm) || normalizeSimeckFilePath(cachedPath) === norm) {
                    lockInfo = fm.cache.lockedPaths[cachedPath];
                    break;
                }
            }
        }

        if (!lockInfo) return -1;
        if (lockInfo.deliverable) return -1;
        return 1;
    };
};
