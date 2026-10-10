<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';
require_once '../../helpers/validation.php';

requireLogin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    
    $error = validateRequired('Nama', $name);

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_info) VALUES (?, ?)");
        $stmt->execute([$name, $contact]);
        header("Location: index.php");
        exit;
    }
}

require_once '../../views/header.php';
?>

<div class="form-container">
    <h2 style="margin-bottom: 24px;">Tambah Supplier</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nama Supplier</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Kontak Info</label>
            <input type="text" name="contact">
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <a href="index.php" class="btn" style="flex: 1; text-align: center;">Batal</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan</button>
        </div>
    </form>
</div>

<?php require_once '../../views/footer.php'; ?>
