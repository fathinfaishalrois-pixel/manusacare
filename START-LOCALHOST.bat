@echo off
setlocal
cd /d "%~dp0"
title MANUSACARE - Localhost
cls
echo ================================================
echo          MANUSACARE - LOCALHOST ONLY
echo ================================================
echo Aplikasi hanya dapat dibuka di komputer ini.
echo URL: http://127.0.0.1:8000
echo.
if not exist "vendor\autoload.php" (
  echo [ERROR] Folder vendor belum tersedia.
  echo Jalankan composer install saat dependency tersedia.
  pause
  exit /b 1
)
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
pause
