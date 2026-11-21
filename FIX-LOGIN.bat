@echo off
setlocal
cd /d "%~dp0"
title MANUSACARE - Perbaikan Login
echo ================================================
echo       MANUSACARE - PERBAIKAN LOGIN
echo ================================================
echo.
if not exist "artisan" (
  echo [ERROR] Jalankan file ini dari folder utama MANUSACARE.
  pause
  exit /b 1
)
if not exist "app\Http\Controllers\Auth" mkdir "app\Http\Controllers\Auth"
if not exist "app\Http\Controllers\Auth\AuthenticatedSessionController.php" (
  echo [ERROR] File controller tidak ada dalam paket. Ekstrak ulang paket perbaikan.
  pause
  exit /b 1
)
findstr /C:"namespace App\Http\Controllers\Auth;" "app\Http\Controllers\Auth\AuthenticatedSessionController.php" >nul
if errorlevel 1 (
  echo [ERROR] Namespace controller tidak sesuai.
  pause
  exit /b 1
)
if not exist "vendor\autoload.php" (
  echo [ERROR] Folder vendor belum ada. Jalankan composer install terlebih dahulu.
  pause
  exit /b 1
)
where composer >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Composer tidak ditemukan di PATH.
  pause
  exit /b 1
)
echo [1/3] Memperbarui autoload Composer...
composer dump-autoload
if errorlevel 1 goto failed
echo.
echo [2/3] Membersihkan cache Laravel...
php artisan optimize:clear
if errorlevel 1 goto failed
echo.
echo [3/3] Memeriksa route login...
php artisan route:list --path=login
if errorlevel 1 goto failed
echo.
echo Berhasil. Coba buka http://127.0.0.1:8000/login
pause
exit /b 0
:failed
echo.
echo Perbaikan belum berhasil. Simpan seluruh pesan error untuk diperiksa.
pause
exit /b 1
