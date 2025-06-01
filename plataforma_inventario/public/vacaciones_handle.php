<?php
// public/vacaciones_handle.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

// 1) Recoger datos del formulario
$empleado_id = isset($_POST['empleado_id']) ? (int)$_POST['empleado_id'] : 0;
$dias_tomar  = isset($_POST['dias_tomar']) ? (int)$_POST['dias_tomar'] : 0;

// 2) Validaciones básicas
if ($empleado_id <= 0 || $dias_tomar <= 0) {
    $_SESSION['mensaje_vac_error'] = 'Datos inválidos. Selecciona empleado y días.';
    header('Location: vacaciones.php');
    exit();
}

// 3) Obtener los datos actuales del empleado (para asegurarnos de que exista)
$emp = obtenerEmpleadoPorID($pdo, $empleado_id);
if (!$emp) {
    $_SESSION['mensaje_vac_error'] = 'Empleado no encontrado.';
    header('Location: vacaciones.php');
    exit();
}

// 4) Calcular cuántos días ya ha tomado desde la tabla vacaciones_tomadas
$tomados     = obtenerDiasTomados($pdo, $empleado_id);
// Días totales por defecto
define('VACACIONES_POR_DEFECTO', 30);
$disponibles = VACACIONES_POR_DEFECTO - $tomados;
if ($disponibles < 0) {
    $disponibles = 0;
}

// 5) Verificar que no exceda
if ($dias_tomar > $disponibles) {
    $_SESSION['mensaje_vac_error'] =
        "El número de días excede las vacaciones disponibles ({$disponibles} días).";
    header('Location: vacaciones.php');
    exit();
}

// 6) Registrar el nuevo registro de vacaciones tomadas
$ok = registrarVacacionesTomadas($pdo, $empleado_id, $dias_tomar);
if ($ok) {
    $_SESSION['mensaje_vac_exito'] = 'Vacaciones asignadas correctamente.';
} else {
    $_SESSION['mensaje_vac_error'] = 'Error al guardar el registro de vacaciones.';
}

// 7) Redirigir nuevamente al formulario
header('Location: vacaciones.php');
exit();

