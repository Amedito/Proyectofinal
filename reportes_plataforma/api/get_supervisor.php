<?php
require '../config/db.php';
$tienda = $_GET['tienda'] ?? '';
$stmt = $pdo->prepare(
    "SELECT supervisor FROM supervisores WHERE tienda LIKE CONCAT('%', ?, '%') LIMIT 1"
);
$stmt->execute([$tienda]);
header('Content-Type: application/json');
echo json_encode($stmt->fetchColumn());
