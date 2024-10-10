<?php
$host = 'postgres';
$db = 'mydb';
$user = 'myuser';
$pass = 'mypassword';
$dsn = "pgsql:host=$host;dbname=$db";
try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "Connected to PostgreSQL successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
