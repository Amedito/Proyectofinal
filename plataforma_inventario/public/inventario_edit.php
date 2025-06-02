<?php
// inventario_edit.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: inventario_list.php');
    exit();
}

// Obtener datos actuales del producto
$producto = obtenerProductoPorId($pdo, $id);
if (!$producto) {
    $_SESSION['mensaje_error'] = 'Producto no encontrado.';
    header('Location: inventario_list.php');
    exit();
}

// Si se envió el formulario por POST, procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $cantidad    = (int) ($_POST['cantidad'] ?? 0);
    $precio      = (float) ($_POST['precio'] ?? 0);
    $categoria   = trim($_POST['categoria'] ?? '');

    if ($nombre === '' || $cantidad < 0 || $precio < 0) {
        $_SESSION['mensaje_error'] = 'Verifique los datos ingresados.';
    } else {
        $ok = actualizarProducto($pdo, $id, $nombre, $descripcion, $precio, $cantidad, $categoria);
        if ($ok) {
            $_SESSION['mensaje_exito'] = 'Producto actualizado correctamente.';
            header('Location: inventario_list.php');
            exit();
        } else {
            $_SESSION['mensaje_error'] = 'Error al actualizar el producto.';
        }
    }
}

?>

<div class="container mt-4">
  <h2>Editar Producto</h2>
  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <form id="formEditar" class="needs-validation" novalidate action="inventario_edit.php?id=<?= $id; ?>" method="post">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input 
          type="text" 
          id="nombre" 
          name="nombre" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($producto['nombre']); ?>"
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
          value="<?= htmlspecialchars($producto['descripcion']); ?>"
        >
        <div class="invalid-feedback">
          La descripción es opcional, pero si la incluyes, no debe quedar vacía.
        </div>
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
          value="<?= (int)$producto['cantidad']; ?>"
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
          value="<?= number_format((float)$producto['precio'], 2, '.', ''); ?>"
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
          value="<?= htmlspecialchars($producto['categoria']); ?>"
        >
        <div class="invalid-feedback">
          La categoría es opcional, pero si la incluyes, no debe quedar vacía.
        </div>
      </div>
    </div>

    <button class="btn btn-primary mt-3" type="submit">Actualizar</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// Bootstrap Validación de Formularios (https://getbootstrap.com/docs/5.0/forms/validation/)
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
