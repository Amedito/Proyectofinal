<?php
// public/inventario_add.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();

// Procesar formulario de “Agregar Nuevo Producto”
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $cantidad    = (int) ($_POST['cantidad'] ?? 0);
    $precio      = (float) ($_POST['precio'] ?? 0);
    $categoria   = trim($_POST['categoria'] ?? '');

    if ($nombre === '' || $cantidad < 0 || $precio < 0) {
        $_SESSION['mensaje_error'] = 'Verifique los datos ingresados.';
    } else {
        $ok = agregarProducto($pdo, $nombre, $descripcion, $precio, $cantidad, $categoria);
        if ($ok) {
            $_SESSION['mensaje_exito'] = 'Producto agregado correctamente.';
            header('Location: inventario_list.php');
            exit();
        } else {
            $_SESSION['mensaje_error'] = 'Error al agregar el producto.';
        }
    }
}
?>
<div class="container mt-4">
  <h2>Agregar Nuevo Producto</h2>
  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <form id="formAgregar" class="needs-validation" novalidate action="inventario_add.php" method="post">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input 
          type="text" 
          id="nombre" 
          name="nombre" 
          class="form-control" 
          required 
          value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>"
        >
        <div class="invalid-feedback">
          Por favor ingresa el nombre del producto.
        </div>
      </div>

      <div class="col-md-6">
        <label for="descripcion" class="form-label">Descripción</label>
        <input 
          type="text" 
          id="descripcion" 
          name="descripcion" 
          class="form-control" 
          value="<?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?>"
        >
      </div>

      <div class="col-md-6">
        <label for="cantidad" class="form-label">Cantidad</label>
        <input 
          type="number" 
          id="cantidad" 
          name="cantidad" 
          class="form-control" 
          min="0" 
          required 
          value="<?= isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : '0' ?>"
        >
        <div class="invalid-feedback">
          Por favor ingresa la cantidad.
        </div>
      </div>

      <div class="col-md-6">
        <label for="precio" class="form-label">Precio (Q)</label>
        <input 
          type="number" 
          step="0.01" 
          id="precio" 
          name="precio" 
          class="form-control" 
          min="0" 
          required 
          value="<?= isset($_POST['precio']) ? number_format((float)$_POST['precio'], 2, '.', '') : '0.00' ?>"
        >
        <div class="invalid-feedback">
          Por favor ingresa el precio.
        </div>
      </div>

      <div class="col-md-6">
        <label for="categoria" class="form-label">Categoría</label>
        <input 
          type="text" 
          id="categoria" 
          name="categoria" 
          class="form-control" 
          value="<?= isset($_POST['categoria']) ? htmlspecialchars($_POST['categoria']) : '' ?>"
        >
      </div>
    </div>

    <button class="btn btn-primary mt-3" type="submit">Guardar</button>
    <a href="inventario_list.php" class="btn btn-secondary mt-3">Cancelar</a>
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
