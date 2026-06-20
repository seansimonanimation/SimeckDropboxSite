// ===== DYNAMIC COMMAND HELPERS =====

function CanUseCommand(cmd, role) {
    if (cmd.role === 'client') {
        if (role === 'client') return true;
        if (cmd.availableToHigherRoles && (role === 'artist' || role === 'admin')) return true;
        return false;
    }
    if (cmd.role === 'clientOnly') return role === 'client'; //legacy role.
    if (cmd.role === 'artist')     return role === 'artist' || role === 'admin';
    if (cmd.role === 'admin')      return role === 'admin';
    return false;
}


function CommandsForMenu(menuName) {
    var role = (window.simeckSession && window.simeckSession.tempRole) || 'client';
    var meta = window.elfinderCommandsMeta || [];
    var cmds = [];
    
    // Filter by role and menu location
    for (var i = 0; i < meta.length; i++) {
        var cmd = meta[i];
        if (!CanUseCommand(cmd, role)) continue;
        if (cmd.loc.indexOf(menuName) === -1) continue;
        cmds.push(cmd);
    }
    
    // Sort by order
    cmds.sort(function(a, b) { return a.order - b.order; });
    
    // Build result array with dividers
    var result = [];
    for (var i = 0; i < cmds.length; i++) {
        if (cmds[i].divider === 'above' && result.length > 0) {
            result.push('|');
        }
        result.push(cmds[i].commandID);
        if (cmds[i].divider === 'below') {
            result.push('|');
        }
    }
    // Collapse consecutive dividers into one
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
