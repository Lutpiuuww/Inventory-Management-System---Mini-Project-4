<?php
require_once '../config/db.php';
require_once '../helpers/auth.php';
require_once '../helpers/escape.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        $passwordHash = $user['password_hash'] ?? $user['password'] ?? null;

        if ($user && $passwordHash && password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    } else {
        $error = "Silakan isi username dan password.";
    }
}

require_once '../views/header.php';
?>

<div class="form-container" style="margin-top: 50px;">
    <h2 style="margin-bottom: 24px;">Login IMS</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Username (hint: admin/staff)</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password (hint: password)</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Login</button>
    </form>
</div>

<?php require_once '../views/footer.php'; ?>
