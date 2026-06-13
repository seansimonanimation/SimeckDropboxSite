/**
 * PreviewOpenOverride.js
 * 
 * Intercepts elFinder's dblclick event to open files in the preview
 * floating island instead of opening a new window.
 * Directories still navigate as normal.
 * 
 * Call overrideOpenCommand(fm) after the elFinder instance is ready.
 */
function overrideOpenCommand(fm) {
    if (!fm) return;
    
    fm.bind('dblclick', function(e) {
        if (e.data && e.data.file) {
            var file = fm.file(e.data.file);
            if (file && file.mime && file.mime !== 'directory') {
                e.preventDefault();
                e.stopPropagation();
                var fileUrl = fm.url(file.hash);
                var isImage = file.mime.indexOf('image') === 0;
                openPreviewIsland(fm, file, fileUrl, isImage);
            }
        }
    }, true);  // ← priorityFirst = true, runs BEFORE the open command's handler

}
