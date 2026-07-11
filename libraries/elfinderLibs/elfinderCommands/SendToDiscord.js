/**
 * @commandID sendToDiscord
 * @nicename Send to Discord
 * @role artist
 * @availableToHigherRoles true
 * @loc files
 * @order 9
 * @contextMenuDividers below
 */
//
elFinder.prototype.commands.sendToDiscord = function() {
    this.contextmenu = true;
    this.init = function(){ this.title = 'Send to Discord'; };
    this.exec = function(hashes) {
        var fm = this.fm, files = fm.selectedFiles();
        if (files.length === 0) return $.Deferred().resolve();
        
        var fileData = files.map(function(f) { return { name: f.name, hash: f.hash }; });
        var adjustedHash = fm.cwd().hash;
        if (adjustedHash.startsWith('s1_')) {
            var path = decodeElfinderHash(adjustedHash);
            var session = window.simeckSession;
            var userName = session.lastname + ', ' + session.firstname;
            var reEncoded = encodeElfinderPath(userName + '/' + path);
            adjustedHash = 's2_' + reEncoded;
        }
        
        Helpers.postHtml('libraries/elfinderLibs/endpoints/getDiscordIsland.php', {
            files: JSON.stringify(fileData),
            folderHash: adjustedHash
        }).then(function(html) {
            $('body').append(html);
        }).catch(function(err) {
            fm.notify({ type: 'error', msg: 'Failed to load Discord send dialog.' });
        });
        
        return $.Deferred().resolve();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 1 : 0; };
};
