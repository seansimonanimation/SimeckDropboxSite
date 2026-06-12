/**
 * @commandID sendToDiscord
 * @nicename Send to Discord
 * @role artist
 * @loc files
 * @order 3
 * @contextMenuDividers below
 */
//
elFinder.prototype.commands.sendToDiscord = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send to Discord';
    };
    
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 0) {
            return $.Deferred().resolve();
        }

        var fileData = files.map(function(f) {
            return { name: f.name, url: fm.url(f.hash) };
        });

        $.post('libraries/elfinderLibs/endpoints/getDiscordIsland.php', {
            files: JSON.stringify(fileData)
        }, function(html) {
            $('body').append(html);
        }, 'html').fail(function() {
            fm.notify({ type: 'error', msg: 'Failed to load Discord send dialog.' });
        });

        return $.Deferred().resolve();
    };
    
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};
