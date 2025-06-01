<?php
// public/factura_list.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// Obtener todas las facturas ordenadas por ID ascendente
$fac = obtenerFacturas($pdo);
?>

<!-- Estilos para ocultar controles (botones y checkboxes) al imprimir -->
<style>
  @media print {
    .no-print { display: none !important; }
  }
</style>

<div class="container mt-4">
  <h2>Facturas</h2>

  <!-- Botones superiores (ocultos en impresión) -->
  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
      <button id="btnEdit" class="btn btn-warning me-2" disabled>Editar</button>
      <button id="btnDelete" class="btn btn-danger me-2" disabled>Borrar</button>
      <button id="btnPrint" class="btn btn-secondary">Imprimir PDF</button>
    </div>
    <div>
      <a href="factura_add.php" class="btn btn-primary">Nueva Factura</a>
    </div>
  </div>

  <!-- Tabla responsiva de Facturas -->
  <div class="table-responsive">
    <table id="tablaFacturas" class="table table-striped table-hover">
      <thead class="table-light">
        <tr>
          <th class="no-print"><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>Número de Factura</th>
          <th>Cliente</th>
          <th>Total (Q)</th>
          <th>Fecha</th>
          <th>NIT</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fac as $f): ?>
          <?php
            // Quitar la parte de hora: campo 'fecha_factura' es DATETIME
            $fechaSolo = substr($f['fecha_factura'], 0, 10); // "YYYY-MM-DD"
          ?>
          <tr>
            <td class="no-print">
              <input type="checkbox" class="select-row" data-id="<?= $f['id'] ?>">
            </td>
            <td><?= $f['id'] ?></td>
            <td><?= htmlspecialchars($f['numero_factura']) ?></td>
            <td><?= htmlspecialchars($f['cliente']) ?></td>
            <!-- Mostrar "Total (Q)" -->
            <td>Q <?= number_format($f['total'], 2) ?></td>
            <td><?= htmlspecialchars($fechaSolo) ?></td>
            <td><?= htmlspecialchars($f['nit']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- Script JS para manejar checkboxes, Editar, Borrar e Imprimir -->
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
      rowCheckboxes.forEach(cb => { cb.checked = selectAllCheckbox.checked; });
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

  // Editar: redirigir a factura_add.php?id=XX
  btnEdit.addEventListener('click', function() {
    const checked = document.querySelectorAll('.select-row:checked');
    if (checked.length === 1) {
      const id = checked[0].getAttribute('data-id');
      window.location.href = 'factura_add.php?id=' + id;
    }
  });

  // Borrar: confirmar y redirigir a factura_delete.php?ids[]=XX&ids[]=YY
  btnDelete.addEventListener('click', function() {
    const selected = document.querySelectorAll('.select-row:checked');
    if (selected.length === 0) return;
    if (!confirm('¿Seguro que deseas eliminar las facturas seleccionadas? Esto reordenará los IDs.')) {
      return;
    }
    const ids = Array.from(selected).map(ch => ch.getAttribute('data-id'));
    const params = ids.map(id => 'ids[]=' + encodeURIComponent(id)).join('&');
    window.location.href = 'factura_delete.php?' + params;
  });

  // Imprimir: solo tabla y encabezado
  btnPrint.addEventListener('click', function() {
    window.print();
  });
});
</script>
