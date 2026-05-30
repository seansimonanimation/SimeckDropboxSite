$(function() {
    // Also call resizeElfinder on initial load
    resizeElfinder();
});

function populateLockCache(fm) {
    // Query all locked file info under /files/Projects/
    $.post('libraries/elfinderLibs/lockedFilesEndpoint.php', {
        action: 'query',
        directory: '/files/Projects'
    }, function(response) {
        if (response.success && response.locked) {
            if (!fm.cache) fm.cache = {};
            fm.cache.lockedPaths = {};
            $.each(response.locked, function(i, lock) {
                fm.cache.lockedPaths[lock.filepath] = {
                    assetlock: lock.assetlock,
                    commentlock: lock.commentlock
                };
            });
        }
    }, 'json');

    // Fetch client's override count
    $.post('libraries/elfinderLibs/lockedFilesEndpoint.php', {
        action: 'get_client_overrides'
    }, function(response) {
        if (response.success && typeof response.overrides !== 'undefined') {
            if (!fm.cache) fm.cache = {};
            fm.cache.clientOverrides = parseInt(response.overrides, 10);
        }
    }, 'json');
}

function resizeElfinder() {
    var winH = $(window).height();
    var offset = $('#elfinder').offset().top;
    var h = winH - offset;
    if (h < 300) h = 300;
    $('#elfinder').height(h);
    var instance = $('#elfinder').elfinder('instance');
    if (instance) {
        instance.resize();
    }
}