<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';

requireLogin();

// Pagination
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalPages = ceil($total / $limit);

require_once '../../views/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Kategori</h2>
    <a href="create.php" class="btn btn-primary">Tambah Kategori</a>
</div>

<div class="table-responsive">
    <table>
        <tr>
            <th style="width: 80px;">ID</th>
            <th>Nama Kategori</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= escape($cat['id']) ?></td>
                <td><?= escape($cat['name']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn <?= $i === $page ? 'btn-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once '../../views/footer.php'; ?>
