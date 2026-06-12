/**
 * @commandID seeComments
 * @nicename See Comments
 * @role client
 * @loc files, cwd
 * @order 2
 * @contextMenuDividers below
 */
//
elFinder.prototype.commands.seeComments = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'See Comments';
    };
    
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var fileUrl = fm.url(files[0].hash);

            $.get('libraries/elfinderLibs/endpoints/getCommentsIsland.php', {
                filepath: fileUrl
            }, function(html) {
                $('body').append(html);
            }, 'html').fail(function() {
                alert('Failed to load comments.');
            });
            
        } else {
            alert('You can only see comments for one file at a time, you silly goose!');
        }
        return $.Deferred().resolve();
    };
    
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};
