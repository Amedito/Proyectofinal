<?php
// public/vacaciones.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// Obtenemos lista de empleados para el <select>
$empsDropdown = obtenerEmpleados($pdo);
?>

<div class="container mt-4">
  <h2>Asignar Vacaciones</h2>
  <a href="empleados_list.php" class="btn btn-secondary mb-3">Volver a Empleados</a>

  <?php if (!empty($_SESSION['mensaje_vac_error'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['mensaje_vac_error']) ?>
    </div>
    <?php unset($_SESSION['mensaje_vac_error']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['mensaje_vac_exito'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['mensaje_vac_exito']) ?>
    </div>
    <?php unset($_SESSION['mensaje_vac_exito']); ?>
  <?php endif; ?>

  <form action="vacaciones_handle.php" method="post" class="needs-validation" novalidate>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="empleado_id" class="form-label">Empleado</label>
        <select id="empleado_id" name="empleado_id" class="form-select" required>
          <option value="">-- Selecciona un empleado --</option>
          <?php foreach ($empsDropdown as $emp): ?>
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
        <label for="dias_tomar" class="form-label">Días a Tomar</label>
        <input 
          type="number" 
          id="dias_tomar" 
          name="dias_tomar" 
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
// Validación de Bootstrap para formularios
(function () {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
