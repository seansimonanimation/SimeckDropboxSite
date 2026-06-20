/**
 * @commandID sendToDiscord
 * @nicename Send to Discord
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 5
 * @contextMenuDividers below
 */
//
elFinder.prototype.commands.sendToDiscord = function() {
    this.contextmenu = true;
    this.init = function(){ this.title = 'Send to Discord'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles();
        if (files.length === 0) return $.Deferred().resolve();
        var fileData = files.map(function(f) { return { name: f.name, url: fm.url(f.hash) }; });
        $.post('libraries/elfinderLibs/endpoints/getDiscordIsland.php', { files: JSON.stringify(fileData), folderHash: fm.cwd().hash }, function(html) { $('body').append(html); }, 'html').fail(function() { fm.notify({ type: 'error', msg: 'Failed to load Discord send dialog.' }); });
        return $.Deferred().resolve();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 1 : 0; };
};
