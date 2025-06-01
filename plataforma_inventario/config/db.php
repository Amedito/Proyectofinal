<?php
// config/db.php
$host   = "localhost";
$dbname = "asenersa_db";
$user   = "root";
$pass   = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la BD: " . $e->getMessage());
}