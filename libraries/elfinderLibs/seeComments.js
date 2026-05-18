elFinder.prototype.commands.seecm = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'See Comments';
    };
    
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var fileUrl = files[0].url || files[0].path || files[0].hash;
            var dialogId = 'elfinder-seecm-dialog';
            
            $('#' + dialogId).remove();
            
            var dialog = $('<div id="' + dialogId + '">' +
                '<div class="seecm-loading">Loading comments...</div>' +
                '</div>').dialog({
                title: 'Comments for: ' + files[0].name,
                width: 500,
                height: 400,
                modal: true,
                close: function() { $(this).dialog('destroy').remove(); }
            });
            
            $.get('modules/admin/adminFileBrowser/comments.php', {
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
                        
                        $.post('modules/admin/adminFileBrowser/comments.php', {
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