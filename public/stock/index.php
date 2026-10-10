<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';

requireLogin();

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT m.*, p.name as product_name, u.username 
          FROM stock_movements m 
          JOIN products p ON m.product_id = p.id 
          JOIN users u ON m.user_id = u.id 
          ORDER BY m.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movements = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM stock_movements")->fetchColumn();
$totalPages = ceil($total / $limit);

require_once '../../views/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Histori Transaksi Stok</h2>
    <a href="create.php" class="btn btn-primary">Transaksi Baru</a>
</div>

<div class="table-responsive">
    <table>
        <tr>
            <th>Waktu</th>
            <th>Produk</th>
            <th>User</th>
            <th>Tipe</th>
            <th>Kuantitas</th>
            <th>Catatan</th>
        </tr>
        <?php foreach ($movements as $m): ?>
            <tr>
                <td><?= escape($m['created_at']) ?></td>
                <td><?= escape($m['product_name']) ?></td>
                <td><?= escape($m['username']) ?></td>
                <td>
                    <?php if ($m['type'] === 'in'): ?>
                        <span class="card-badge" style="background: #16a34a;">MASUK</span>
                    <?php else: ?>
                        <span class="card-badge" style="background: var(--danger);">KELUAR</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight: bold;"><?= escape($m['quantity']) ?></td>
                <td><?= escape($m['notes']) ?></td>
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
