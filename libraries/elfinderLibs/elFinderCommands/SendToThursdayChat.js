/**
 * @commandID sendToThursdayChat
 * @nicename Send to Thursday Chat
 */
elFinder.prototype.commands.sendToThursdayChat = function() {
    this.contextmenu = true;

    this.init = function(){
        this.title = 'Send to Thursday Chat';
    };
    this.exec = function(hashes) {
        var fm = this.fm;
        var files = fm.selectedFiles();
        var dfrd = $.Deferred();

        var fileData = files.map(function(f) {
            return { name: f.name, url: fm.url(f.hash) };
        });

        fm.notify({
            type: 'discord',
            cnt: 1,
            msg: 'Sending ' + files.length + ' file(s) to Thursday Chat...',
            progress: 0
        });

        $.post('/libraries/elfinderLibs/endpoints/discordWebhookEndpoint.php', {
            action: 'sendToThursdayChat',
            files: JSON.stringify(fileData)
        }).done(function(resp) {
            if (resp.success) {
                var msg = 'Sent ' + resp.files_sent + ' file(s) to Thursday Chat';
                if (resp.batches > 1) msg += ' (' + resp.batches + ' messages)';
                fm.notify({ type: 'discord', cnt: -1 });
                fm.notify({ type: 'info', msg: msg });
            } else {
                fm.notify({ type: 'discord', cnt: -1 });
                fm.notify({ type: 'error', msg: resp.error || 'Failed to send to Discord.' });
            }
        }).fail(function(jqXHR) {
            fm.notify({ type: 'discord', cnt: -1 });
            var errMsg = 'Request failed.';
            try {
                var resp = JSON.parse(jqXHR.responseText);
                if (resp.error) errMsg = resp.error;
            } catch(e) {}
            fm.notify({ type: 'error', msg: errMsg });
        }).always(function() { dfrd.resolve(); });

        return dfrd.promise();
    };
    this.getstate = function() {
        return this.fm.selectedFiles().length ? 1 : 0;
    };
};
