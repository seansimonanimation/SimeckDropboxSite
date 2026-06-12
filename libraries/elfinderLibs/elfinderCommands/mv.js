/**
 * @commandID mvToPrj
 * @nicename Move To Project
 * @role artist
 * @loc files
 * @order 6
 * @contextmenuDividers below
 */
elFinder.prototype.commands.mvToPrj = function() {
    this.contextmenu = true;

        this.init = function() {
        this.title = 'Move to Project➡️';
    };

    this.exec = function() {

    }

    this.getstate = function() {
        return this.fm.selectedFiles().length ? 0 : -1;
    };
}//