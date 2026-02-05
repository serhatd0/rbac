<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\Env;
use App\Database\Connection;

Env::load(__DIR__ . '/../.env');
$db = Connection::get();

$email = 'admin@example.com';
$inputPass = 'secret123';

$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
if ($user) {
    echo "Hash in DB: " . $user['password_hash'] . "\n";
    echo "Verify result: " . (password_verify($inputPass, $user['password_hash']) ? 'TRUE' : 'FALSE') . "\n";
    
    // Test generating new hash
    echo "New Hash for comparison: " . password_hash($inputPass, PASSWORD_BCRYPT) . "\n";
}
