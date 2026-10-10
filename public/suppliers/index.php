<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';

requireLogin();

$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll();

require_once '../../views/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Supplier</h2>
    <a href="create.php" class="btn btn-primary">Tambah Supplier</a>
</div>

<div class="table-responsive">
    <table>
        <tr>
            <th style="width: 80px;">ID</th>
            <th>Nama Supplier</th>
            <th>Kontak</th>
        </tr>
        <?php foreach ($suppliers as $sup): ?>
            <tr>
                <td><?= escape($sup['id']) ?></td>
                <td><?= escape($sup['name']) ?></td>
                <td><?= escape($sup['contact_info']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once '../../views/footer.php'; ?>
