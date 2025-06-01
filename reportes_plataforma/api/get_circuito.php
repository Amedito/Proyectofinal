<?php
require '../config/db.php';
$app = $_GET['app'] ?? '';
$stmt = $pdo->prepare(
    "SELECT circuito FROM circuitos WHERE aplicativo = ?"
);
$stmt->execute([$app]);
header('Content-Type: application/json');
echo json_encode($stmt->fetchColumn());
