<?php
// public/empleados_list.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();
// Obtener todos los empleados (ordenados por ID ascendente)
$emps = obtenerEmpleados($pdo);

// Definir cuántos días de vacaciones totales otorga cada año
define('DIAS_POR_ANIO', 15);
?>

<!-- Estilos para ocultar elementos en la vista de impresión -->
<style>
  @media print {
    .no-print { display: none !important; }
  }
  /* Ajustes de ancho de columnas */
  #tablaEmpleados th:nth-child(3) { /* Nombre */
    width: 20%;
  }
  #tablaEmpleados th:nth-child(6) { /* Fecha Contratación */
    width: 10%;
  }
</style>

<div class="container mt-4">
  <h2>Empleados</h2>

  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
      <button id="btnEdit" class="btn btn-warning me-2" disabled>Editar</button>
      <button id="btnDelete" class="btn btn-danger me-2" disabled>Borrar</button>
      <button id="btnPrint" class="btn btn-secondary">Imprimir PDF</button>
    </div>
    <div>
      <a href="vacaciones.php" class="btn btn-info me-2">Vacaciones</a>
      <a href="empleados_add.php" class="btn btn-primary">Nuevo Empleado</a>
    </div>
  </div>

  <div class="table-responsive">
    <table id="tablaEmpleados" class="table table-striped table-hover">
      <thead class="table-light">
        <tr>
          <th class="no-print"><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>Nombre</th>
          <th>Puesto</th>
          <th>Email</th>
          <th>Fecha Contratación</th>
          <th>Días Acumulados</th>
          <th>Días Tomados</th>
          <th>Días Disponibles</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($emps as $e): ?>
          <?php
            $acumulados = calcularDiasVacacionesAcumulados($e['fecha_contratacion']);
            $tomados     = obtenerDiasTomados($pdo, (int)$e['id']);
            $disponibles = $acumulados - $tomados;
            if ($disponibles < 0) {
                $disponibles = 0;
            }
          ?>
          <tr>
            <td class="no-print">
              <input type="checkbox" class="select-row" data-id="<?= $e['id'] ?>">
            </td>
            <td><?= $e['id'] ?></td>
            <td><?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) ?></td>
            <td><?= htmlspecialchars($e['puesto']) ?></td>
            <td><?= htmlspecialchars($e['email']) ?></td>
            <td><?= htmlspecialchars($e['fecha_contratacion']) ?></td>
            <td><?= $acumulados ?> días</td>
            <td><?= $tomados ?> días</td>
            <td><?= $disponibles ?> días</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const selectAllCheckbox = document.getElementById('selectAll');
  const rowCheckboxes     = document.querySelectorAll('.select-row');
  const btnEdit           = document.getElementById('btnEdit');
  const btnDelete         = document.getElementById('btnDelete');
  const btnPrint          = document.getElementById('btnPrint');

  function actualizarBotones() {
    const selected = document.querySelectorAll('.select-row:checked');
    btnDelete.disabled = selected.length === 0;
    btnEdit.disabled   = selected.length !== 1;
  }

  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      rowCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
      actualizarBotones();
    });
  }

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

  btnEdit.addEventListener('click', function() {
    const checked = document.querySelectorAll('.select-row:checked');
    if (checked.length === 1) {
      const id = checked[0].getAttribute('data-id');
      window.location.href = 'empleados_edit.php?id=' + id;
    }
  });

  btnDelete.addEventListener('click', function() {
    const selected = document.querySelectorAll('.select-row:checked');
    if (selected.length === 0) return;
    if (!confirm('¿Seguro que deseas eliminar los empleados seleccionados? Esto reordenará los IDs.')) {
      return;
    }
    const ids = Array.from(selected).map(ch => ch.getAttribute('data-id'));
    const params = ids.map(id => 'ids[]=' + encodeURIComponent(id)).join('&');
    window.location.href = 'empleados_delete.php?' + params;
  });

  btnPrint.addEventListener('click', function() {
    window.print();
  });
});
</script>
