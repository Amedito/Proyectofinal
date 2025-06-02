<?php
// public/inventario_delete.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

$pdo = conectarDB();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: inventario_list.php');
    exit();
}

$id = intval($_GET['id']);

// Eliminar el registro de inventario
if (eliminarInventario($pdo, $id)) {
    // Reordenar IDs de la tabla productos para mantener correlativo
    reordenarIDsProductos($pdo);

    $_SESSION['mensaje_exito'] = 'Producto eliminado correctamente y IDs resecueciados.';
} else {
    $_SESSION['mensaje_error'] = 'Error al eliminar el producto del inventario.';
}

header('Location: inventario_list.php');
exit();
?>
