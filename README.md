# Absensi Wajah Web

Aplikasi absensi siswa berbasis pengenalan wajah (PHP + MySQL + face-api.js).

## Alur
1. Guru login sebagai **admin (back office)** — satu akun saja, siswa tidak perlu akun.
2. Tab **Siswa**: tambah ID + nama, lalu daftarkan wajah lewat kamera.
3. Tab **Scan Wajah** (publik): siswa menghadap kamera → tercatat datang / pulang.
4. Tab **Rekap**: pilih rentang tanggal → tampilkan → export **Excel** / **PDF**.
5. Tab **Pengaturan**: ubah nama instansi.

## Deploy
> GitHub Pages hanya mendukung situs statis, jadi **tidak bisa** menjalankan PHP/MySQL.
> GitHub dipakai untuk menyimpan kode; hosting-nya tetap di server PHP + MySQL
> (shared hosting/cPanel, VPS, dsb.).

1. Upload repo ke GitHub (jangan commit `config.local.php`, sudah ada di `.gitignore`).
2. Di hosting: buat database MySQL, lalu upload/clone repo ke `public_html`
   (cPanel mendukung *Git Version Control* untuk clone langsung dari GitHub).
3. Salin `config.local.sample.php` → `config.local.php`, isi kredensial database
   dan **ganti password admin**.
4. Buka situs lewat **HTTPS** (wajib untuk akses kamera). Tabel dibuat otomatis
   (atau impor `database.sql`).

## Catatan
- Library (face-api.js, SheetJS, jsPDF) dimuat dari CDN → perlu internet.
- Yang disimpan hanya vektor wajah (angka), bukan foto. Tetap data pribadi:
  minta persetujuan siswa/orang tua.
- Zona waktu: Asia/Jakarta (ubah di `api.php`).

## Login
Bawaan: **tanpa login** (`login_required` = false di config) agar guru langsung bisa buka Siswa & Rekap.
Untuk mengaktifkan login, set `'login_required' => true` di `config.local.php`.
