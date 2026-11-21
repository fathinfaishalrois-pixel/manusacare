@echo off
setlocal
cd /d "%~dp0"
title MANUSACARE - Reset Admin
if not exist artisan (
  echo [ERROR] Jalankan file ini dari folder utama MANUSACARE.
  pause
  exit /b 1
)
if not exist vendor\autoload.php (
  echo [ERROR] Folder vendor belum tersedia. Jalankan composer install terlebih dahulu.
  pause
  exit /b 1
)
echo Memulihkan akun admin lokal MANUSACARE...
php artisan manusacare:reset-admin
if errorlevel 1 (
  echo.
  echo [ERROR] Pemulihan gagal. Pastikan migrasi database sudah dijalankan dan MySQL Laragon aktif.
  pause
  exit /b 1
)
echo.
echo Login: http://127.0.0.1:8000/admin/login
echo ID: admin
echo Email: admin@manusacare.local
echo Password: ChangeMe123!
echo Segera ganti password setelah masuk.
pause
