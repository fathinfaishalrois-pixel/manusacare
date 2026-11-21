@echo off
setlocal
cd /d "%~dp0"
title MANUSACARE - Instalasi Laragon Offline Localhost
echo ================================================
echo MANUSACARE - Setup Laravel MVC Offline Localhost
echo ================================================
where php >nul 2>nul || (echo [ERROR] PHP tidak ditemukan. Aktifkan PHP Laragon / PATH. & pause & exit /b 1)
if exist database\migrations\0001_01_01_000003_create_cache_and_jobs_tables.php del /q database\migrations\0001_01_01_000003_create_cache_and_jobs_tables.php
if not exist .env (
  if not exist .env.example (echo [ERROR] .env.example tidak ditemukan. & pause & exit /b 1)
  copy .env.example .env >nul
  echo [INFO] .env dibuat dari .env.example
)
if not exist vendor\autoload.php (
  where composer >nul 2>nul || (echo [ERROR] Folder vendor belum ada dan Composer tidak ditemukan. Jalankan composer install saat internet tersedia, atau siapkan vendor terlebih dahulu. & pause & exit /b 1)
  echo [1/3] Memasang dependency PHP...
  composer install --no-interaction
  if errorlevel 1 goto failed
) else (
  echo [INFO] Dependency PHP vendor sudah tersedia.
)
findstr /C:"APP_KEY=" .env >nul
if errorlevel 1 (
  echo [INFO] APP_KEY tidak ditemukan. Periksa file .env.
) else (
  findstr /R /C:"^APP_KEY=$" .env >nul
  if not errorlevel 1 php artisan key:generate
)
echo.
echo Pastikan database manusacare sudah dibuat dan MySQL Laragon aktif.
echo [2/3] Menjalankan migrasi dan seeder...
php artisan migrate --seed
if errorlevel 1 goto failed
echo.
echo [3/3] Membersihkan cache...
php artisan optimize:clear
if errorlevel 1 goto failed
echo.
echo Setup selesai. Jalankan START-LOCALHOST.bat untuk akses hanya di komputer ini.
pause
exit /b 0
:failed
echo.
echo Setup terhenti. Periksa pesan error di atas.
pause
exit /b 1
