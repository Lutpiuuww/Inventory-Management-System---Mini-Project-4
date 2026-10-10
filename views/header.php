<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System P4</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1>IMS P4</h1>
            </div>
            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/public/dashboard.php" class="btn">Dashboard</a>
                    <a href="<?= BASE_URL ?>/public/products/index.php" class="btn">Produk</a>
                    <a href="<?= BASE_URL ?>/public/categories/index.php" class="btn">Kategori</a>
                    <a href="<?= BASE_URL ?>/public/suppliers/index.php" class="btn">Supplier</a>
                    <a href="<?= BASE_URL ?>/public/stock/index.php" class="btn">Stok</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?= BASE_URL ?>/public/users/index.php" class="btn">Users</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/public/logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/public/login.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
                
                <button id="themeToggle" class="btn theme-toggle" aria-label="Toggle Dark Mode">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>
        
        <?php if (isLoggedIn()): ?>
            <div style="margin-bottom: 24px; font-weight: bold;">
                <span class="card-badge" style="background: var(--text-muted); color: var(--surface);">
                    User: <?= escape($_SESSION['username'] ?? '') ?> (<?= escape($_SESSION['user_role'] ?? '') ?>)
                </span>
            </div>
        <?php endif; ?>
