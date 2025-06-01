<?php
// public/factura_add.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// 1) Verificar si vienen datos de edición (GET?id=XX)
$editMode = false;
$factura = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editMode = true;
    $id = (int)$_GET['id'];
    $factura = obtenerFacturaPorID($pdo, $id);
    if (!$factura) {
        header('Location: factura_list.php');
        exit();
    }
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Capturar valores del formulario
    $numero_factura = trim($_POST['numero_factura'] ?? '');
    $cliente        = trim($_POST['cliente'] ?? '');
    $total          = (float)($_POST['total'] ?? 0);
    $fecha          = trim($_POST['fecha'] ?? ''); // "YYYY-MM-DD"
    $nit            = trim($_POST['nit'] ?? '');

    // 3) Validaciones
    if ($numero_factura === '') {
        $errores[] = 'El número de factura es obligatorio.';
    }
    if ($cliente === '') {
        $errores[] = 'El nombre del cliente es obligatorio.';
    }
    if ($total <= 0) {
        $errores[] = 'El total debe ser mayor a 0.';
    }
    if ($fecha === '') {
        $errores[] = 'La fecha de factura es obligatoria.';
    }
    if ($nit === '') {
        $errores[] = 'El NIT es obligatorio.';
    }

    if (empty($errores)) {
        // Concatenamos la fecha con "00:00:00" para DATETIME
        $fechaDatetime = $fecha . ' 00:00:00';

        if ($editMode) {
            // 4.a) Actualizar: ahora incluimos el parámetro $nit
            $ok = actualizarFactura(
                $pdo,
                $id,
                $numero_factura,
                $fechaDatetime,
                $cliente,
                $total,
                $nit
            );
            if ($ok) {
                $_SESSION['mensaje_exito'] = 'Factura actualizada correctamente.';
                header('Location: factura_list.php');
                exit();
            } else {
                $errores[] = 'Error al actualizar la factura.';
            }
        } else {
            // 4.b) Agregar nueva: pasamos $nit también
            $ok = agregarFactura(
                $pdo,
                $numero_factura,
                $fechaDatetime,
                $cliente,
                $total,
                $nit
            );
            if ($ok) {
                $_SESSION['mensaje_exito'] = 'Factura agregada correctamente.';
                header('Location: factura_list.php');
                exit();
            } else {
                $errores[] = 'Error al guardar la factura.';
            }
        }
    }
}
?>

<div class="container mt-4">
  <h2><?= $editMode ? "Editar Factura (ID: {$factura['id']})" : "Nueva Factura" ?></h2>
  
  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="<?= $editMode ? "factura_add.php?id={$factura['id']}" : 'factura_add.php' ?>" method="post" class="needs-validation" novalidate>
    <div class="row g-3">
      <!-- Número de Factura -->
      <div class="col-md-6">
        <label for="numero_factura" class="form-label">Número de Factura</label>
        <input
          type="text"
          id="numero_factura"
          name="numero_factura"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['numero_factura'] ?? $factura['numero_factura'] ?? '') ?>"
        >
        <div class="invalid-feedback">Ingresa el número de factura.</div>
      </div>
      <!-- Cliente -->
      <div class="col-md-6">
        <label for="cliente" class="form-label">Cliente</label>
        <input
          type="text"
          id="cliente"
          name="cliente"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['cliente'] ?? $factura['cliente'] ?? '') ?>"
        >
        <div class="invalid-feedback">Ingresa el nombre del cliente.</div>
      </div>
      <!-- Total (Precio) -->
      <div class="col-md-3">
        <label for="total" class="form-label">Total (Q)</label>
        <input
          type="number"
          id="total"
          name="total"
          class="form-control"
          step="0.01"
          min="0"
          required
          value="<?= htmlspecialchars($_POST['total'] ?? $factura['total'] ?? '') ?>"
        >
        <div class="invalid-feedback">Ingresa un total válido (> 0).</div>
      </div>
      <!-- Fecha (solo fecha, sin hora) -->
      <div class="col-md-3">
        <label for="fecha" class="form-label">Fecha</label>
        <input
          type="date"
          id="fecha"
          name="fecha"
          class="form-control"
          required
          value="<?= htmlspecialchars(
            $_POST['fecha'] 
            ?? (isset($factura['fecha_factura']) ? substr($factura['fecha_factura'], 0, 10) : '')
          ) ?>"
        >
        <div class="invalid-feedback">Ingresa la fecha de la factura.</div>
      </div>
      <!-- NIT -->
      <div class="col-md-6">
        <label for="nit" class="form-label">NIT</label>
        <input
          type="text"
          id="nit"
          name="nit"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['nit'] ?? $factura['nit'] ?? '') ?>"
        >
        <div class="invalid-feedback">Ingresa el NIT.</div>
      </div>
      <!-- Botones -->
      <div class="col-12">
        <button type="submit" class="btn btn-success"><?= $editMode ? 'Actualizar' : 'Guardar' ?></button>
        <a href="factura_list.php" class="btn btn-secondary">Cancelar</a>
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
