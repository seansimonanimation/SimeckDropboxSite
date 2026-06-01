/**
 * @commandID sendToMondayChat
 * @nicename Send to Monday Chat
 */
elFinder.prototype.commands.sendToMondayChat = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send to Monday Chat';
    };
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        alert('If this was implemented, it would have sent ' + files.length + ' file(s) To Monday Chat!');
        return $.Deferred().resolve();
    };
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};
