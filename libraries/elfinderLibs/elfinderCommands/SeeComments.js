/**
 * @commandID seeComments
 * @nicename See Comments
 * @role client
 * @availableToHigherRoles true
 * @loc files, cwd
 * @order 1
 * @contextMenuDividers below
 */
//
elFinder.prototype.commands.seeComments = function() {
    this.contextmenu = true;
    this.init = function(){ this.title = 'See Comments'; };
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var fileUrl = fm.url(files[0].hash);
            var endpoint = 'libraries/elfinderLibs/endpoints/getCommentsIsland.php?filepath=' + encodeURIComponent(fileUrl);
            Helpers.spawnIsland(endpoint, 'Comments');
        } else {
            Helpers.alertIsland('Hold on', 'You can only see comments for one file at a time, you silly goose!', 'warning');
        }
        return $.Deferred().resolve();
    };
    this.getstate = function() { return this.fm.selectedFiles().length ? 1 : 0; };
};
