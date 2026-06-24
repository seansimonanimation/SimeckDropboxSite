/**
 * linklib.js - Client-side link generation
 * 
 * Generates download URLs from elFinder hashes by calling the
 * linkEndpoint synchronously.
 */
var Simeck = window.Simeck || {};

Simeck.MakeLink = function(hash, mode, type) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/libraries/elfinderLibs/endpoints/linkEndpoint.php', false);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('hash=' + encodeURIComponent(hash) + '&mode=' + encodeURIComponent(mode) + '&type=' + encodeURIComponent(type));
    
    if (xhr.status === 200) {
        try {
            var response = JSON.parse(xhr.responseText);
            if (response.success) return response.url;
        } catch(e) {}
    }
    return null;
};

Simeck.MakeShortlink = function(hash, mode) {
    return Simeck.MakeLink(hash, mode, 'shortlink');
};
