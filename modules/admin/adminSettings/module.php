<?php
/**
 * @module adminSettings
 * @name Settings
 * @role admin
 * @nav-text Platform Settings
 * @nav-icon settings
 * @nav-order 99
 */
include_once __ROOT__ . '/libraries/session.php';
include_once __ROOT__ . '/libraries/db.php';
include_once __ROOT__ . '/libraries/artistmanagementlib.php';

// ════════════════════════════════════════════════════════════
//  HANDLERS
// ════════════════════════════════════════════════════════════

// Add a new secondary role
if (isset($_GET['add_secondary_role']) && isset($_GET['role_name']) && isset($_GET['display_name'])) {
    $roleName = trim($_GET['role_name']);
    $displayName = trim($_GET['display_name']);
    if ($roleName !== '' && $displayName !== '') {
        $result = AddDefinedSecondaryRole($roleName, $displayName);
        if (!$result) {
            $error = 'Role "' . htmlspecialchars($roleName) . '" already exists.';
        }
    }
    RefreshPortal();
}

// Remove a secondary role
if (isset($_GET['remove_secondary_role']) && isset($_GET['id'])) {
    RemoveDefinedSecondaryRole((int)$_GET['id']);
}
?>

<link rel="stylesheet" href="/css/moduleStyle.css">
<div class="module">
    <div class="module-header">
        <h1>Platform Settings</h1>
    </div>
    <div class="module-grid">

        <!-- ════════════════════════════════════════════════ -->
        <!--  SECONDARY ROLES MANAGEMENT CARD                -->
        <!-- ════════════════════════════════════════════════ -->
        <div class="module-card module-card--span-1">
            <h2>Secondary Roles</h2>
            <p>Define the available secondary roles that can be assigned to artists.</p>

            <?php if (isset($error)): ?>
                <div style="color:red; font-weight:bold;"><?= $error ?></div>
            <?php endif; ?>

            <!-- Add new role form -->
            <form method="GET" style="margin-bottom:1rem;">
                <input type="hidden" name="add_secondary_role" value="1" />
                <input class="module-input" type="text" name="role_name" placeholder="Role name (e.g. marketing)" required style="width:100%; margin-bottom:0.5rem;" />
                <input class="module-input" type="text" name="display_name" placeholder="Display name (e.g. Marketing)" required style="width:100%; margin-bottom:0.5rem;" />
                <button class="module-button" type="submit">Add Role</button>
            </form>

            <!-- Existing roles list -->
            <?php
            $roles = GetAllDefinedSecondaryRoles();
            if (empty($roles)): ?>
                <p style="color:#888;">No secondary roles defined yet.</p>
            <?php else: ?>
                <table style="width:100%; border-collapse: collapse; overflow-x: auto; display: block;">
                    <thead>
                        <tr>
                            <th style="text-align:left; border-bottom:1px solid #444;">Role Name</th>
                            <th style="text-align:left; border-bottom:1px solid #444;">Display Name</th>
                            <th style="border-bottom:1px solid #444;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td style="padding:0.3rem 0;"><?= htmlspecialchars($role['role_name']) ?></td>
                            <td style="padding:0.3rem 0;"><?= htmlspecialchars($role['display_name']) ?></td>
                            <td style="padding:0.3rem 0;">
                                <a href="?remove_secondary_role=1&id=<?= $role['id'] ?>" onclick="return confirm('Delete role &quot;<?= htmlspecialchars($role['display_name'], ENT_QUOTES) ?>&quot;?');" style="text-decoration:none;">❌</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>
