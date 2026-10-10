<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';
require_once '../../helpers/validation.php';

requireLogin();

$products = $pdo->query("SELECT id, name, sku, stock FROM products ORDER BY name ASC")->fetchAll();

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $quantity = trim($_POST['quantity'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($product_id) || empty($type) || empty($quantity)) {
        $error = "Semua field wajib diisi.";
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $error = "Kuantitas harus lebih dari 0.";
    } elseif (!in_array($type, ['in', 'out'])) {
        $error = "Tipe transaksi tidak valid.";
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            
            $stmtP = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            $stmtP->execute([$product_id]);
            $product = $stmtP->fetch();
            
            if (!$product) {
                throw new Exception("Produk tidak ditemukan.");
            }
            
            $current_stock = $product['stock'];
            
            if ($type === 'out' && $current_stock < $quantity) {
                throw new Exception("Barang keluar ($quantity) melebihi stok yang tersedia ($current_stock).");
            }
            
            $new_stock = $type === 'in' ? $current_stock + $quantity : $current_stock - $quantity;
            $stmtU = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmtU->execute([$new_stock, $product_id]);
            
            $stmtM = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) VALUES (?, ?, ?, ?, ?)");
            $stmtM->execute([$product_id, getCurrentUserId(), $type, $quantity, $notes]);
            
            $pdo->commit();
            $success = "Transaksi stok berhasil disimpan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

require_once '../../views/header.php';
?>

<div class="form-container">
    <h2 style="margin-bottom: 24px;">Transaksi Stok Baru</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= escape($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Produk</label>
            <select name="product_id" required style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background-color: var(--bg-color); color: var(--text-main);">
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= escape($p['id']) ?>">
                        <?= escape($p['sku']) ?> - <?= escape($p['name']) ?> (Sisa: <?= escape($p['stock']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tipe Transaksi</label>
            <select name="type" required style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background-color: var(--bg-color); color: var(--text-main);">
                <option value="in">Barang Masuk (In)</option>
                <option value="out">Barang Keluar (Out)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Kuantitas</label>
            <input type="number" name="quantity" min="1" required>
        </div>
        <div class="form-group">
            <label>Catatan (Opsional)</label>
            <textarea name="notes" rows="3" style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background-color: var(--bg-color); color: var(--text-main); font-family: inherit;"></textarea>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <a href="index.php" class="btn" style="flex: 1; text-align: center;">Batal</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Transaksi</button>
        </div>
    </form>
</div>

<?php require_once '../../views/footer.php'; ?>
