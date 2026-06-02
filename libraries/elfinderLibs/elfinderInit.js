$(function() {
        resizeElfinder();
});

function populateLockCache(fm) {
    $.post('libraries/elfinderLibs/endpoints/GetLockedfilesInProjectEndpoint.php', {}, function(response) {
        if (!response.success || !response.lockedFiles) return;
        if (!fm.cache) fm.cache = {};
        fm.cache.lockedPaths = {};
        response.lockedFiles.forEach(function(lock) {
            fm.cache.lockedPaths[lock.filepath] = {
                assetlock: lock.assetlock,
                commentlock: lock.commentlock
            };
        });
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

document.addEventListener('dragstart', function(e) {
    if (e.target.closest('#elfinder')) {
        e.preventDefault();
    }
});
