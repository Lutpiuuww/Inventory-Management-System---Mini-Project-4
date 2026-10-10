<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';
require_once '../../helpers/validation.php';

requireLogin();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $selected_suppliers = $_POST['suppliers'] ?? [];
    
    // Validations
    $errSku = validateRequired('SKU', $sku);
    $errName = validateRequired('Nama', $name);
    $errCat = validateRequired('Kategori', $category_id);
    $errPrice = validateNonNegative('Harga', $price);
    
    if ($errSku || $errName || $errCat || $errPrice) {
        $error = implode("<br>", array_filter([$errSku, $errName, $errCat, $errPrice]));
    }
    
    $image_path = null;
    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (!in_array($fileExt, $allowed)) {
            $error = "Format gambar tidak valid (Hanya JPG, PNG, WEBP, GIF).";
        } elseif ($_FILES['image']['size'] > 2000000) {
            $error = "Ukuran gambar tidak boleh lebih dari 2MB.";
        } else {
            $newFileName = uniqid('prod_', true) . '.' . $fileExt;
            $destination = '../../assets/uploads/' . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $image_path = $newFileName;
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO products (sku, name, category_id, price, stock, image_path) VALUES (?, ?, ?, ?, 0, ?)");
            $stmt->execute([$sku, $name, $category_id, $price, $image_path]);
            $product_id = $pdo->lastInsertId();
            
            if (!empty($selected_suppliers)) {
                $stmtSup = $pdo->prepare("INSERT INTO product_supplier (product_id, supplier_id) VALUES (?, ?)");
                foreach ($selected_suppliers as $sup_id) {
                    $stmtSup->execute([$product_id, $sup_id]);
                }
            }
            
            $pdo->commit();
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = "SKU sudah digunakan, harap gunakan SKU lain.";
            } else {
                $error = "Gagal menyimpan: " . $e->getMessage();
            }
        }
    }
}

require_once '../../views/header.php';
?>

<div class="form-container">
    <h2 style="margin-bottom: 24px;">Tambah Produk Baru</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Gambar Produk (Maks 2MB)</label>
            <input type="file" name="image" accept="image/jpeg, image/png, image/webp, image/gif" style="padding: 10px; border: 2px dashed var(--border-color); background: var(--bg-color);">
        </div>
        <div class="form-group">
            <label>SKU (Unik)</label>
            <input type="text" name="sku" required value="<?= escape($_POST['sku'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" required value="<?= escape($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" required style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background-color: var(--bg-color); color: var(--text-main);">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= escape($cat['id']) ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= escape($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="price" min="0" required value="<?= escape($_POST['price'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label>Supplier (Bisa Pilih Banyak)</label>
            <div style="background: var(--bg-color); border: 2px solid var(--border-color); padding: 12px; border-radius: var(--radius);">
                <?php foreach ($suppliers as $sup): ?>
                    <label style="font-weight: normal; margin-bottom: 8px; text-transform: none; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="suppliers[]" value="<?= escape($sup['id']) ?>" style="width: auto;">
                        <?= escape($sup['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="alert" style="padding: 12px; font-size: 0.85rem; border-color: var(--border-color);">
            <em>* Stok default adalah 0. Silakan lakukan transaksi Barang Masuk untuk menambah stok.</em>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <a href="index.php" class="btn" style="flex: 1; text-align: center;">Batal</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan</button>
        </div>
    </form>
</div>

<?php require_once '../../views/footer.php'; ?>
