<?php
// public/vacaciones_handle.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

$pdo = conectarDB();

// Solo procesar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vacaciones.php');
    exit();
}

// Leer datos
$empleado_id      = intval($_POST['empleado_id'] ?? 0);
$dias_solicitados = intval($_POST['dias_tomados'] ?? 0);

// Validar que se haya elegido empleado y un número de días ≥ 1
if ($empleado_id <= 0 || $dias_solicitados < 1) {
    $_SESSION['mensaje_vac_error'] = 'Debes seleccionar un empleado y un número de días válido.';
    header('Location: vacaciones.php');
    exit();
}

// Obtener empleado para calcular días
$empleado = obtenerEmpleadoPorId($pdo, $empleado_id);
if (!$empleado) {
    $_SESSION['mensaje_vac_error'] = 'Empleado no encontrado.';
    header('Location: vacaciones.php');
    exit();
}

// Calcular días disponibles nuevamente en servidor
$disponibles = obtenerDiasVacacionesDisponibles(
    $pdo,
    $empleado_id,
    $empleado['fecha_contratacion']
);

// Si los días solicitados exceden los disponibles, error
if ($dias_solicitados > $disponibles) {
    $_SESSION['mensaje_vac_error'] = "Solo hay $disponibles día(s) disponibles para este empleado.";
    header('Location: vacaciones.php');
    exit();
}

// Insertar registro en vacaciones_tomadas (sin fecha_inicio, ya que la columna no existe)
$stmt = $pdo->prepare("
    INSERT INTO vacaciones_tomadas (empleado_id, dias_tomados)
    VALUES (:empleado_id, :dias_tomados)
");
$stmt->execute([
    ':empleado_id'  => $empleado_id,
    ':dias_tomados' => $dias_solicitados
]);

// Redirigir de vuelta a lista de empleados para ver el cambio
$_SESSION['mensaje_exito'] = 'Vacaciones registradas correctamente.';
header('Location: empleados_list.php');
exit();
?>
