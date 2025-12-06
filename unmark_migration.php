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
    
    // Remove the bad entry we just added
    $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration = ?");
    $stmt->execute(['2022_10_03_080325_create_super_admin_tables_table']);
    
    echo "Removed 2022_10_03_080325_create_super_admin_tables_table from migrations table.\n";

} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage();
}
