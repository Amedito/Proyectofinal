<?php
// public/empleados_edit.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// Obtener ID por GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: empleados_list.php');
    exit();
}

// Obtener datos del empleado
$emp = obtenerEmpleadoPorID($pdo, $id);
if (!$emp) {
    header('Location: empleados_list.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $puesto      = trim($_POST['puesto'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $fecha_cont  = trim($_POST['fecha_contratacion'] ?? '');
    $salario     = (float) ($_POST['salario'] ?? 0);

    $errores = [];
    if ($nombre === '' || $apellido === '') {
        $errores[] = 'Nombre y Apellido son obligatorios.';
    }
    if ($puesto === '') {
        $errores[] = 'El puesto es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email no válido.';
    }
    if ($fecha_cont === '') {
        $errores[] = 'La fecha de contratación es obligatoria.';
    }
    if ($salario <= 0) {
        $errores[] = 'El salario debe ser mayor a 0.';
    }

    if (empty($errores)) {
        $ok = actualizarEmpleado($pdo, $id, $nombre, $apellido, $puesto, $email, $fecha_cont, $salario);
        if ($ok) {
            $_SESSION['mensaje_exito'] = 'Empleado actualizado correctamente.';
            header('Location: empleados_list.php');
            exit();
        } else {
            $errores[] = 'Error al actualizar en la base de datos.';
        }
    }
}
?>

<div class="container mt-4">
  <h2>Editar Empleado (ID: <?= $emp['id'] ?>)</h2>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="empleados_edit.php?id=<?= $emp['id'] ?>" method="post" class="needs-validation" novalidate>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input 
          type="text" 
          id="nombre" 
          name="nombre" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($_POST['nombre'] ?? $emp['nombre']) ?>"
        >
        <div class="invalid-feedback">Ingresa el nombre.</div>
      </div>
      <div class="col-md-6">
        <label for="apellido" class="form-label">Apellido</label>
        <input 
          type="text" 
          id="apellido" 
          name="apellido" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($_POST['apellido'] ?? $emp['apellido']) ?>"
        >
        <div class="invalid-feedback">Ingresa el apellido.</div>
      </div>
      <div class="col-md-6">
        <label for="puesto" class="form-label">Puesto</label>
        <input 
          type="text" 
          id="puesto" 
          name="puesto" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($_POST['puesto'] ?? $emp['puesto']) ?>"
        >
        <div class="invalid-feedback">Ingresa el puesto.</div>
      </div>
      <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($_POST['email'] ?? $emp['email']) ?>"
        >
        <div class="invalid-feedback">Ingresa un email válido.</div>
      </div>
      <div class="col-md-6">
        <label for="fecha_contratacion" class="form-label">Fecha de Contratación</label>
        <input 
          type="date" 
          id="fecha_contratacion" 
          name="fecha_contratacion" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($_POST['fecha_contratacion'] ?? $emp['fecha_contratacion']) ?>"
        >
        <div class="invalid-feedback">Ingresa la fecha de contratación.</div>
      </div>
      <div class="col-md-6">
        <label for="salario" class="form-label">Salario (Q)</label>
        <input 
          type="number" 
          id="salario" 
          name="salario" 
          class="form-control" 
          step="0.01" 
          min="0" 
          required 
          value="<?= htmlspecialchars($_POST['salario'] ?? $emp['salario']) ?>"
        >
        <div class="invalid-feedback">Ingresa un salario válido (> 0).</div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="empleados_list.php" class="btn btn-secondary">Cancelar</a>
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
