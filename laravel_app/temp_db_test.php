<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "DB_USERNAME=" . getenv('DB_USERNAME') . "\n";
echo "DB_PASSWORD=" . getenv('DB_PASSWORD') . "\n";
echo "DB_HOST=" . getenv('DB_HOST') . "\n";
echo "DB_DATABASE=" . getenv('DB_DATABASE') . "\n";

test('127.0.0.1');
test('localhost');

function test($host) {
    echo "\nTesting host: $host\n";
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;dbname=u827349422_kasir_lily;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "Connected as root\n";
    } catch (PDOException $e) {
        echo "Root error: " . $e->getMessage() . "\n";
    }
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;dbname=u827349422_kasir_lily;charset=utf8mb4", 'u827349422_kasir_user', '*rL6lFW2gJ7', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "Connected as kasir user\n";
    } catch (PDOException $e) {
        echo "Kasir user error: " . $e->getMessage() . "\n";
    }
}
