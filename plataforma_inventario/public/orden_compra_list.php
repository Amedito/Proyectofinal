<?php
// public/orden_compra_list.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();
$ordenes = obtenerOrdenesCompra($pdo);
?>

<div class="container mt-4">
  <h2>Órdenes de Compra</h2>

  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success">
      <?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <a href="orden_compra_add.php" class="btn btn-primary">Agregar Nueva Orden de Compra</a>
  </div>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Número de Orden</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th>Total</th>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($ordenes)): ?>
        <tr>
          <td colspan="9" class="text-center">No hay órdenes de compra registradas.</td>
        </tr>
      <?php else: ?>
        <?php $contador = 1; ?>
        <?php foreach ($ordenes as $fila): ?>
          <tr>
            <td><?= $contador++; ?></td>
            <td><?= htmlspecialchars($fila['numero_orden']); ?></td>
            <td><?= htmlspecialchars($fila['fecha_orden']); ?></td>
            <td><?= htmlspecialchars($fila['proveedor']); ?></td>
            <td><?= number_format((float)$fila['total'], 2); ?></td>
            <td><?= htmlspecialchars($fila['producto'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($fila['cantidad'] ?? '-'); ?></td>
            <td><?= isset($fila['precio_unitario']) ? number_format((float)$fila['precio_unitario'], 2) : '-'; ?></td>
            <td>
              <a href="orden_compra_edit.php?id=<?= $fila['orden_id']; ?>" class="btn btn-sm btn-warning">Editar</a>
              <a href="orden_compra_delete.php?id=<?= $fila['orden_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta orden de compra?');">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
