<?php
// inventario_edit.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// Obtener el ID vía GET y buscar el producto
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: inventario_list.php');
    exit();
}

$p = obtenerProductoPorID($pdo, $id);
if (!$p) {
    header('Location: inventario_list.php');
    exit();
}

// Si se envió el formulario por POST, procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $cantidad    = (int) ($_POST['cantidad'] ?? 0);
    $precio      = (float) ($_POST['precio'] ?? 0);
    $categoria   = trim($_POST['categoria'] ?? '');

    // Validaciones del lado del servidor
    $errores = [];
    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($cantidad < 0) {
        $errores[] = 'La cantidad no puede ser negativa.';
    }
    if ($precio < 0) {
        $errores[] = 'El precio no puede ser negativo.';
    }

    if (empty($errores)) {
        $ok = actualizarProducto($pdo, $id, $nombre, $descripcion, $cantidad, $precio, $categoria);
        if ($ok) {
            $_SESSION['mensaje_exito'] = 'Producto actualizado correctamente.';
            header('Location: inventario_list.php');
            exit();
        } else {
            $errores[] = 'Ocurrió un error al actualizar en la base de datos.';
        }
    }
}
?>

<div class="container mt-4">
  <h2>Editar Producto (ID: <?= $p['id'] ?>)</h2>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form id="formEditar" class="needs-validation" novalidate action="inventario_edit.php?id=<?= $p['id'] ?>" method="post">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input 
          type="text" 
          id="nombre" 
          name="nombre" 
          class="form-control" 
          required 
          value="<?= htmlspecialchars($p['nombre'] ?? '') ?>"
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
          value="<?= htmlspecialchars($p['descripcion'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          La descripción es opcional, pero si la incluyes, no debe quedar vacía.
        </div>
      </div>

      <div class="col-md-3">
        <label for="cantidad" class="form-label">Cantidad</label>
        <input 
          type="number" 
          id="cantidad" 
          name="cantidad" 
          class="form-control" 
          min="0" 
          required 
          value="<?= (int)$p['cantidad'] ?>"
        >
        <div class="invalid-feedback">
          Ingresa una cantidad válida (0 o más).
        </div>
      </div>

      <div class="col-md-3">
        <label for="precio" class="form-label">Precio (Q)</label>
        <input 
          type="number" 
          step="0.01" 
          id="precio" 
          name="precio" 
          class="form-control" 
          min="0" 
          required 
          value="<?= number_format((float)$p['precio'], 2, '.', '') ?>"
        >
        <div class="invalid-feedback">
          Ingresa un precio válido (0.00 o más).
        </div>
      </div>

      <div class="col-md-6">
        <label for="categoria" class="form-label">Categoría</label>
        <input 
          type="text" 
          id="categoria" 
          name="categoria" 
          class="form-control" 
          value="<?= htmlspecialchars($p['categoria'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          La categoría es opcional, pero si la incluyes, no debe quedar vacía.
        </div>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="inventario_list.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </div>
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
