@echo off
cd /d C:\xampp\htdocs\kasirtokolily.id\laravel_app
echo TEST START
php -r "echo 'HELLO';"
echo ---
php -r "var_dump(getenv('DB_USERNAME')); var_dump(getenv('DB_PASSWORD')); var_dump(getenv('DB_HOST')); var_dump(getenv('DB_DATABASE'));"
echo ---
php artisan config:clear
echo ---
php artisan env
echo TEST END
