<?php
require '../config/db.php';
$tienda     = $_POST['tienda'] ?? '';
$tipo       = $_POST['tipo'] ?? '';
$fecha      = $_POST['fecha'] ?? '';
$hora       = $_POST['hora'] ?? '';
$estado     = $_POST['estado'] ?? '';
$severidad  = $_POST['severidad'] ?? '';
$aplicativo = $_POST['aplicativo'] ?? '';
$circuito   = $_POST['circuito'] ?? '';
$supervisor = $_POST['supervisor'] ?? '';
$detalle    = $_POST['detalle'] ?? '';

$stmt = $pdo->prepare("
    INSERT INTO reportes
    (tienda, tipo, fecha, hora, estado, severidad, aplicativo, circuito, supervisor, detalle)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$ok = $stmt->execute([
    $tienda, $tipo, $fecha, $hora,
    $estado, $severidad, $aplicativo,
    $circuito, $supervisor, $detalle
]);

header('Content-Type: application/json');
echo json_encode(['success' => $ok]);
