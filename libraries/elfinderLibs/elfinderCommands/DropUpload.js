/**
 * DropUpload.js
 *
 * Overrides elFinder's upload command so drag-and-drop uploads follow the
 * identical code path as the open-button (file picker) upload:
 *
 *   dataTransfer.files  ->  fm.upload()  ->  transport.upload()  ->  AJAX cmd=upload
 *
 * Instead of routing drops through dropUpload() -- which parses dataTransfer
 * with browser-specific quirks (types, items[0].kind, getAsEntry, getAsFile,
 * getData('text/html'), etc.) -- drops now extract dataTransfer.files directly
 * and call fm.upload(), exactly what the open button's <input type="file">
 * change handler reaches.
 *
 * Folder drag-and-drop is preserved: if dataTransfer.files is empty, we fall
 * back to the original dropUpload() which handles folders via the entry API.
 *
 * The upload dialog is still created by the original exec() (required for the
 * open-button path), but the dropbox drop handler inside it is replaced.
 *
 * @commandID upload
 * @nicename Upload
 * @role client
 * @availableToHigherRoles true
 * @loc toolbar, navbar, cwd, files
 * @order 50
 * @contextMenuDividers none
 */

(function() {
    var origUpload = elFinder.prototype.commands.upload;

    elFinder.prototype.commands.upload = function() {
        // Run the original constructor to set up getstate, init, shortcuts, etc.
        origUpload.call(this);

        var fm = this.fm;
        var self = this;
        var origExec = this.exec;

        this.exec = function(data) {
            // Let the original exec create the dialog (required for open-button path)
            var result = origExec.call(self, data);

            // Once the dialog exists in the DOM, replace its dropbox drop handler
            // so drag-and-drop uses dataTransfer.files -> fm.upload() directly,
            // matching the open-button code path.
            setTimeout(function() {
                var dropbox = $('.elfinder-upload-dropbox').first();
                if (dropbox.length) {
                    dropbox.off('drop').on('drop', function(e) {
                        e.stopPropagation();
                        e.preventDefault();

                        // Close the upload dialog (same as open button path)
                        var dialog = dropbox.closest('.ui-dialog');
                        if (dialog.length && dialog.elfinderdialog) {
                            dialog.elfinderdialog('close');
                        }

                        // Extract files from dataTransfer -- the same FileList the open
                        // button's <input type="file"> produces.
                        var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;

                        if (files && files.length) {
                            // Compute target directory hash (same logic as getTargets())
                            var sel = fm.selected();
                            var targetHash = (sel.length === 1 && fm.file(sel[0]).mime === 'directory')
                                ? sel[0]
                                : fm.cwd().hash;

                            // Same call the open button eventually reaches: fm.upload()
                            fm.upload({
                                files: files,
                                type: 'files',
                                target: targetHash
                            })
                            .done(function(data) {
                                result.resolve(data);

                                // Scroll to and select newly added files
                                if (data && data.added && data.added[0]) {
                                    var newItem = fm.findCwdNodes(data.added);
                                    if (newItem.length) {
                                        newItem.trigger('scrolltoview');
                                    }
                                    fm.trigger('selectfiles', {
                                        files: $.map(data.added, function(f) { return f.hash; })
                                    });
                                }
                                fm.toast({
                                    msg: fm.i18n(['complete', fm.i18n('cmdupload')])
                                });
                            })
                            .fail(function(error) {
                                result.reject(error);
                                fm.error(error);
                            })
                            .progress(function() {
                                result.notifyWith(this, arguments);
                            });
                        } else {
                            // dataTransfer.files empty -- fall back to original dropUpload()
                            // which handles folders via the entry API (webkitGetAsEntry, etc.)
                            if (typeof dropUpload === 'function') {
                                dropUpload(e.originalEvent);
                            } else {
                                result.reject(['errUploadNoFiles']);
                                fm.error(['errUploadNoFiles']);
                            }
                        }
                    });
                }
            }, 0);

            return result;
        };
    };
})();
