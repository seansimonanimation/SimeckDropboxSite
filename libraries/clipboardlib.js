/**
 * clipboardLib.js - Clipboard utilities and elFinder clipboard commands
 */

// ── Clipboard Copy with Fallback ──────────────────────────────────
function copyToClipboard(text, successMsg, fm) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            if (fm) fm.notify({ type: 'info', msg: successMsg || 'Copied to clipboard!' });
        }).catch(function() {
            prompt('Copy this text (Ctrl+C, then Enter):', text);
        });
    } else {
        prompt('Copy this text (Ctrl+C, then Enter):', text);
    }
}

// ── SimeckClipboardCommands namespace ─────────────────────────────
var SimeckClipboardCommands = {};

SimeckClipboardCommands.CopyFolderLink = function(fm, hash) {
    var scheme = window.location.protocol + '//';
    var host = window.location.host;
    var url = scheme + host + '/viewfolder.php?hash=' + encodeURIComponent(hash);
    copyToClipboard(url, 'Folder link copied!', fm);
};

SimeckClipboardCommands.CopyInternalPermalink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'internal', 'permalink');
    if (url) copyToClipboard(url, 'Internal permalink copied!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate link.' });
};

SimeckClipboardCommands.CopyThumbnailPermalink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'thumbnail', 'permalink');
    if (url) copyToClipboard(url, 'Thumbnail permalink copied!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate link.' });
};

SimeckClipboardCommands.CopyWatermarkedPermalink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'clientPreview', 'permalink');
    if (url) copyToClipboard(url, 'Watermarked permalink copied!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate link.' });
};

SimeckClipboardCommands.CopyDeliverablePermalink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'deliverable', 'permalink');
    if (url) copyToClipboard(url, 'Deliverable permalink copied!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate link.' });
};

SimeckClipboardCommands.CopyInternalShortlink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'internal', 'shortlink');
    if (url) copyToClipboard(url, 'Internal shortlink copied (expires in 14 days)!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate shortlink.' });
};

SimeckClipboardCommands.CopyWatermarkedShortlink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'clientPreview', 'shortlink');
    if (url) copyToClipboard(url, 'Watermarked shortlink copied (expires in 14 days)!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate shortlink.' });
};

SimeckClipboardCommands.CopyThumbnailShortlink = function(fm, hash) {
    var url = Simeck.MakeLink(hash, 'thumbnail', 'shortlink');
    if (url) copyToClipboard(url, 'Thumbnail shortlink copied (expires in 14 days)!', fm);
    else fm.notify({ type: 'error', msg: 'Failed to generate shortlink.' });
};
