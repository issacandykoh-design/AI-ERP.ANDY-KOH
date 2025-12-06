<?php

$host = '35.185.187.107';
$db   = 'hub.craveva.com';
$user = 'hubcraveva';
$pass = '986L$o_}?tg-yeH|';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $stmt = $pdo->query("SELECT count(*) as count FROM users");
    $row = $stmt->fetch();
    echo "Users count in Cloud SQL: " . $row['count'] . "\n";
    
    if ($row['count'] == 0) {
        echo "WARNING: No users found. You cannot log in.\n";
        
        // Check if we have a default admin seeder or if we should create one
        echo "Checking if there is a super admin role...\n";
        $stmt = $pdo->query("SELECT * FROM roles WHERE name = 'admin' OR name = 'superadmin' OR name = 'Super Admin'");
        $roles = $stmt->fetchAll();
        print_r($roles);
    } else {
        // Show the first user email to confirm
        $stmt = $pdo->query("SELECT email, name FROM users LIMIT 1");
        $user = $stmt->fetch();
        echo "First user: " . $user['email'] . " (" . $user['name'] . ")\n";
    }

} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
