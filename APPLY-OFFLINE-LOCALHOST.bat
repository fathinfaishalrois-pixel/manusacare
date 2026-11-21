@echo off
setlocal
cd /d "%~dp0"
title MANUSACARE - Terapkan Offline Localhost
cls
echo ================================================
echo      MANUSACARE - OFFLINE LOCALHOST
echo ================================================
echo.
if not exist "artisan" (
  echo [ERROR] Jalankan script ini dari folder utama MANUSACARE.
  pause
  exit /b 1
)
if not exist "app\Http\Controllers\Auth\AuthenticatedSessionController.php" (
  echo [ERROR] Controller login tidak ditemukan dalam paket.
  pause
  exit /b 1
)
if not exist "vendor\autoload.php" (
  echo [ERROR] Folder vendor belum tersedia. Jalankan composer install terlebih dahulu.
  pause
  exit /b 1
)
echo [1/4] Memperbarui autoload Composer bila tersedia...
where composer >nul 2>nul
if not errorlevel 1 (
  composer dump-autoload
  if errorlevel 1 goto failed
) else (
  echo [PERINGATAN] Composer tidak ditemukan. Autoload yang ada akan digunakan.
)
echo.
echo [2/4] Membersihkan cache Laravel...
php artisan optimize:clear
if errorlevel 1 goto failed
echo.
echo [3/4] Memeriksa route autentikasi dan pendaftaran...
php artisan route:list --path=login
if errorlevel 1 goto failed
php artisan route:list --path=daftar
if errorlevel 1 goto failed
echo [4/4] Memeriksa route media...
php artisan route:list --path=media
if errorlevel 1 goto failed
echo.
echo Selesai. Jalankan START-LOCALHOST.bat.
pause
exit /b 0
:failed
echo.
echo Ada pemeriksaan yang gagal. Jangan lanjut dulu; kirim pesan error di atas.
pause
exit /b 1
