/**
 * @commandID sendToThursdayChat
 * @nicename Send to Thursday Chat
 */
elFinder.prototype.commands.sendToThursdayChat = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send to Thursday Chat';
    };
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        alert('If this was implemented, it would have sent ' + files.length + ' file(s) To Thursday Chat!');
        return $.Deferred().resolve();
    };
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};
