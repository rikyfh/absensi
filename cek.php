<?php
// Buka /cek.php untuk memeriksa server, HAPUS setelah semuanya normal.
header('Content-Type: text/plain; charset=utf-8');
echo "PHP berjalan: " . PHP_VERSION . "\n";
echo "Ekstensi pdo_mysql: " . (extension_loaded('pdo_mysql') ? 'OK' : 'TIDAK ADA') . "\n";
$c = require __DIR__ . '/config.php';
echo "login_required: " . (empty($c['login_required']) ? 'false (tanpa login)' : 'true') . "\n";
try { new PDO("mysql:host={$c['host']};dbname={$c['db']};charset=utf8mb4", $c['user'], $c['pass']); echo "Database: TERHUBUNG\n"; }
catch (Exception $e) { echo "Database: GAGAL - " . $e->getMessage() . "\n"; }
