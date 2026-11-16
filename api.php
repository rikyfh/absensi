<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');
$c = require __DIR__ . '/config.php';
$out = function ($d) { die(json_encode($d)); };
try {
  $db = new PDO("mysql:host={$c['host']};dbname={$c['db']};charset=utf8mb4", $c['user'], $c['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (Exception $e) { http_response_code(500); $out(['error' => 'Koneksi database gagal, cek config.php']); }

$db->exec("CREATE TABLE IF NOT EXISTS students(id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(30) UNIQUE NOT NULL, name VARCHAR(100) NOT NULL, descriptor MEDIUMTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$db->exec("CREATE TABLE IF NOT EXISTS attendance(id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, date DATE NOT NULL, time_in TIME, time_out TIME NULL, UNIQUE(student_id,date), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE)");
$db->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(50) PRIMARY KEY, v TEXT)");

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$a = $_GET['a'] ?? '';
$admin = empty($c['login_required']) || !empty($_SESSION['admin']);
if (!in_array($a, ['settings', 'me', 'login', 'logout', 'faces', 'scan']) && !$admin) {
  http_response_code(401); $out(['error' => 'Login diperlukan']);
}

try {
switch ($a) {
  case 'settings':
    $r = $db->query("SELECT v FROM settings WHERE k='name'")->fetchColumn();
    $out(['name' => $r ?: 'SMA Nusantara']);
  case 'me': $out(['admin' => $admin, 'login' => !empty($c['login_required'])]);
  case 'login':
    if (($in['username'] ?? '') === $c['admin_user'] && ($in['password'] ?? '') === $c['admin_pass']) { $_SESSION['admin'] = 1; $out(['ok' => 1]); }
    http_response_code(401); $out(['error' => 'Username atau password salah']);
  case 'logout': session_destroy(); $out(['ok' => 1]);
  case 'faces':
    $out($db->query("SELECT id,code,name,descriptor FROM students WHERE descriptor IS NOT NULL")->fetchAll());
  case 'scan':
    $id = (int)($in['id'] ?? 0); $d = date('Y-m-d'); $t = date('H:i:s');
    $s = $db->prepare("SELECT name,code FROM students WHERE id=?"); $s->execute([$id]); $st = $s->fetch();
    if (!$st) $out(['error' => 'Siswa tidak ditemukan']);
    $q = $db->prepare("SELECT * FROM attendance WHERE student_id=? AND date=?"); $q->execute([$id, $d]); $r = $q->fetch();
    if (!$r) {
      $db->prepare("INSERT INTO attendance(student_id,date,time_in) VALUES(?,?,?)")->execute([$id, $d, $t]);
      $out($st + ['type' => 'datang', 'time' => $t]);
    }
    if (time() - strtotime("$d {$r['time_in']}") < 1800) $out($st + ['type' => 'sudah', 'time' => $r['time_in']]);
    $db->prepare("UPDATE attendance SET time_out=? WHERE id=?")->execute([$t, $r['id']]);
    $out($st + ['type' => 'pulang', 'time' => $t]);
  case 'students':
    $out($db->query("SELECT id,code,name,(descriptor IS NOT NULL) has_face FROM students ORDER BY code")->fetchAll());
  case 'student_save':
    $code = trim($in['code'] ?? ''); $name = trim($in['name'] ?? '');
    if (!$code || !$name) { http_response_code(400); $out(['error' => 'ID dan nama wajib diisi']); }
    $desc = $in['descriptor'] ?? null;
    if (!empty($in['id'])) {
      $db->prepare("UPDATE students SET code=?,name=? WHERE id=?")->execute([$code, $name, $in['id']]);
      if ($desc) $db->prepare("UPDATE students SET descriptor=? WHERE id=?")->execute([$desc, $in['id']]);
    } else {
      $db->prepare("INSERT INTO students(code,name,descriptor) VALUES(?,?,?)")->execute([$code, $name, $desc]);
    }
    $out(['ok' => 1]);
  case 'student_delete':
    $db->prepare("DELETE FROM students WHERE id=?")->execute([(int)$in['id']]); $out(['ok' => 1]);
  case 'report':
    $q = $db->prepare("SELECT s.code,s.name,a.date,a.time_in,a.time_out FROM attendance a JOIN students s ON s.id=a.student_id WHERE a.date BETWEEN ? AND ? ORDER BY a.date,s.code");
    $q->execute([$in['from'] ?? date('Y-m-01'), $in['to'] ?? date('Y-m-d')]);
    $out($q->fetchAll());
  case 'settings_save':
    $db->prepare("REPLACE INTO settings(k,v) VALUES('name',?)")->execute([trim($in['name'] ?? '')]); $out(['ok' => 1]);
  default: http_response_code(404); $out(['error' => 'Aksi tidak dikenal']);
}
} catch (PDOException $e) {
  http_response_code(400);
  $out(['error' => $e->getCode() == 23000 ? 'ID siswa sudah dipakai' : 'Kesalahan database']);
}
