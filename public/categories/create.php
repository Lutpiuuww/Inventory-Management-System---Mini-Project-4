<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';
require_once '../../helpers/validation.php';

requireLogin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $error = validateRequired('Nama Kategori', $name);

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal menyimpan: " . $e->getMessage();
        }
    }
}

require_once '../../views/header.php';
?>

<div class="form-container">
    <h2 style="margin-bottom: 24px;">Tambah Kategori</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="name" required>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <a href="index.php" class="btn" style="flex: 1; text-align: center;">Batal</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan</button>
        </div>
    </form>
</div>

<?php require_once '../../views/footer.php'; ?>
