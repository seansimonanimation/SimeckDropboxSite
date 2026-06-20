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
    // Detect text/code file for inline snippet
    var isTextFile = false;
    if (f.mime) {
        var textMimePrefixes = ['text/', 'application/json', 'application/xml',
            'application/x-yaml', 'application/x-sh'];
        for (var p = 0; p < textMimePrefixes.length; p++) {
            if (f.mime.indexOf(textMimePrefixes[p]) === 0) {
                isTextFile = true;
                break;
            }
        }
    }
    if (!isTextFile && f.name) {
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        var codeExtensions = ['txt', 'md', 'json', 'xml', 'html', 'htm', 'css', 'js', 'ts',
            'jsx', 'tsx', 'php', 'py', 'rb', 'java', 'c', 'cpp', 'h', 'hpp', 'cs', 'go', 'rs',
            'swift', 'kt', 'sql', 'sh', 'bash', 'zsh', 'bat', 'cmd', 'ps1', 'yaml', 'yml',
            'ini', 'cfg', 'conf', 'log', 'gitignore', 'dockerfile', 'makefile', 'gradle',
            'sass', 'scss', 'less', 'vue', 'svelte', 'env', 'toml', 'r', 'pl', 'lua', 'scala',
            'coffee', 'mjs', 'cjs', 'mts', 'cts', 'tex', 'latex', 'rtf', 'diff', 'patch',
            'glsl', 'frag', 'vert', 'hlsl', 'cmake', 'properties', 'desktop', 'service', 'prefab', 'unity', 'cs'];
        if (codeExtensions.indexOf(ext) !== -1) {
            isTextFile = true;
        }
    }


    
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
        html += '  <img src="' + fm.escape(fileUrl) + '" class="preview-image" data-hash="' + f.hash + '" alt="' + fm.escape(f.name) + '" data-needs-watermark="1">';
    } else if (f.mime && f.mime.indexOf('video') === 0) {
        html += '  <video controls preload="metadata" style="width:100%;height:100%;object-fit:contain;" src="' + fm.escape(fileUrl) + '"></video>';
    } else if (isTextFile) {
        // Show inline text snippet in the side pane
        var snippetUrl = '/libraries/elfinderLibs/endpoints/previewText.php?url=' + encodeURIComponent(fileUrl) + '&lines=5&page=1';
        html += '  <div class="preview-text-snippet" id="preview-snippet-' + f.hash.replace(/[^a-zA-Z0-9_-]/g, '') + '">';
        html += '    <p class="seecm-loading">Loading preview…</p>';
        html += '  </div>';

        // Load the snippet
        var snippetContainerId = 'preview-snippet-' + f.hash.replace(/[^a-zA-Z0-9_-]/g, '');
        (function(containerId, snippetUrl) {
            $.get(snippetUrl, function(html) {
                // Extract just the <pre><code> block from the response
                var match = html.match(/<pre[^>]*>([\s\S]*?)<\/pre>/i);
                if (match) {
                    var snippet = '<pre>' + match[1] + '</pre>';
                    $('#' + containerId).html(snippet);
                } else {
                    $('#' + containerId).html('<p class="seecm-status-empty">No preview available.</p>');
                }
            }).fail(function() {
                $('#' + containerId).html('<p class="seecm-status-error">Failed to load preview.</p>');
            });
        })(snippetContainerId, snippetUrl);

    
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
    // Apply watermarked URL for clients
    if (isImage) {
        applyWatermarkedUrl(fm, f.hash, '.preview-image[data-needs-watermark="1"]');
    }

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
    });
}

function copyDirectLink(fm, file) {
    // Reuse the CopyDirectLink command logic
    $.post('/libraries/elfinderLibs/endpoints/getElfinderDownloadLinksEndpoint.php', {
        hashes: [file.hash]
    }, function(response) {
        if (response.success && response.urls.length > 0) {
            var link = response.urls[0];
            copyToClipboard(link, 'Download link copied to clipboard!', fm);
        } else {
            fm.notify({ type: 'error', msg: response.error || 'Failed to generate link.' });
        }
    }, 'json');
}
/**
 * Replace an image's src with a watermarked download URL for clients.
 * Non-clients are unaffected.
 */
function applyWatermarkedUrl(fm, hash, imgSelector, callback) {
    var role = window.simeckSession.tempRole || '';
    if (role !== 'client') {
        if (callback) callback();
        return;
    }
    $.post('/libraries/elfinderLibs/endpoints/getElfinderDownloadLinksEndpoint.php', {
        hashes: [hash]
    }, function(response) {
        if (response.success && response.urls.length > 0) {
            $(imgSelector).attr('src', response.urls[0]);
        }
        if (callback) callback();
    }, 'json').fail(function() {
        if (callback) callback();
    });
}

function copyFolderLink(fm, file) {
    // Copy link to parent folder
    var phash = file.phash || fm.cwd().hash;
    var baseUrl = window.location.protocol + '//' + window.location.host;
    
    // Adjust hash the same way CopyLinkToFolder does
    var adjustedHash = phash;
        if (phash.startsWith('s1_')) {
        var path = decodeElfinderHash(phash);
        var session = window.simeckSession;
        var userName = session.lastname + ', ' + session.firstname;
        var reEncoded = encodeElfinderPath(userName + '/' + path);
        adjustedHash = 's2_' + reEncoded;
    }
    
    var link = baseUrl + '/viewfolder.php?folderid=' + encodeURIComponent(adjustedHash);
    
    copyToClipboard(link, 'Folder link copied to clipboard!', fm);

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
    
        // ─── 3D Model Detection ──────────────────────────────────────────
    var ext = (file.name.split('.').pop() || '').toLowerCase();
    var is3DModel = ['obj', 'fbx', 'blend', 'mb'].indexOf(ext) !== -1;

    if (is3DModel) {
        // Build a container div that the 3D viewer will use, plus a loading placeholder
        var modelContainerId = 'model-' + file.hash.replace(/[^a-zA-Z0-9_-]/g, '');
        contentHtml = '<div id="' + modelContainerId + '" style="width:100%;height:100%;position:relative;background:#1a1a1a;overflow:hidden;"></div>';
    } else if (isImage) {
        var islandImgId = 'pi-img-' + file.hash.replace(/[^a-zA-Z0-9_-]/g, '');
        contentHtml = '<img id="' + islandImgId + '" src="' + fm.escape(fileUrl) + '" style="width:100%;height:100%;object-fit:contain;display:block;" data-needs-watermark="1">';
    } else if (file.mime && file.mime.indexOf('video') === 0) {
        contentHtml = '<video controls autoplay style="width:100%;height:100%;object-fit:contain;background:#000;">';
        contentHtml += '  <source src="' + fm.escape(fileUrl) + '" type="' + fm.escape(file.mime) + '">';
        contentHtml += '  Your browser does not support the video tag.';
        contentHtml += '</video>';
    } else {
        // detect ext and embed iframe preview (docs, spreadsheets, text/code)
        var name = file && file.name ? file.name : '';
        var ext2 = (name.split('.').pop() || '').toLowerCase();

        // ─── Text / Code file detection ────────────────────────────
        var textMimePrefixes = ['text/', 'application/json', 'application/xml',
            'application/x-yaml', 'application/x-sh'];

        var codeExtensions = ['txt', 'md', 'json', 'xml', 'html', 'htm', 'css', 'js', 'ts',
            'jsx', 'tsx', 'php', 'py', 'rb', 'java', 'c', 'cpp', 'h', 'hpp', 'cs', 'go', 'rs',
            'swift', 'kt', 'sql', 'sh', 'bash', 'zsh', 'bat', 'cmd', 'ps1', 'yaml', 'yml',
            'ini', 'cfg', 'conf', 'log', 'gitignore', 'dockerfile', 'makefile', 'gradle',
            'sass', 'scss', 'less', 'vue', 'svelte', 'env', 'toml', 'r', 'pl', 'lua', 'scala',
            'coffee', 'mjs', 'cjs', 'mts', 'cts', 'tex', 'latex', 'rtf', 'diff', 'patch',
            'glsl', 'frag', 'vert', 'hlsl', 'cmake', 'makefile', 'yml', 'yaml', 'lock',
            'properties', 'desktop', 'service', 'svg'];

        var isTextFile = false;
        if (file.mime) {
            for (var p = 0; p < textMimePrefixes.length; p++) {
                if (file.mime.indexOf(textMimePrefixes[p]) === 0) {
                    isTextFile = true;
                    break;
                }
            }
        }
        if (!isTextFile && codeExtensions.indexOf(ext2) !== -1) {
            isTextFile = true;
        }
        // rtf should show as text, not generic icon
        if (ext2 === 'rtf') {
            isTextFile = true;
        }

        if (isTextFile) {
            var previewUrl = '/libraries/elfinderLibs/endpoints/previewText.php?url=' + encodeURIComponent(fileUrl);
            contentHtml = '<iframe src="' + previewUrl + '" style="width:100%;height:100%;border:0;"></iframe>';

        } else if (ext2 === 'docx' || ext2 === 'doc') {
            var previewUrl = '/libraries/elfinderLibs/endpoints/previewDocx.php?url=' + fileUrl;
            contentHtml = '<iframe src="' + previewUrl + '" style="width:100%;height:100%;border:0;"></iframe>';
        } else if (ext2 === 'xlsx' || ext2 === 'xls' || ext2 === 'csv' || ext2 === 'ods') {
            var previewUrl = '/libraries/elfinderLibs/endpoints/previewXlsx.php?url=' + fileUrl;
            contentHtml = '<iframe src="' + previewUrl + '" style="width:100%;height:100%;border:0;"></iframe>';
        } else if (ext2 === 'pptx' || ext2 === 'ppt' || ext2 === 'odp') {
            var previewUrl = '/libraries/elfinderLibs/endpoints/previewPptx.php?url=' + fileUrl;
            contentHtml = '<iframe src="' + previewUrl + '" style="width:100%;height:100%;border:0;"></iframe>';
        } else if (ext2 === 'pdf') {
            var previewUrl = '/libraries/elfinderLibs/endpoints/previewPdf.php?url=' + encodeURIComponent(fileUrl);
            contentHtml = '<iframe src="' + previewUrl + '" style="width:100%;height:100%;border:0;"></iframe>';
        } else {

            var iconClass = getElfinderIconClass(file.mime, file.name);
            contentHtml = '<div style="text-align:center;padding:40px;">';
            contentHtml += '  <div class="' + iconClass + '" style="font-size:128px;width:128px;height:128px;margin:0 auto 20px;"></div>';
            contentHtml += '  <h2>' + fm.escape(file.name) + '</h2>';
            contentHtml += '  <p style="color:var(--color-text-muted);">' + (file.mime || 'Unknown type') + '</p>';
            contentHtml += '  <p style="color:var(--color-text-muted);">' + formatBytes(file.size) + '</p>';
            contentHtml += '</div>';
        }
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
    // Apply watermarked URL for clients in floating island
    if (isImage) {
        applyWatermarkedUrl(fm, file.hash, '#' + islandImgId);
    }

    // ─── If it's a 3D model, initialize the viewer ───────────────────
    if (is3DModel) {
        var container = document.getElementById(modelContainerId);
        if (container) {
            if (typeof window.open3DViewer === 'function') {
                window.open3DViewer(container, fileUrl, ext, file.name);
            } else {
                // Dynamically load 3dViewer.js first, then call it
                var script = document.createElement('script');
                script.src = '/libraries/elfinderLibs/3dViewer.js';
                script.onload = function() {
                    window.open3DViewer(container, fileUrl, ext, file.name);
                };
                script.onerror = function() {
                    container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#888;font-family:sans-serif;">Failed to load 3D viewer.</div>';
                };
                document.head.appendChild(script);
            }
        }
    }

    
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

        // Decode the elFinder hash to get the file's reFhlative path
        var relPath = decodeElfinderHash(hash);
        if (!relPath) return;

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