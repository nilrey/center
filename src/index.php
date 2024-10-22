<?php
$host = 'postgres';
$db = 'center';
$user = 'postgres';
$pass = 'postgres';
$dsn = "pgsql:host=$host;dbname=$db";
try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "Connected to PostgreSQL successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
