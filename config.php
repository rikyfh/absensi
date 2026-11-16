<?php
// Nilai bawaan. JANGAN isi kredensial asli di sini (file ini masuk GitHub).
// Salin config.local.sample.php -> config.local.php lalu isi data asli.
$d = ['host'=>'localhost','db'=>'absensi','user'=>'root','pass'=>'','admin_user'=>'admin','admin_pass'=>'admin123','login_required'=>false];
if (is_file(__DIR__ . '/config.local.php')) $d = array_merge($d, require __DIR__ . '/config.local.php');
return $d;
