 /**
 * CopyDirectLink.js
 * 
 *  Hooks in to the site's download.php and provides a hashed link for download.
 *  We then copy this to the Clipboard for pasting into Discord or wherever else.
 *  //TODOs: Password support.
 *    
 * @commandID CopyLinkToFolder
 * @nicename Copy link to this folder
 */
elFinder.prototype.commands.CopyLinkToFolder = function() {
    this.contextmenu = true;

        this.init = function() {
        this.title = 'Copy Link to This Folder';
    };

    this.exec = function() {

    }

    this.getstate = function() {
        return this.fm.selectedFiles().length ? 0 : -1;
    };
}