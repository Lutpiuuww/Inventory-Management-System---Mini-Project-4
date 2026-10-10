<?php
// Tentukan Base URL secara dinamis dari SCRIPT_NAME agar aman di Windows / berbagai environment
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$public_pos = strpos($script_name, '/public/');
if ($public_pos !== false) {
    $base_url = substr($script_name, 0, $public_pos);
} else {
    // Jika diakses dari luar folder public (misal index.php)
    $base_url = dirname($script_name);
}
// Pastikan tidak berujung slash
$base_url = rtrim(str_replace('\\', '/', $base_url), '/');
define('BASE_URL', $base_url);

$host = 'localhost';
$dbname = 'store_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
