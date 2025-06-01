<?php
// public/factura_delete.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

// Verificar que se reciban IDs por GET (ids[])
if (!empty($_GET['ids']) && is_array($_GET['ids'])) {
    foreach ($_GET['ids'] as $id) {
        $idInt = (int)$id;
        if ($idInt > 0) {
            eliminarFactura($pdo, $idInt);
        }
    }
    // Reordenar IDs correlativos
    resecuenciarFacturas($pdo);
}

// Redirigir de vuelta al listado
header("Location: factura_list.php");
exit();
