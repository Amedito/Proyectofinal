<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';

// Validar que vengan IDs por GET (array ‘ids[]’)
if (!empty($_GET['ids']) && is_array($_GET['ids'])) {
  $pdo->beginTransaction();
  try {
    foreach ($_GET['ids'] as $id) {
      $idInt = (int)$id;
      if ($idInt > 0) {
        eliminarProducto($pdo, $idInt);
      }
    }
    // Tras eliminar, resecuenciar para mantener correlativa la columna 'id'
    resecuenciarProductos($pdo);
    $pdo->commit();
  } catch (Exception $e) {
    $pdo->rollBack();
    // Podrías mostrar un mensaje de error o registrar en logs
  }
}

// Redirigir siempre de vuelta a la lista de inventario
header("Location: inventario_list.php");
exit();
