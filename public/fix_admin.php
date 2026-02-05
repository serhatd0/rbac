<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\Env;
use App\Database\Connection;

Env::load(__DIR__ . '/../.env');
$db = Connection::get();

$password = 'secret123';
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@example.com'");
$stmt->execute([$hash]);

echo "Admin password reset to 'secret123'. Hash: " . $hash . "\n";
echo "Verify immediately: " . (password_verify($password, $hash) ? 'TRUE' : 'FALSE');
