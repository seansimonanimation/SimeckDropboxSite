/**
 * PreviewOpenOverride.js
 * 
 * Intercepts elFinder's open behavior to display files in the preview
 * floating island instead of opening a new window (which 404s).
 * Directories still navigate as normal.
 * 
 * Call overrideOpenCommand(fm) after the elFinder instance is ready.
 */

// ── Override the open command at script load time ──
// This must run BEFORE elFinder initializes, so the instance
// picks up the overridden command. Catches Enter key, toolbar
// "Open" button, and context menu "Open".
(function() {
    var origOpen = elFinder.prototype.commands.open;
    
    elFinder.prototype.commands.open = function() {
        origOpen.call(this);
        var self = this;
        var origExec = this.exec;
        
        this.exec = function(hashes, cOpts) {
            var fm = this.fm;
            var files = self.files(hashes);
            var thash = (typeof cOpts == 'object') ? cOpts.thash : false;
            
            // Directories navigate normally
            if (thash || (files.length === 1 && files[0].mime === 'directory')) {
                return origExec.call(self, hashes, cOpts);
            }
            
            // Filter out directories from multi-select
            var nonDirs = $.grep(files, function(f) { return f.mime !== 'directory'; });
            if (nonDirs.length === 0) {
                return origExec.call(self, hashes, cOpts);
            }
            
            // Open first file in floating island preview
            var file = nonDirs[0];
            var fileUrl = fm.url(file.hash);
            var displayUrl = getDisplayUrl(file.hash);
            var isImage = file.mime.indexOf('image') === 0;
            openPreviewIsland(fm, file, fileUrl, isImage, displayUrl);
            
            return $.Deferred().resolve(hashes);
        };
    };
})();

function overrideOpenCommand(fm) {
    if (!fm) return;
    
    // ── Double-click interception ──
    fm.bind('dblclick', function(e) {
        if (e.data && e.data.file) {
            var file = fm.file(e.data.file);
            if (file && file.mime && file.mime !== 'directory') {
                e.preventDefault();
                e.stopPropagation();
                var fileUrl = fm.url(file.hash);
                var displayUrl = getDisplayUrl(file.hash);
                var isImage = file.mime.indexOf('image') === 0;
                openPreviewIsland(fm, file, fileUrl, isImage, displayUrl);
            }
        }
    }, true);  // ← priorityFirst = true, runs BEFORE the open command's handler
}
