<?php
require_once '../../config/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/escape.php';
require_once '../../helpers/validation.php';

requireLogin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    
    if (!validateCsrfToken($token)) {
        die("Invalid CSRF token.");
    }

    if (!empty($id)) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $image_path = $stmt->fetchColumn();

            $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmtDel->execute([$id]);
            
            // Hapus file fisik gambar jika ada
            if ($image_path && file_exists('../../assets/uploads/' . $image_path)) {
                unlink('../../assets/uploads/' . $image_path);
            }
            
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            // Error code 23000 typically means foreign key constraint violation
            if ($e->getCode() == 23000) {
                die("Gagal menghapus: Produk ini memiliki histori transaksi atau terhubung dengan data lain.");
            }
            die("Gagal menghapus: " . $e->getMessage());
        }
    }
}
header("Location: index.php");
exit;
