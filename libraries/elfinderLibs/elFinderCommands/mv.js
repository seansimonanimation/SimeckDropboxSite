/**
 * @commandID mv
 * @nicename Move To
 */
elFinder.prototype.commands.mv = function() {
    this.contextmenu = true;

        this.init = function() {
        this.title = 'Move to ➡️';
    };

    this.exec = function() {

    }

    this.getstate = function() {
        return this.fm.selectedFiles().length ? 0 : -1;
    };
}//