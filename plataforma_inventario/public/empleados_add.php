<?php
// public/empleados_add.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre             = trim($_POST['nombre'] ?? '');
    $apellido           = trim($_POST['apellido'] ?? '');
    $puesto             = trim($_POST['puesto'] ?? '');
    $email              = trim($_POST['email'] ?? '');
    $fecha_contratacion = trim($_POST['fecha_contratacion'] ?? '');
    $salario            = trim($_POST['salario'] ?? '');

    if (
        $nombre === '' ||
        $apellido === '' ||
        $puesto === '' ||
        $email === '' ||
        $fecha_contratacion === '' ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_contratacion) ||
        !is_numeric($salario)
    ) {
        $_SESSION['mensaje_error'] = 'Por favor complete todos los campos correctamente.';
    } else {
        $salario = (float)$salario;
        $ok = agregarEmpleado($pdo, $nombre, $apellido, $puesto, $email, $fecha_contratacion, $salario);
        if ($ok) {
            $_SESSION['mensaje_exito'] = 'Empleado agregado correctamente.';
            header('Location: empleados_list.php');
            exit();
        } else {
            $_SESSION['mensaje_error'] = 'Error al agregar el empleado.';
        }
    }
}
?>

<div class="container mt-4">
  <h2>Nuevo Empleado</h2>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <form action="empleados_add.php" method="post" class="needs-validation" novalidate>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input
          type="text"
          id="nombre"
          name="nombre"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese el nombre.
        </div>
      </div>
      <div class="col-md-6">
        <label for="apellido" class="form-label">Apellido</label>
        <input
          type="text"
          id="apellido"
          name="apellido"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese el apellido.
        </div>
      </div>

      <div class="col-md-6">
        <label for="puesto" class="form-label">Puesto</label>
        <input
          type="text"
          id="puesto"
          name="puesto"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['puesto'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese el puesto.
        </div>
      </div>

      <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese un email válido.
        </div>
      </div>

      <div class="col-md-6">
        <label for="fecha_contratacion" class="form-label">Fecha Contratación</label>
        <input
          type="date"
          id="fecha_contratacion"
          name="fecha_contratacion"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['fecha_contratacion'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Seleccione la fecha de contratación.
        </div>
      </div>

      <div class="col-md-6">
        <label for="salario" class="form-label">Salario (Q)</label>
        <input
          type="number"
          step="0.01"
          id="salario"
          name="salario"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['salario'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese el salario.
        </div>
      </div>
    </div>

    <button class="btn btn-primary mt-3" type="submit">Guardar</button>
    <a href="empleados_list.php" class="btn btn-secondary mt-3">Cancelar</a>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// Validación de formularios con Bootstrap 5
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
