<?php
// public/inventario_list.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();

// Reordenar IDs de inventario para mantener correlativo ascendente
reordenarIDsInventario($pdo);

$inventario = obtenerInventario($pdo);
?>

<div class="container mt-4">
  <h2>Inventario</h2>

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
    <a href="inventario_add.php" class="btn btn-primary">Agregar Nuevo Producto</a>
  </div>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio Unitario (Q)</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($inventario)): ?>
        <tr>
          <td colspan="5" class="text-center">No hay productos en inventario.</td>
        </tr>
      <?php else: ?>
        <?php $contador = 1; ?>
        <?php foreach ($inventario as $fila): ?>
          <tr>
            <td><?= $contador++; ?></td>
            <td><?= htmlspecialchars($fila['producto']); ?></td>
            <td><?= htmlspecialchars($fila['cantidad']); ?></td>
            <td><?= number_format((float)$fila['precio_unitario'], 2); ?></td>
            <td>
              <a href="inventario_edit.php?id=<?= $fila['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
              <a href="inventario_delete.php?id=<?= $fila['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto del inventario?');">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
