<?php
// public/empleados_delete.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

$pdo = conectarDB();

// Si llegan múltiples IDs como array (ids[]), procesarlos
if (isset($_GET['ids']) && is_array($_GET['ids'])) {
    $ids = array_map('intval', $_GET['ids']);
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // O bien un solo ID vía id=
    $ids = [ intval($_GET['id']) ];
} else {
    header('Location: empleados_list.php');
    exit();
}

$errores   = 0;
$eliminados = 0;

foreach ($ids as $id) {
    try {
        $res = eliminarEmpleado($pdo, $id);
        if ($res) {
            $eliminados++;
        } else {
            $errores++;
        }
    } catch (Exception $e) {
        $errores++;
    }
}

// Reordenar IDs en la tabla empleados para mantener correlativo
try {
    $pdo->beginTransaction();
    $pdo->exec("SET @count = 0");
    $pdo->exec("
        UPDATE empleados
        SET id = (@count := @count + 1)
        ORDER BY id ASC
    ");
    $pdo->exec("ALTER TABLE empleados AUTO_INCREMENT = 1");
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

// Preparar mensaje según resultado
if ($eliminados && $errores === 0) {
    $_SESSION['mensaje_exito'] = "$eliminados empleado(s) eliminado(s) correctamente.";
} elseif ($eliminados && $errores) {
    $_SESSION['mensaje_exito'] = "$eliminados empleado(s) eliminado(s), pero $errores no se pudieron eliminar.";
} else {
    $_SESSION['mensaje_error'] = 'No se eliminó ningún empleado.';
}

header('Location: empleados_list.php');
exit();
?>
