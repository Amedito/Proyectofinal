<?php
// public/vacaciones.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();

// Obtener lista de empleados
$emps = obtenerEmpleados($pdo);

// Calcular días disponibles para cada empleado
$disponiblesPorEmpleado = [];
foreach ($emps as $emp) {
    $acumulados = calcularDiasVacacionesAcumulados($emp['fecha_contratacion']);
    $tomados    = obtenerDiasTomados($pdo, $emp['id']);
    $disp       = $acumulados - $tomados;
    $disponiblesPorEmpleado[$emp['id']] = ($disp > 0) ? $disp : 0;
}

// Mensaje de error de sesión (si existe)
$error = $_SESSION['mensaje_vac_error'] ?? '';
unset($_SESSION['mensaje_vac_error']);
?>

<div class="container mt-4">
  <h2>Asignar Vacaciones</h2>
  <a href="empleados_list.php" class="btn btn-secondary mb-3">Volver a Empleados</a>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="vacaciones_handle.php" method="post" class="needs-validation" novalidate>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="empleado_id" class="form-label">Empleado</label>
        <select id="empleado_id" name="empleado_id" class="form-select" required>
          <option value="">-- Selecciona un empleado --</option>
          <?php foreach ($emps as $emp): ?>
            <option value="<?= $emp['id'] ?>">
              <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">
          Debes seleccionar un empleado.
        </div>
      </div>

      <div class="col-md-6">
        <label for="dias_disponibles" class="form-label">Días Disponibles</label>
        <input
          type="text"
          id="dias_disponibles"
          class="form-control"
          readonly
          value="—"
        >
      </div>

      <div class="col-md-6">
        <label for="dias_tomados" class="form-label">Días a Tomar</label>
        <input
          type="number"
          id="dias_tomados"
          name="dias_tomados"
          class="form-control"
          min="1"
          required
          placeholder="Ingresa número de días"
        >
        <div class="invalid-feedback">
          Ingresa un número válido de días.
        </div>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// Mapa de días disponibles por empleado
const disponiblesPorEmpleado = <?= json_encode($disponiblesPorEmpleado, JSON_HEX_TAG) ?>;

document.getElementById('empleado_id').addEventListener('change', function() {
  const id = this.value;
  const dispInput = document.getElementById('dias_disponibles');
  dispInput.value = id && disponiblesPorEmpleado[id] != null
    ? disponiblesPorEmpleado[id] + ' días'
    : '—';
});

// Validación de formulario y comparación de días
(function () {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      const empleadoSelect = document.getElementById('empleado_id');
      const diasInput      = document.getElementById('dias_tomados');
      const selectedId     = empleadoSelect.value;
      const diasVal        = parseInt(diasInput.value, 10);

      // Si no se seleccionó empleado o los días no son válidos, cancelar envío
      if (!selectedId || isNaN(diasVal) || diasVal < 1) {
        event.preventDefault();
        event.stopPropagation();
        return;
      }

      // Validar contra días disponibles
      const available = disponiblesPorEmpleado[selectedId] || 0;
      if (diasVal > available) {
        event.preventDefault();
        event.stopPropagation();
        alert('No puedes tomar más días de los disponibles: ' + available);
        return;
      }

      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
