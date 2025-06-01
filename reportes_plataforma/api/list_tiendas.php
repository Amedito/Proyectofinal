<?php
require '../config/db.php';
$data = $pdo->query("SELECT DISTINCT tienda FROM reportes ORDER BY tienda")->fetchAll(PDO::FETCH_COLUMN);
header('Content-Type: application/json');
echo json_encode($data);
