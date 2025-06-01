<?php
// public/empleados_delete.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

// Verificar que se reciban IDs por GET (ids[])
if (!empty($_GET['ids']) && is_array($_GET['ids'])) {
    // Eliminar cada empleado sin usar transacción
    foreach ($_GET['ids'] as $id) {
        $idInt = (int)$id;
        if ($idInt > 0) {
            eliminarEmpleado($pdo, $idInt);
        }
    }

    // Reordenar IDs correlativos después de las eliminaciones
    resecuenciarEmpleados($pdo);
}

// Redirigir de vuelta al listado
header("Location: empleados_list.php");
exit();

