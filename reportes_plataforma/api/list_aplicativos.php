<?php
require '../config/db.php';
$data = $pdo->query("SELECT DISTINCT aplicativo FROM reportes ORDER BY aplicativo")->fetchAll(PDO::FETCH_COLUMN);
header('Content-Type: application/json');
echo json_encode($data);
