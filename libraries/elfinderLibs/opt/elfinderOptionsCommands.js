// ===== DYNAMIC COMMAND HELPERS =====

function CanUseCommand(cmd, role) {
    if (cmd.role === 'client') {
        if (role === 'client') return true;
        if (cmd.availableToHigherRoles && (role === 'artist' || role === 'admin')) return true;
        return false;
    }
    if (cmd.role === 'clientOnly') return role === 'client';
    if (cmd.role === 'artist')     return role === 'artist' || role === 'admin';
    if (cmd.role === 'admin')      return role === 'admin';
    return false;
}

function CommandsForMenu(menuName) {
    var role = (window.simeckSession && window.simeckSession.tempRole) || 'client';
    var meta = window.elfinderCommandsMeta || [];
    var cmds = [];

    for (var i = 0; i < meta.length; i++) {
        var cmd = meta[i];
        if (!CanUseCommand(cmd, role)) continue;
        if (cmd.loc.indexOf(menuName) === -1) continue;
        cmds.push(cmd);
    }

    cmds.sort(function(a, b) { return a.order - b.order; });

    var result = [];
    var seenGroups = {};
    var pendingGroup = null;

    for (var i = 0; i < cmds.length; i++) {
        var cmd = cmds[i];
        var parts = (cmd.nicename || '').split('///');
        var groupLabel = parts.length > 1 ? parts[0].trim() : null;

        if (groupLabel) {
            var parentId = 'submenu_' + groupLabel.toLowerCase().replace(/[^a-z0-9]/g, '_');
            if (!seenGroups[groupLabel]) {
                seenGroups[groupLabel] = true;
                if (pendingGroup) {
                    if (pendingGroup.divider === 'above' && result.length > 0) result.push('|');
                    result.push(pendingGroup.parentId);
                    if (pendingGroup.divider === 'below') result.push('|');
                    pendingGroup = null;
                }
                pendingGroup = {
                    label: groupLabel,
                    parentId: parentId,
                    divider: cmd.divider
                };
            }
            continue;
        }

        if (pendingGroup) {
            if (pendingGroup.divider === 'above' && result.length > 0) result.push('|');
            result.push(pendingGroup.parentId);
            if (pendingGroup.divider === 'below') result.push('|');
            pendingGroup = null;
        }

        if (cmd.divider === 'above' && result.length > 0) result.push('|');
        result.push(cmd.commandID);
        if (cmd.divider === 'below') result.push('|');
    }

    if (pendingGroup) {
        if (pendingGroup.divider === 'above' && result.length > 0) result.push('|');
        result.push(pendingGroup.parentId);
        if (pendingGroup.divider === 'below') result.push('|');
    }

    var cleaned = [];
    for (var i = 0; i < result.length; i++) {
        if (result[i] === '|' && cleaned.length > 0 && cleaned[cleaned.length - 1] === '|') continue;
        cleaned.push(result[i]);
    }

    return cleaned;
}

function CommandsList() {
    var role = (window.simeckSession && window.simeckSession.tempRole) || 'client';
    var meta = window.elfinderCommandsMeta || [];
    var cmds = [];
    for (var i = 0; i < meta.length; i++) {
        if (CanUseCommand(meta[i], role)) {
            cmds.push(meta[i]);
        }
    }
    cmds.sort(function(a, b) { return a.order - b.order; });
    var result = [];
    for (var i = 0; i < cmds.length; i++) {
        result.push(cmds[i].commandID);
    }
    return result;
}

function NavbarCommands()  { return CommandsForMenu('navbar'); }
function CWDCommands()     { return CommandsForMenu('cwd'); }
function FilesCommands()   { return CommandsForMenu('files'); }

// ── Register pseudo-commands that elFinder treats as submenu parents ────

function RegisterSubmenuParents() {
    var meta = window.elfinderCommandsMeta || [];
    var role = (window.simeckSession && window.simeckSession.tempRole) || 'client';
    var groups = {};

    for (var i = 0; i < meta.length; i++) {
        var cmd = meta[i];
        if (!CanUseCommand(cmd, role)) continue;
        var parts = (cmd.nicename || '').split('///');
        if (parts.length > 1) {
            var groupLabel = parts[0].trim();
            if (!groups[groupLabel]) groups[groupLabel] = [];
            groups[groupLabel].push({
                commandID: cmd.commandID,
                label: parts[1].trim(),
                order: cmd.order
            });
        }
    }

    for (var groupLabel in groups) {
        var children = groups[groupLabel];
        children.sort(function(a, b) { return a.order - b.order; });
        var parentCmdId = 'submenu_' + groupLabel.toLowerCase().replace(/[^a-z0-9]/g, '_');

        if (!elFinder.prototype.commands[parentCmdId]) {
            (function(pid, label, kids) {
                elFinder.prototype.commands[pid] = function() {
                    this.contextmenu = true;
                    this.title = label;
                    this.init = function() { this.title = label; };
                    this.variants = [];
                    this.contextmenuOpts = { submenu: true };
                    this.exec = function() { return $.Deferred().resolve(); };
                    this.getstate = function() { return 0; };
                    for (var k = 0; k < kids.length; k++) {
                        this.variants.push([kids[k].commandID, kids[k].label]);
                    }
                };
            })(parentCmdId, groupLabel, children);
        }
    }
}

setTimeout(RegisterSubmenuParents, 0);