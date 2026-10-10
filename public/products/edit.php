<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';
require_once '../../helpers/validation.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = $_GET['id'];

// Get Product Data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Get Product Suppliers
$stmtSup = $pdo->prepare("SELECT supplier_id FROM product_supplier WHERE product_id = ?");
$stmtSup->execute([$id]);
$current_suppliers = $stmtSup->fetchAll(PDO::FETCH_COLUMN);

// Master Data
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

    $image_path = $product['image_path'];
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
                // Hapus gambar lama jika ada
                if ($image_path && file_exists('../../assets/uploads/' . $image_path)) {
                    unlink('../../assets/uploads/' . $image_path);
                }
                $image_path = $newFileName;
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE products SET sku = ?, name = ?, category_id = ?, price = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$sku, $name, $category_id, $price, $image_path, $id]);
            
            // Re-sync suppliers (delete old, insert new)
            $pdo->prepare("DELETE FROM product_supplier WHERE product_id = ?")->execute([$id]);
            if (!empty($selected_suppliers)) {
                $stmtIns = $pdo->prepare("INSERT INTO product_supplier (product_id, supplier_id) VALUES (?, ?)");
                foreach ($selected_suppliers as $sup_id) {
                    $stmtIns->execute([$id, $sup_id]);
                }
            }
            
            $pdo->commit();
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $error = "SKU sudah digunakan oleh produk lain.";
            } else {
                $error = "Gagal menyimpan: " . $e->getMessage();
            }
        }
    }
}

require_once '../../views/header.php';
?>

<div class="form-container">
    <h2 style="margin-bottom: 24px;">Edit Produk</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Gambar Produk (Opsional - Kosongkan jika tidak diubah)</label>
            <?php if ($product['image_path']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?= BASE_URL ?>/assets/uploads/<?= escape($product['image_path']) ?>" style="max-height: 100px; border: 2px solid var(--border-color); border-radius: var(--radius);">
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/jpeg, image/png, image/webp, image/gif" style="padding: 10px; border: 2px dashed var(--border-color); background: var(--bg-color);">
        </div>
        <div class="form-group">
            <label>SKU (Unik)</label>
            <input type="text" name="sku" required value="<?= escape($_POST['sku'] ?? $product['sku']) ?>">
        </div>
        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" required value="<?= escape($_POST['name'] ?? $product['name']) ?>">
        </div>
        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" required style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--radius); background-color: var(--bg-color); color: var(--text-main);">
                <option value="">-- Pilih Kategori --</option>
                <?php $selCat = $_POST['category_id'] ?? $product['category_id']; ?>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= escape($cat['id']) ?>" <?= $selCat == $cat['id'] ? 'selected' : '' ?>>
                        <?= escape($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="price" min="0" required value="<?= escape($_POST['price'] ?? $product['price']) ?>">
        </div>
        
        <div class="form-group">
            <label>Supplier (Bisa Pilih Banyak)</label>
            <div style="background: var(--bg-color); border: 2px solid var(--border-color); padding: 12px; border-radius: var(--radius);">
                <?php $selSups = $_POST['suppliers'] ?? $current_suppliers; ?>
                <?php foreach ($suppliers as $sup): ?>
                    <label style="font-weight: normal; margin-bottom: 8px; text-transform: none; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="suppliers[]" value="<?= escape($sup['id']) ?>" <?= in_array($sup['id'], $selSups) ? 'checked' : '' ?> style="width: auto;">
                        <?= escape($sup['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <a href="index.php" class="btn" style="flex: 1; text-align: center;">Batal</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Update</button>
        </div>
    </form>
</div>

<?php require_once '../../views/footer.php'; ?>
