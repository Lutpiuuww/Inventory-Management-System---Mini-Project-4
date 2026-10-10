<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';

requireLogin();

// Parameters for listing
$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$order = $_GET['order'] ?? 'asc';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Whitelist sorting
$allowedSorts = ['id', 'sku', 'name', 'price', 'stock'];
if (!in_array($sort, $allowedSorts)) {
    $sort = 'id';
}
$order = strtolower($order) === 'desc' ? 'desc' : 'asc';

// Build Query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE :search OR p.sku LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category_id !== '') {
    $where[] = "p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $whereClause 
          ORDER BY p.$sort $order 
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// Count total for pagination
$countQuery = "SELECT COUNT(*) FROM products p $whereClause";
$countStmt = $pdo->prepare($countQuery);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Fetch categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

require_once '../../views/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Daftar Produk</h2>
    <a href="create.php" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg> Tambah Produk
    </a>
</div>

<form method="GET" class="search-form" style="flex-wrap: wrap;">
    <input type="text" name="search" placeholder="Cari nama atau SKU..." value="<?= escape($search) ?>" style="min-width: 200px;">
    
    <select name="category_id" style="padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background: var(--surface); color: var(--text-main);">
        <option value="">Semua Kategori</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= escape($cat['id']) ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                <?= escape($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <select name="sort" style="padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background: var(--surface); color: var(--text-main);">
        <?php foreach ($allowedSorts as $s): ?>
            <option value="<?= $s ?>" <?= $sort === $s ? 'selected' : '' ?>>Urut: <?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    
    <select name="order" style="padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background: var(--surface); color: var(--text-main);">
        <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>Naik</option>
        <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Turun</option>
    </select>

    <button type="submit" class="btn">Filter</button>
</form>

<?php if (count($products) > 0): ?>
    <div class="products-grid">
        <?php foreach ($products as $p): ?>
            <div class="card <?= $p['stock'] == 0 ? 'out-of-stock' : '' ?>">
                <?php if (!empty($p['image_path'])): ?>
                    <div class="card-img-wrapper">
                        <img src="<?= BASE_URL ?>/assets/uploads/<?= escape($p['image_path']) ?>" alt="<?= escape($p['name']) ?>">
                    </div>
                <?php endif; ?>
                
                <span class="card-badge"><?= escape($p['category_name'] ?? 'Uncategorized') ?></span>
                <h3><?= escape($p['name']) ?></h3>
                
                <div class="card-data">
                    <span class="label">SKU</span>
                    <span class="value"><?= escape($p['sku']) ?></span>
                </div>
                <div class="card-data">
                    <span class="label">Harga</span>
                    <span class="value">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
                </div>
                <div class="card-data">
                    <span class="label">Stok</span>
                    <span class="value" <?= $p['stock'] == 0 ? 'style="color: var(--danger);"' : '' ?>>
                        <?= $p['stock'] == 0 ? 'HABIS' : escape($p['stock']) ?>
                    </span>
                </div>
                
                <div class="card-actions">
                    <a href="edit.php?id=<?= escape($p['id']) ?>" class="btn">Edit</a>
                    <form method="POST" action="delete.php" style="flex: 1;">
                        <input type="hidden" name="csrf_token" value="<?= escape(generateCsrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= escape($p['id']) ?>">
                        <button type="submit" class="btn btn-danger" style="width: 100%;" onclick="return confirm('Hapus produk ini?')">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert">Tidak ada produk yang ditemukan.</div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        $queryParams = $_GET;
        for ($i = 1; $i <= $totalPages; $i++): 
            $queryParams['page'] = $i;
            $qs = http_build_query($queryParams);
        ?>
            <a href="?<?= escape($qs) ?>" class="btn <?= $i === $page ? 'btn-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once '../../views/footer.php'; ?>
