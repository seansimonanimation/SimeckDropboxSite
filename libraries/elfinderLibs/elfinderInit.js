
$(function() {
    function resizeElfinder() {
        var winH = $(window).height();
        var offset = $('#elfinder').offset().top;
        var h = winH - offset - 15; // 15px breathing room
        if (h < 300) h = 300;
        $('#elfinder').height(h);
        
        var instance = $('#elfinder').elfinder('instance');
        if (instance) {
            instance.resize();
        }
    }


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


elFinder.prototype.commands.seecm = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'See Comments';
    };
    
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var fileUrl = fm.url(files[0].hash);
            var dialogId = 'elfinder-seecm-dialog';
            
            $('#' + dialogId).remove();
            
            var dialog = $('<div id="' + dialogId + '">' +
                '<div class="seecm-loading">Loading comments...</div>' +
                '</div>').dialog({
                    title: 'Comments for: ' + files[0].name,
                    width: 700,
                    height: 500,
                    modal: true,
                    appendTo: '#elfinder',
                    close: function() { $(this).dialog('destroy').remove(); }
                });
            
            $.get('libraries/elfinderLibs/commentsEndpoint.php', {
                action: 'fetch',
                file_url: fileUrl
            }, function(response) {
                if (response.success) {
                    var html = '<div class="seecm-comments-list" style="max-height: 250px; overflow-y: auto;">';
                    if (response.comments.length === 0) {
                        html += '<p style="color: #888; text-align: center;">No comments yet.</p>';
                    } else {
                        $.each(response.comments, function(i, comment) {
                            html += '<div style="border-bottom: 1px solid #eee; padding: 8px 0;">' +
                                '<strong>' + fm.escape(comment.owner) + '</strong> ' +
                                '<span style="color: #999; font-size: 0.85em;">' + comment.comment_time + '</span>' +
                                '<p style="margin: 4px 0 0 0;">' + fm.escape(comment.comment_content) + '</p>' +
                                '</div>';
                        });
                    }
                    html += '</div>' +
                        '<hr>' +
                        '<div class="seecm-add-form" style="margin-top: 8px;">' +
                        '<textarea id="seecm-new-comment" style="width: 100%; height: 60px; box-sizing: border-box;" placeholder="Write a comment..."></textarea>' +
                        '<button id="seecm-submit" style="margin-top: 4px;">Add Comment</button>' +
                        '</div>';
                    
                    dialog.html(html);
                    $('#seecm-submit').on('click', function() {
                        var content = $('#seecm-new-comment').val().trim();
                        if (!content) return;
                        
                        $.post('libraries/elfinderLibs/commentsEndpoint.php', {
                            action: 'add',
                            file_url: fileUrl,
                            content: content
                        }, function(addResponse) {
                            if (addResponse.success) {
                                fm.exec('seecm');
                            } else {
                                alert('Failed to add comment: ' + (addResponse.error || 'unknown error'));
                            }
                        }, 'json').fail(function() {
                            alert('Failed to add comment due to a server error.');
                        });
                    });

                    // Prevent elFinder hotkeys from firing when typing in the comment box
                    $('#seecm-new-comment').on('keydown', function(event) {
                        event.stopPropagation();

                    });
                } else {
                    dialog.html('<p style="color: red;">Failed to load comments: ' + (response.error || 'unknown error') + '</p>');
                }
            }, 'json').fail(function() {
                dialog.html('<p style="color: red;">Failed to load comments due to a server error.</p>');
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

elFinder.prototype.i18.en.cmdseecm = 'See Comments';
    $('#elfinder').elfinder({
        cssAutoLoad: false,
        baseUrl: 'libraries/elfinder/',
        url: 'modules/admin/adminFileBrowser/adminConnector.php',
        height: $(window).height() - $('#elfinder').offset().top - 15,
    });
    
    $(window).on('resize', resizeElfinder);
});