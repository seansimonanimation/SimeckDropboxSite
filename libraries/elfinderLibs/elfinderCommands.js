// ── Send to Monday Chat command ──────────────────────────────
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

// ── Send to Thursday Chat command ────────────────────────────
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

// ── See Comments command ─────────────────────────────────────
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
                    dialogClass: 'elfinder-seecm-dialog-wrapper',
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
                    var html = '<div class="seecm-comments-list">';
                    if (response.comments.length === 0) {
                        html += '<p class="seecm-status-empty">No comments yet.</p>';
                    } else {
                        $.each(response.comments, function(i, comment) {
                            html += '<div class="seecm-comment">' +
                                '<div class="seecm-comment__header">' +
                                '<span class="seecm-comment__author">' + fm.escape(comment.owner) + '</span> ' +
                                '<span class="seecm-comment__time">' + comment.comment_time + '</span></div>' +
                                '<p class="seecm-comment__body">' + fm.escape(comment.comment_content) + '</p>' +
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

elFinder.prototype.i18.en.cmdseecm = 'See Comments';

// ── Lock/Unlock command ───────────────────────────────────────
elFinder.prototype.commands.togglelock = function() {
    this.contextmenu = true;

    this.init = function(){
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var url = fm.url(files[0].hash);
            var hash = files[0].hash;
            var $node = fm.getFile(hash);
            var filepath = url;
            this.title = fm.cache?.['lockedPaths']?.[filepath] 
                ? 'Unlock File' : 'Lock File';
        } else {
            this.title = 'Lock File';
        }
    };

    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length !== 1) {
            fm.error('You can only lock/unlock one file at a time.');
            return $.Deferred().resolve();
        }
        var fileUrl = fm.url(files[0].hash);
        if (fileUrl.indexOf('/files/') !== 0) {
            fm.error('Can only lock/unlock files under /files/');
            return $.Deferred().resolve();
        }

        var isLocked = fm.cache && fm.cache.lockedPaths && fm.cache.lockedPaths[fileUrl];
        var action = isLocked ? 'unlock' : 'lock';

        $.post('libraries/elfinderLibs/lockedFilesEndpoint.php', {
            action: action,
            filepath: fileUrl
        }, function(response) {
            if (response.success) {
                fm.exec('reload');
            } else {
                fm.error(response.error || 'Failed to ' + action + ' file.');
            }
        }, 'json').fail(function() {
            fm.error('Server error while ' + action + 'ing file.');
        });

        return $.Deferred().resolve();
    };

    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var role = fm.options.role || '';
        if (role !== 'admin' && role !== 'artist') return -1;
        return 0;
    };
};

elFinder.prototype.i18.en.cmdtogglelock = 'Lock / Unlock File';

// ── Client Lock Override command ──────────────────────────────
elFinder.prototype.commands.clientlockoverride = function() {
    this.contextmenu = true;

    this.init = function(){
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length === 1) {
            var fileUrl = fm.url(files[0].hash);
            var lockInfo = fm.cache?.lockedPaths?.[fileUrl];
            if (lockInfo && lockInfo.commentlock) {
                var overrides = fm.cache?.clientOverrides ?? 0;
                this.title = 'Lock Override (' + overrides + ' remaining)';
            } else {
                this.title = 'Lock Override';
            }
        } else {
            this.title = 'Lock Override';
        }
    };

    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        if (files.length !== 1) {
            fm.error('You can only override one file at a time.');
            return $.Deferred().resolve();
        }
        var fileUrl = fm.url(files[0].hash);
        
        var overrides = fm.cache?.clientOverrides ?? 0;
        if (!confirm('Use one lock override (' + overrides + ' remaining) to unlock comments on "' + files[0].name + '"?')) {
            return $.Deferred().resolve();
        }

        $.post('libraries/elfinderLibs/lockedFilesEndpoint.php', {
            action: 'override',
            filepath: fileUrl
        }, function(response) {
            if (response.success) {
                if (fm.cache?.lockedPaths?.[fileUrl]) {
                    fm.cache.lockedPaths[fileUrl].commentlock = 0;
                }
                if (typeof response.remaining_overrides !== 'undefined') {
                    fm.cache.clientOverrides = response.remaining_overrides;
                }
                fm.exec('reload');
            } else {
                fm.error(response.error || 'Override failed.');
            }
        }, 'json').fail(function() {
            fm.error('Server error while performing override.');
        });

        return $.Deferred().resolve();
    };

    this.getstate = function() {
        var fm = this.fm;
        var sel = fm.selectedFiles();
        if (sel.length !== 1) return -1;
        var role = fm.options.role || '';
        if (role !== 'client') return -1;
        
        var url = fm.url(sel[0].hash);
        var lockInfo = fm.cache?.lockedPaths?.[url];
        if (!lockInfo) return -1;
        if (!lockInfo.commentlock) return -1;
        return 0;
    };
};

elFinder.prototype.i18.en.cmdclientlockoverride = 'Lock Override';
