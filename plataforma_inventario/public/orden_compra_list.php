<?php
// public/orden_compra_list.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// 7) Listar órdenes de compra en orden ascendente (ID 1 arriba)
$ordenes = obtenerOrdenesCompra($pdo);
?>

<!-- Ocultar botones/check al imprimir -->
<style>
  @media print {
    .no-print { display: none !important; }
  }
</style>

<div class="container mt-4">
  <h2>Órdenes de Compra</h2>

  <!-- 4) Botones Editar, Borrar, Imprimir PDF (ocultos en impresión) -->
  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
      <button id="btnEdit" class="btn btn-warning me-2" disabled>Editar</button>
      <button id="btnDelete" class="btn btn-danger me-2" disabled>Borrar</button>
      <button id="btnPrint" class="btn btn-secondary">Imprimir PDF</button>
    </div>
    <div>
      <a href="orden_compra_add.php" class="btn btn-primary">Nueva Orden</a>
    </div>
  </div>

  <!-- Tabla responsiva de Órdenes de Compra -->
  <div class="table-responsive">
    <table id="tablaOrdenes" class="table table-striped table-hover">
      <thead class="table-light">
        <tr>
          <th class="no-print"><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>Número de Orden</th>
          <th>Proveedor</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Precio Unitario (Q)</th>
          <th>Total (Q)</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ordenes as $o): ?>
          <?php
            // 3) Quitar la hora de fecha_orden (DATETIME)
            $fechaSolo = substr($o['fecha_orden'], 0, 10);

            // 8–9) Obtener detalles para calcular Cantidad, Precio y Nombre de Producto
            $detalles = obtenerOrdenCompraDetalles($pdo, (int)$o['id']);

            // Inicializamos variables
            $cantTotal = 0;
            $subtotal   = 0.0;
            $productoNombre = '';

            if (!empty($detalles)) {
                // Asumimos un solo producto por orden (el primero)
                $firstDet = $detalles[0];
                $productoNombre = $firstDet['producto_nombre'];
                $cantTotal = (int)$firstDet['cantidad'];
                $precioUnitario = (float)$firstDet['precio_unitario'];
                $subtotal = $precioUnitario * $cantTotal;
            } else {
                // Si no hay detalles, mantenemos en cero
                $precioUnitario = 0.0;
            }
          ?>
          <tr>
            <td class="no-print">
              <input type="checkbox" class="select-row" data-id="<?= $o['id'] ?>">
            </td>
            <td><?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['numero_orden']) ?></td>
            <td><?= htmlspecialchars($o['proveedor']) ?></td>
            <!-- Nuevo: Columna Producto -->
            <td><?= htmlspecialchars($productoNombre) ?></td>
            <!-- 8) Cantidad total (solo la del primer detalle si existe) -->
            <td><?= $cantTotal ?></td>
            <!-- 1) Precio Unitario con Q -->
            <td>Q <?= number_format($precioUnitario, 2) ?></td>
            <!-- 1) Total con Q -->
            <td>Q <?= number_format($o['total'], 2) ?></td>
            <!-- 3) Fecha sin hora -->
            <td><?= htmlspecialchars($fechaSolo) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- 4) Script JS para manejar selección, Editar, Borrar e Imprimir -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const selectAllCheckbox = document.getElementById('selectAll');
  const rowCheckboxes     = document.querySelectorAll('.select-row');
  const btnEdit           = document.getElementById('btnEdit');
  const btnDelete         = document.getElementById('btnDelete');
  const btnPrint          = document.getElementById('btnPrint');

  // Habilita/Deshabilita botones según selección
  function actualizarBotones() {
    const selected = document.querySelectorAll('.select-row:checked');
    btnDelete.disabled = (selected.length === 0);
    btnEdit.disabled   = (selected.length !== 1);
  }

  // Seleccionar/deseleccionar todas las filas
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      rowCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
      actualizarBotones();
    });
  }

  // Cada checkbox individual
  rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      if (!this.checked) {
        selectAllCheckbox.checked = false;
      } else {
        const allChecked = Array.from(rowCheckboxes).every(ch => ch.checked);
        selectAllCheckbox.checked = allChecked;
      }
      actualizarBotones();
    });
  });

  // Editar: redirigir a orden_compra_add.php?id=XX
  btnEdit.addEventListener('click', function() {
    const checked = document.querySelectorAll('.select-row:checked');
    if (checked.length === 1) {
      const id = checked[0].getAttribute('data-id');
      window.location.href = 'orden_compra_add.php?id=' + id;
    }
  });

  // Borrar: confirmar y redirigir a orden_compra_delete.php?ids[]=XX&ids[]=YY
  btnDelete.addEventListener('click', function() {
    const selected = document.querySelectorAll('.select-row:checked');
    if (selected.length === 0) return;
    if (!confirm('¿Seguro que deseas eliminar las órdenes seleccionadas? Esto reordenará los IDs.')) {
      return;
    }
    const ids = Array.from(selected).map(ch => ch.getAttribute('data-id'));
    const params = ids.map(id => 'ids[]=' + encodeURIComponent(id)).join('&');
    window.location.href = 'orden_compra_delete.php?' + params;
  });

  // Imprimir: solo tabla y encabezado
  btnPrint.addEventListener('click', function() {
    window.print();
  });
});
</script>
