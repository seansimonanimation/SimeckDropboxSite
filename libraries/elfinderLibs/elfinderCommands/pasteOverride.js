/**
 * pasteOverride.js
 * 
 * Overrides elFinder's paste command to:
 * 1. Allow locked files to be moved (clears locked flag temporarily)
 * 2. Update filecomments and lockedfiles DB tables after the move
 *    so comments and locks travel with moved files/directories.
 * @commandID paste
 * @nicename Paste
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 */
(function() {
    var origPaste = elFinder.prototype.commands.paste;
    
    elFinder.prototype.commands.paste = function() {
        // Run the original constructor to set up getstate, handlers, shortcuts, etc.
        origPaste.call(this);
        
        var fm = this.fm;
        var self = this;
        var origExec = this.exec;
        
        this.exec = function(select, cOpts) {
            var clipboard = fm.clipboard();
            var oldPaths = [];
            var unlocked = [];  // track files we temporarily unlock
            
            // ── Capture clipboard state and temporarily unlock locked files ──
            if (clipboard.length && clipboard[0].cut) {
                $.each(clipboard, function(i, f) {
                    oldPaths.push(decodeURIComponent(fm.url(f.hash)));
                    if (f.locked) {
                        unlocked.push(f);
                        f.locked = 0;  // temporarily clear so elFinder allows the move
                    }
                });
            }
            
            // ── Call the original exec ──
            var result = origExec.call(self, select, cOpts);
            
            // ── Restore locked state after paste completes (success or failure) ──
            if (unlocked.length) {
                var restoreLocks = function() {
                    $.each(unlocked, function(i, f) {
                        f.locked = 1;
                    });
                };
                if (result && result.promise) {
                    result.always(restoreLocks);
                } else {
                    restoreLocks();
                }
            }
            
            // ── On success, update DB with old→new path mappings ──
            if (result && result.promise && oldPaths.length) {
                result.done(function() {
                    var dst = select ? self.files(select)[0] : fm.cwd();
                    var dstUrl = decodeURIComponent(fm.url(dst.hash)).replace(/\/$/, '');
                    
                    var mappings = $.map(oldPaths, function(oldPath) {
                        return {
                            oldPath: oldPath,
                            newPath: dstUrl + '/' + oldPath.split('/').pop()
                        };
                    });
                    
                    if (mappings.length) {
                        $.post('libraries/elfinderLibs/endpoints/pasteOverrideEndpoint.php', {
                            mappings: mappings
                        }, null, 'json');
                    }
                });
            }
            
            return result;
        };
    };
})();
