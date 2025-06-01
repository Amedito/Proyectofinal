<?php
// public/orden_compra_delete.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

// Verificar que se reciban IDs por GET (ids[])
if (!empty($_GET['ids']) && is_array($_GET['ids'])) {
    foreach ($_GET['ids'] as $id) {
        $idInt = (int)$id;
        if ($idInt > 0) {
            // 1) Obtener detalles de la orden para devolver stock
            $detalles = obtenerOrdenCompraDetalles($pdo, $idInt);
            foreach ($detalles as $det) {
                $productoId = (int)$det['producto_id'];
                $cantidad   = (int)$det['cantidad'];

                // Obtener el producto actual para conocer el stock
                $prodInfo = obtenerProductoPorID($pdo, $productoId);
                if ($prodInfo) {
                    $nuevoStock = (int)$prodInfo['cantidad'] + $cantidad;
                    // Devolver al inventario
                    actualizarStockProducto($pdo, $productoId, $nuevoStock);
                }
            }

            // 2) Eliminar detalles asociados a esta orden
            eliminarOrdenCompraDetallesPorOrden($pdo, $idInt);

            // 3) Eliminar la cabecera de la orden
            eliminarOrdenCompra($pdo, $idInt);
        }
    }

    // 4) Reordenar IDs correlativos en la tabla ordenes_compra
    resecuenciarOrdenesCompra($pdo);
}

// Redirigir de vuelta al listado
header("Location: orden_compra_list.php");
exit();
