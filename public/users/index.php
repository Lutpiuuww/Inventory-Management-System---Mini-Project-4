<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';

requireAdmin(); // Hanya admin yang boleh akses

$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../../views/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2>Manajemen User</h2>
    <p style="color: var(--text-muted);">Halaman ini hanya dapat diakses oleh Admin.</p>
</div>

<div class="table-responsive">
    <table>
        <tr>
            <th style="width: 80px;">ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Terdaftar Pada</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= escape($u['id']) ?></td>
                <td><?= escape($u['username']) ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="card-badge" style="background: var(--primary); color: #111;">ADMIN</span>
                    <?php else: ?>
                        <span class="card-badge" style="background: var(--text-muted);">STAFF</span>
                    <?php endif; ?>
                </td>
                <td><?= escape($u['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once '../../views/footer.php'; ?>
