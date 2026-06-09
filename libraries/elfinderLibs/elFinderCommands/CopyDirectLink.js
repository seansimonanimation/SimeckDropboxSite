/**
 * CopyDirectLink.js
 * 
 *  Hooks in to the site's download.php and provides a hashed link for download.
 *  We then copy this to the Clipboard for pasting into Discord or wherever else.
 *  //TODOs: Password support.
 *    
 * @commandID CopyDirectLink
 * @nicename Copy Download Link
 */

elFinder.prototype.commands.CopyDirectLink = function() {
    this.contextmenu = true;

        this.init = function() {
        this.title = 'Copy Download Link';
    };

    this.exec = function() {

    }

    this.getstate = function() {
        return this.fm.selectedFiles().length ? 0 : -1;
    };
}