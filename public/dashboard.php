<?php
require_once '../config/db.php';
require_once '../helpers/auth.php';
require_once '../helpers/escape.php';

requireLogin();

// Agregat Total Produk & Nilai Inventory
$stmt = $pdo->query("SELECT COUNT(*) as total_items, SUM(price * stock) as total_value FROM products");
$summary = $stmt->fetch();

// Total Kategori
$stmtCat = $pdo->query("SELECT COUNT(*) as total_categories FROM categories");
$summaryCat = $stmtCat->fetch();

// Produk yang stoknya mau habis (< 5)
$stmtLowStock = $pdo->query("SELECT name, stock FROM products WHERE stock < 5 ORDER BY stock ASC");
$lowStocks = $stmtLowStock->fetchAll();

require_once '../views/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2>Dashboard</h2>
    <p style="color: var(--text-muted);">Ringkasan inventaris Anda.</p>
</div>

<div class="products-grid" style="margin-bottom: 40px;">
    <div class="card">
        <span class="card-badge">Items</span>
        <h3>Total Produk</h3>
        <div class="card-data" style="border: none;">
            <span class="value" style="font-size: 2rem; color: var(--primary);"><?= escape($summary['total_items'] ?? 0) ?></span>
        </div>
    </div>
    
    <div class="card">
        <span class="card-badge">Value</span>
        <h3>Nilai Inventaris</h3>
        <div class="card-data" style="border: none;">
            <span class="value" style="font-size: 1.5rem; color: var(--primary);">Rp <?= number_format($summary['total_value'] ?? 0, 0, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="card">
        <span class="card-badge">Master</span>
        <h3>Total Kategori</h3>
        <div class="card-data" style="border: none;">
            <span class="value" style="font-size: 2rem; color: var(--primary);"><?= escape($summaryCat['total_categories'] ?? 0) ?></span>
        </div>
    </div>
</div>

<h3 style="margin-bottom: 16px;">Peringatan Stok Menipis</h3>
<?php if (count($lowStocks) > 0): ?>
    <div class="table-responsive">
    <table>
        <tr>
            <th>Nama Produk</th>
            <th>Sisa Stok</th>
        </tr>
        <?php foreach ($lowStocks as $ls): ?>
            <tr style="<?= $ls['stock'] == 0 ? 'background: rgba(220,38,38,0.1);' : '' ?>">
                <td><?= escape($ls['name']) ?></td>
                <td style="color: var(--danger); font-weight: bold;"><?= escape($ls['stock']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-success">Semua stok produk masih aman.</div>
<?php endif; ?>

<?php require_once '../views/footer.php'; ?>
