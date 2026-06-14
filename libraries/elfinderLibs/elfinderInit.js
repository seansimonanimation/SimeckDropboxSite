$(function() {
    // Restore preview pane collapsed state
    if (localStorage.getItem('simeck_previewPaneCollapsed') === '1') {
        $('#preview-pane').addClass('collapsed');
        $('#preview-toggle').html('<');
        // Trigger resize after elFinder initializes
        setTimeout(function() {
            var instance = $('#elfinder').elfinder('instance');
            if (instance) instance.resize();
        }, 100);
    }
    //fm.bind('load', function() { refreshLockOverrides(fm); });
    resizeElfinder();
});


function populateLockCache(fm) {
    $.post('libraries/elfinderLibs/endpoints/GetLockedfilesInProjectEndpoint.php', {}, function(response) {
        if (!response.success || !response.lockedFiles) return;
        if (!fm.cache) fm.cache = {};
        fm.cache.lockedPaths = {};
        response.lockedFiles.forEach(function(lock) {
            var normalizedPath = normalizeSimeckFilePath(lock.filepath);
            fm.cache.lockedPaths[normalizedPath] = {
                assetlock: lock.assetlock,
                commentlock: lock.commentlock
            };
        });
        refreshLockOverrides(fm);
    }, 'json');
}

function normalizeSimeckFilePath(path) {
    if (!path) return '';
    path = path.replace(/\\/g, '/');
    path = path.replace(/\+/g, ' ');
    try {
        path = decodeURIComponent(path);
    } catch (e) {
        // leave as-is if decode fails
    }
    return path;
}

function getSimeckLockFilePath(fm, hash) {
    var relPath = fm.path(hash) || '';
    relPath = relPath.replace(/\\/g, '/').replace(/^\/+/, '');
    relPath = normalizeSimeckFilePath(relPath);
    return '/files/Projects/' + relPath;
}
function bindLockRefreshOnNavigate(fm) {
    // Refresh lock cache whenever elFinder finishes opening a directory.
    fm.bind('opendone', function() {
        setTimeout(function() {
            populateLockCache(fm);
        }, 50);
    });

    // Also refresh overlays after redraws, as a fallback for UI repaint cycles.
    fm.bind('redraw', function() {
        setTimeout(function() {
            refreshLockOverrides(fm);
        }, 50);
    });
}




function resizeElfinder() {
    var container = $('.file-browser-container');
    var h = container.length ? container.height() : $(window).height() - $('#elfinder').offset().top;
    if (h < 300) h = 300;
    $('#elfinder').height(h);
    var instance = $('#elfinder').elfinder('instance');
    if (instance) {
        instance.resize();
    }
}


function updatePreviewPane(fm) {
    var $content = $('#preview-pane .preview-content');
    var selected = fm.selectedFiles();
    
    if (selected.length !== 1) {
        $content.html('<p class="preview-placeholder">Select a file to preview</p>');
        return;
    }
    
    var f = selected[0];
    var fileUrl = fm.url(f.hash);
    var isImage = f.mime && f.mime.startsWith('image/');
    var isLocked = false;
    var commentLocked = false;
    var rawCommentLocked = false;

    // Check lock status from cache
    var decodedUrl = decodeURIComponent(fileUrl);
    var normalizedUrl = normalizeSimeckFilePath(fileUrl);
    if (fm.cache && fm.cache.lockedPaths && fm.cache.lockedPaths[normalizedUrl]) {
        var lockData = fm.cache.lockedPaths[normalizedUrl];
        isLocked = lockData.assetlock ? true : false;
        commentLocked = lockData.commentlock ? true : false;
        rawCommentLocked = commentLocked;
    }
    // Determine role for context-sensitive buttons
    var role = window.simeckSession.tempRole || '';
    // Admins and artists bypass comment locks (for form visibility, not badge display)
    if (role === 'admin' || role === 'artist') { commentLocked = false; }

    
    var html = '';
    
    // 1. Title
    html += '<h1 class="preview-title">File Preview</h1>';
    
    // 2. Context-sensitive buttons
    html += '<div class="preview-actions">';
    html += '  <button class="preview-action-btn" data-action="share-link">🔗 Share Link</button>';
    if (role !== 'client') {
        html += '  <button class="preview-action-btn" data-action="share-folder">📁 Share Folder</button>';
        html += '  <button class="preview-action-btn" data-action="send-discord">💬 Send to Discord</button>';
    }
    html += '</div>';
    
    // 3. Preview image/icon
    html += '<div class="preview-visual' + (isImage ? '' : ' preview-visual--icon') + '">';
    if (isImage) {
        html += '  <img src="' + fm.escape(fileUrl) + '" class="preview-image" data-hash="' + f.hash + '" alt="' + fm.escape(f.name) + '">';
    } else if (f.mime && f.mime.indexOf('video') === 0) {
        html += '  <video controls preload="metadata" style="width:100%;height:100%;object-fit:contain;" src="' + fm.escape(fileUrl) + '"></video>';
    } else {
        var iconClass = getElfinderIconClass(f.mime, f.name);
        html += '  <div class="preview-generic-icon ' + iconClass + '"></div>';
    }
    if (isLocked) {
        if (rawCommentLocked) {
            html += '  <span class="preview-lock-badge preview-lock-badge--locked">🔒</span>';
        } else {
            html += '  <span class="preview-lock-badge preview-lock-badge--open">🔓</span>';
        }
    }


    html += '</div>';
    
    // 4. Filename (centered)
    html += '<div class="preview-filename">' + fm.escape(f.name) + '</div>';
    
    // 5. Filesize (centered)
    var size = f.mime === 'directory' ? '—' : formatBytes(f.size);
    html += '<div class="preview-filesize">' + size + '</div>';
    
    // 6. Comments section
    html += '<div class="preview-comments-section">';
    html += '  <hr class="preview-divider">';
    html += '  <h4 class="preview-comments-heading">Comments</h4>';
    html += '  <div class="preview-comments-list" data-filepath="' + fm.escape(fileUrl) + '" data-commentlocked="' + (commentLocked ? '1' : '0') + '">';
    html += '    <p class="seecm-loading">Loading comments…</p>';
    html += '  </div>';
    html += '</div>';
    
    $content.html(html);
    
    // Load comments
    loadPreviewComments(fileUrl, commentLocked);
    
    // Wire up click on preview to open in floating island
    $('.preview-visual').on('click', function() {
        openPreviewIsland(fm, f, fileUrl, isImage);
    });
    
    // Wire up action buttons
    $('.preview-action-btn').on('click', function() {
        var action = $(this).data('action');
        switch (action) {
            case 'share-link':  copyDirectLink(fm, f); break;
            case 'share-folder': copyFolderLink(fm, f); break;
            case 'send-discord': sendToDiscord(fm, f); break;
        }
    });
}

function getElfinderIconClass(mime, name) {
    // Map MIME types to elFinder-like icon classes
    if (!mime || mime === 'directory') return 'elfinder-cwd-icon-directory';
    if (mime.startsWith('text/')) return 'elfinder-cwd-icon-text';
    if (mime.startsWith('video/')) return 'elfinder-cwd-icon-video';
    if (mime.startsWith('audio/')) return 'elfinder-cwd-icon-audio';
    if (mime === 'application/pdf') return 'elfinder-cwd-icon-pdf';
    if (mime === 'application/zip' || mime.indexOf('compress') !== -1 || mime.indexOf('archive') !== -1) return 'elfinder-cwd-icon-archive';
    if (mime.indexOf('javascript') !== -1 || mime.indexOf('json') !== -1 || mime.indexOf('xml') !== -1) return 'elfinder-cwd-icon-code';
    return 'elfinder-cwd-icon-default';
}

function loadPreviewComments(fileUrl, isCommentLocked) {
    var $list = $('.preview-comments-list');
    
    $.get('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
        action: 'fetch',
        file_url: fileUrl
    }, function(response) {
        if (!response.success) {
            $list.html('<p class="seecm-status-error">Failed to load comments.</p>');
            return;
        }
        
        var html = '<div class="seecm-comments-list">';
        if (response.comments.length === 0) {
            html += '<p class="seecm-status-empty">No comments yet.</p>';
        } else {
            $.each(response.comments, function(i, c) {
                html += '<div class="seecm-comment">';
                html += '  <div class="seecm-comment__header">';
                html += '    <span class="seecm-comment__author">' + $('<span>').text(c.owner).html() + '</span>';
                html += '    <span class="seecm-comment__time">' + $('<span>').text(c.comment_time).html() + '</span>';
                html += '  </div>';
                html += '  <p class="seecm-comment__body">' + c.comment_content + '</p>';
                html += '</div>';
            });
        }
        html += '</div>';
        
        // Add comment form (if not locked)
        if (isCommentLocked) {
            html += '<p class="seecm-locked-notice">Comments are locked on this file.</p>';
        } else {
            html += '<hr class="seecm-divider">';
            html += '<div class="seecm-add-form">';
            html += '  <textarea class="preview-comment-input" placeholder="Write a comment…"></textarea>';
            html += '  <button class="preview-comment-submit">Add Comment</button>';
            html += '</div>';
        }
        
        $list.html(html);
        
        // Wire up submit
        if (!isCommentLocked) {
            $list.find('.preview-comment-submit').on('click', function() {
                var $input = $list.find('.preview-comment-input');
                var content = $input.val().trim();
                if (!content) return;
                
                $.post('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
                    action: 'add',
                    file_url: fileUrl,
                    content: content
                }, function(addResponse) {
                    if (addResponse.success) {
                        $input.val('');
                        loadPreviewComments(fileUrl, isCommentLocked);
                    } else {
                        alert('Failed to add comment: ' + (addResponse.error || 'unknown error'));
                    }
                }, 'json').fail(function() {
                    alert('Failed to add comment.');
                });
            });
            
            $list.find('.preview-comment-input').on('keydown', function(e) {
                e.stopPropagation();
            });
        }
        
        // Wire up submit button
        $list.find('.preview-comment-submit').on('click', function() {
            var $input = $list.find('.preview-comment-input');
            var content = $input.val().trim();
            if (!content) return;
            
            $.post('libraries/elfinderLibs/endpoints/commentsEndpoint.php', {
                action: 'add',
                file_url: fileUrl,
                content: content
            }, function(addResponse) {
                if (addResponse.success) {
                    $input.val('');
                    loadPreviewComments(fileUrl, isCommentLocked);
                } else {
                    alert('Failed to add comment: ' + (addResponse.error || 'unknown error'));
                }
            }, 'json').fail(function() {
                alert('Failed to add comment.');
            });
        });
    }, 'json').fail(function() {
        $list.html('<p class="seecm-status-error">Failed to load comments.</p>');
    });
}

function copyDirectLink(fm, file) {
    // Reuse the CopyDirectLink command logic
    $.post('/libraries/elfinderLibs/endpoints/getElfinderDownloadLinksEndpoint.php', {
        hashes: [file.hash]
    }, function(response) {
        if (response.success && response.urls.length > 0) {
            var link = response.urls[0];
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(function() {
                    fm.notify({ type: 'info', msg: 'Download link copied to clipboard!' });
                }).catch(function() {
                    prompt('Copy this download link:', link);
                });
            } else {
                prompt('Copy this download link:', link);
            }
        } else {
            fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
        }
    }, 'json');
}

function copyFolderLink(fm, file) {
    // Copy link to parent folder
    var phash = file.phash || fm.cwd().hash;
    var baseUrl = window.location.protocol + '//' + window.location.host;
    
    // Adjust hash the same way CopyLinkToFolder does
    var adjustedHash = phash;
    if (phash.startsWith('s1_')) {
        var raw = phash.substring(3);
        var b64 = raw.replace(/-/g, '+').replace(/_/g, '/').replace(/\./g, '=');
        var path = atob(b64);
        var session = window.simeckSession;
        var userName = session.lastname + ', ' + session.firstname;
        var reEncoded = btoa(userName + '/' + path)
            .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.');
        adjustedHash = 's2_' + reEncoded;
    }
    
    var link = baseUrl + '/viewfolder.php?folderid=' + encodeURIComponent(adjustedHash);
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(function() {
            fm.notify({ type: 'info', msg: 'Folder link copied to clipboard!' });
        }).catch(function() {
            prompt('Copy this folder link:', link);
        });
    } else {
        prompt('Copy this folder link:', link);
    }
}

function sendToDiscord(fm, file) {
    var fileData = [{ name: file.name, url: fm.url(file.hash) }];
    
    $.post('libraries/elfinderLibs/endpoints/getDiscordIsland.php', {
        files: JSON.stringify(fileData),
        folderHash: fm.cwd().hash
    }, function(html) {
        $('body').append(html);
    }, 'html').fail(function() {
        fm.notify({ type: 'error', msg: 'Failed to load Discord dialog.' });
    });
}

function openPreviewIsland(fm, file, fileUrl, isImage) {
    // Build a floating island with a large preview
    var islandId = 'fi-preview-' + file.hash.replace(/[^a-zA-Z0-9_-]/g, '');
    
    // Remove existing preview island if any
    $('#' + islandId).remove();
    
    var contentHtml = '';
    if (isImage) {
        contentHtml = '<img src="' + fm.escape(fileUrl) + '" style="width:100%;height:100%;object-fit:contain;display:block;">';
    } else if (file.mime && file.mime.indexOf('video') === 0) {
        contentHtml = '<video controls autoplay style="width:100%;height:100%;object-fit:contain;background:#000;">';
        contentHtml += '  <source src="' + fm.escape(fileUrl) + '" type="' + fm.escape(file.mime) + '">';
        contentHtml += '  Your browser does not support the video tag.';
        contentHtml += '</video>';
    } else {
        var iconClass = getElfinderIconClass(file.mime, file.name);
        contentHtml = '<div style="text-align:center;padding:40px;">';
        contentHtml += '  <div class="' + iconClass + '" style="font-size:128px;width:128px;height:128px;margin:0 auto 20px;"></div>';
        contentHtml += '  <h2>' + fm.escape(file.name) + '</h2>';
        contentHtml += '  <p style="color:var(--color-text-muted);">' + (file.mime || 'Unknown type') + '</p>';
        contentHtml += '  <p style="color:var(--color-text-muted);">' + formatBytes(file.size) + '</p>';
        contentHtml += '</div>';
    }
    // detect ext and embed iframe preview (insert into existing openPreviewIsland flow)
        var name = file && file.name ? file.name : '';
        var ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'docx' || ext === 'doc') {
            var previewUrl = '/libraries/elfinderLibs/endpoints/previewDocx.php?url=' + fileUrl;
            contentHtml = '<iframe src="' + previewUrl + '" style="width:100%;height:100%;border:0;"></iframe>';
        }
    
    var islandHtml = '<div class="floating-island preview-island" id="' + islandId + '" style="width:90vw;height:90vh;max-width:1400px;max-height:900px;">';
    islandHtml += '  <div class="floating-island__header">';
    islandHtml += '    <h3 class="floating-island__title">' + fm.escape(file.name) + '</h3>';
    islandHtml += '    <button class="floating-island__close" onclick="this.closest(\'.floating-island\').remove()" aria-label="Close">✕</button>';
    islandHtml += '  </div>';
    islandHtml += '  <div class="floating-island__body" style="display:flex;align-items:center;justify-content:center;overflow:hidden;">';
    islandHtml +=      contentHtml;
    islandHtml += '  </div>';
    islandHtml += '  <div class="floating-island__resize-handle"></div>';
    islandHtml += '</div>';
    
    $('body').append(islandHtml);
    
    // Add drag behavior (reuse the same pattern from floatingIslandLib.php)
    var island = document.getElementById(islandId);
    if (island) {
        var header = island.querySelector('.floating-island__header');
        if (header) {
            var offsetX, offsetY, dragging = false;
            header.addEventListener('mousedown', function(e) {
                if (e.target.closest('.floating-island__close')) return;
                dragging = true;
                var rect = island.getBoundingClientRect();
                offsetX = e.clientX - rect.left;
                offsetY = e.clientY - rect.top;
                island.style.cursor = 'grabbing';
                island.style.transition = 'none';
                e.preventDefault();
            });
            document.addEventListener('mousemove', function(e) {
                if (!dragging) return;
                island.style.left = (e.clientX - offsetX) + 'px';
                island.style.top = (e.clientY - offsetY) + 'px';
                island.style.transform = 'none';
            });
            document.addEventListener('mouseup', function() {
                if (!dragging) return;
                dragging = false;
                island.style.cursor = '';
                island.style.transition = '';
            });
        }
    }
}


function formatBytes(bytes) {
    if (isNaN(bytes) || bytes === 0) return '0 B';
    var units = ['B', 'KB', 'MB', 'GB', 'TB'];
    var i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
}
function togglePreviewPane() {
    var $pane = $('#preview-pane');
    var $btn = $('#preview-toggle');
    var $container = $('.file-browser-container');
    
    $pane.toggleClass('collapsed');
    
    var isCollapsed = $pane.hasClass('collapsed');
    $btn.html(isCollapsed ? '<' : '>');
    
    // NEW: Save state to localStorage
    localStorage.setItem('simeck_previewPaneCollapsed', isCollapsed ? '1' : '0');
    
    // Recalculate elFinder layout
    var instance = $('#elfinder').elfinder('instance');
    if (instance) {
        instance.resize();
    }
}

function refreshLockOverrides(fm) {
    if (!fm.cache || !fm.cache.lockedPaths) return;

    $('.elfinder-cwd-file').each(function() {
        var hash = this.id;
        if (!hash) return;

        var $file = $(this);
        var $icon = $file.find('.elfinder-cwd-icon');
        if (!$icon.length) return;

        // Remove any existing overlay
        $icon.find('.simeck-lock-overlay').remove();

        // Decode the elFinder hash to get the file's relative path
        var sepIdx = hash.indexOf('_');
        if (sepIdx === -1) return;
        var encoded = hash.substring(sepIdx + 1);
        var b64 = encoded.replace(/-/g, '+').replace(/_/g, '/').replace(/\./g, '=');
        try {
            var relPath = atob(b64);
        } catch(e) { return; }

        // Match against cache: cache keys are full paths like
        // "/files/Projects/clientProjects/C01/file.jpg"
        // Decoded relPath is "clientProjects/C01/file.jpg"
        var normalizedRelPath = normalizeSimeckFilePath(relPath);

        var lockData = null;
        for (var cachedPath in fm.cache.lockedPaths) {
            var normalizedCachedPath = normalizeSimeckFilePath(cachedPath);
            if (normalizedCachedPath.endsWith('/' + normalizedRelPath) || normalizedCachedPath === normalizedRelPath) {
                lockData = fm.cache.lockedPaths[cachedPath];
                break;
            }
        }

        if (!lockData) return;  // No lock = no overlay

        // Determine lock icon by state:
        //   Fully locked  (assetlock=1, commentlock=1) → 🔒
        //   Lock override (assetlock=1, commentlock=0) → 🔓
        var icon = '🔒';
        if (lockData.assetlock && !lockData.commentlock) {
            icon = '🔓';
        }

        $icon.append('<span class="simeck-lock-overlay">' + icon + '</span>');
    });
}






