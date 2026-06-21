/**
 * CopyDeliverablePermalink.js
 *
 * Copies a permanent "deliverable" download link for the selected file.
 * Deliverable mode serves full resolution with no watermark.
 *
 * Available to clients unconditionally (when deliverable=1), and to
 * artists/admins only when the file is in a client project with a POC.
 * The server enforces the deliverable flag regardless.
 *
 * @commandID DeliverableLink
 * @nicename Copy Permalinks///Deliverable
 * @role client
 * @availableToHigherRoles true
 * @loc files
 * @order 9
 * @contextMenuDividers none
 */
//
elFinder.prototype.commands.DeliverableLink = function() {
    this.contextmenu = true;
    this.init = function() { this.title = 'Copy Deliverable Permalink'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles(), dfrd = $.Deferred();
        if (files.length === 0) { fm.notify({ type: 'error', msg: 'No files selected.' }); return dfrd.reject(); }
        if (files.length > 1) { fm.notify({ type: 'error', msg: 'Please select only one file.' }); return dfrd.reject(); }
        fm.notify({ type: 'info', msg: 'Generating deliverable permalink...', cnt: 1, progress: 0 });
        $.post('/libraries/elfinderLibs/endpoints/generateLinkEndpoint.php', { hash: files[0].hash, type: 'permalink', mode: 'deliverable' }, function(response) {
            fm.notify({ type: 'info', cnt: -1 });
            if (response.success && response.url) copyToClipboard(response.url, 'Deliverable permalink copied to clipboard!', fm);
            else fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
        }, 'json').fail(function() { fm.notify({ type: 'info', cnt: -1 }); fm.notify({ type: 'error', msg: 'Server request failed.' }); }).always(function() { dfrd.resolve(); });
        return dfrd.promise();
    };
    this.getstate = function() {
        var fm = this.fm, session = window.simeckSession, sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        if (!isDeliverableFile(sel[0].hash, fm)) return -1;
        if (session.tempRole === 'client' || session.tempRole === 'admin') return 0;
        if (!hasPoCRequirementForHash(sel[0].hash)) return -1;
        return 0;
    };
};
