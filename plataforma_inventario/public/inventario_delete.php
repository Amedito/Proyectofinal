<?php
// public/inventario_delete.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

if (isset($_GET['ids']) && is_array($_GET['ids'])) {
    foreach ($_GET['ids'] as $id) {
        eliminarProducto($pdo, (int)$id);
    }
    resecuenciarProductos($pdo);
    $_SESSION['mensaje_exito'] = 'Producto(s) eliminado(s) correctamente.';
}

header('Location: inventario_list.php');
exit;
?>
