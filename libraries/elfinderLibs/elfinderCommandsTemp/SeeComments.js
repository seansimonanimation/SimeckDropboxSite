/**
 * @commandID seeComments
 * @nicename See Comments
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
            var dialogId = 'elfinder-seecm-dialog';
            
            $('#' + dialogId).remove();
            
            var dialog = $('<div id="' + dialogId + '">' +
                '<div class="seecm-loading">Loading comments...</div>' +
                '</div>').dialog({
                    title: 'Comments for: ' + files[0].name,
                    dialogClass: 'elfinder-seecm-dialog-wrapper',
                    width: 700,
                    height: 500,
                    modal: true,
                    appendTo: '#elfinder',
                    close: function() { $(this).dialog('destroy').remove(); }
                });
            
            $.get('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
                action: 'fetch',
                file_url: fileUrl
            }, function(response) {
                if (response.success) {
                    var html = '<div class="seecm-comments-list">';
                    if (response.comments.length === 0) {
                        html += '<p class="seecm-status-empty">No comments yet.</p>';
                    } else {
                        $.each(response.comments, function(i, comment) {
                            html += '<div class="seecm-comment">' +
                                '<div class="seecm-comment__header">' +
                                '<span class="seecm-comment__author">' + fm.escape(comment.owner) + '</span> ' +
                                '<span class="seecm-comment__time">' + comment.comment_time + '</span></div>' +
                                '<p class="seecm-comment__body">' + comment.comment_content + '</p>' +
                                '</div>';
                        });
                    }

                    // Determine if comment input should be disabled
                    var showCommentForm = true;
                    var role = fm.options.role || '';
                    if (role === 'client') {
                        var lockInfo = fm.cache?.lockedPaths?.[fileUrl];
                        if (lockInfo && lockInfo.commentlock) {
                            showCommentForm = false;
                        }
                    }

                    // Build form HTML
                    var formHtml = '';
                    if (showCommentForm) {
                        formHtml = '<hr class="seecm-divider">' +
                            '<div class="seecm-add-form" style="margin-top: 8px;">' +
                            '<textarea id="seecm-new-comment" placeholder="Write a comment..."></textarea>' +
                            '<button id="seecm-submit">Add Comment</button>' +
                            '</div>';
                    } else {
                        formHtml = '<hr class="seecm-divider">' +
                            '<p class="seecm-locked-notice" style="color: #888; font-style: italic; margin-top: 8px;">' +
                            'Comments are locked for this file. Use "Lock Override" from the context menu to unlock comments.' +
                            '</p>';
                    }

                    html += '</div>' + formHtml;
                    
                    dialog.html(html);
                    $('#seecm-submit').on('click', function() {
                        var content = $('#seecm-new-comment').val().trim();
                        if (!content) return;
                        
                        $.post('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
                            action: 'add',
                            file_url: fileUrl,
                            content: content
                        }, function(addResponse) {
                            if (addResponse.success) {
                                fm.exec('seeComments');
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
                    dialog.html('<p class="seecm-status-error">Failed to load comments: ' + (response.error || 'unknown error') + '</p>');
                }
            }, 'json').fail(function() {
                dialog.html('<p class="seecm-status-error">Failed to load comments due to a server error.</p>');
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