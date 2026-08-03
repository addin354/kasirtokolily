@echo off
cd /d C:\xampp\htdocs\kasirtokolily.id\laravel_app
copy /Y .env .env.bak >nul
if exist .env.tmp del /f /q .env.tmp
rename .env .env.tmp
set "DB_CONNECTION=mysql"
set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_DATABASE=kasir_lily"
set "DB_USERNAME=root"
set "DB_PASSWORD="
php artisan config:clear > migrate_output.txt 2>&1
php artisan migrate --force >> migrate_output.txt 2>&1
rename .env.tmp .env
type migrate_output.txt
