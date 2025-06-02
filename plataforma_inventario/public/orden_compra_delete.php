<?php
// public/orden_compra_delete.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

$pdo = conectarDB();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orden_compra_list.php');
    exit();
}

$orden_id = intval($_GET['id']);

// La función eliminarOrdenCompra() en “models/funciones.php” restaurará automáticamente el stock
if (eliminarOrdenCompra($pdo, $orden_id)) {
    $_SESSION['mensaje_exito'] = 'Orden de compra eliminada y stock restaurado correctamente.';
} else {
    $_SESSION['mensaje_error'] = 'Error al eliminar la orden de compra.';
}

header('Location: orden_compra_list.php');
exit();
?>
