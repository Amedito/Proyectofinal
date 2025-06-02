<?php
// public/orden_compra_add.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = conectarDB();

// Procesar formulario de “Nueva Orden de Compra”
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_orden    = trim($_POST['numero_orden'] ?? '');
    $fecha_orden     = trim($_POST['fecha_orden'] ?? '');
    $proveedor       = trim($_POST['proveedor'] ?? '');
    $producto_id     = intval($_POST['producto_id'] ?? 0);
    $cantidad        = intval($_POST['cantidad'] ?? 0);

    if (
        $numero_orden === '' ||
        $fecha_orden === '' ||
        $proveedor === '' ||
        $producto_id <= 0 ||
        $cantidad <= 0
    ) {
        $_SESSION['mensaje_error'] = 'Complete todos los campos obligatorios, y seleccione un producto válido.';
    } else {
        // Obtener precio actual del producto desde la base
        $producto = obtenerProductoPorId($pdo, $producto_id);
        if (!$producto) {
            $_SESSION['mensaje_error'] = 'Producto no encontrado.';
        } elseif ($cantidad > $producto['cantidad']) {
            $_SESSION['mensaje_error'] = 'La cantidad solicitada excede el stock disponible (' . $producto['cantidad'] . ').';
        } else {
            $precio_unitario = floatval($producto['precio']);
            $total = $precio_unitario * $cantidad;

            // 1) Insertar encabezado de orden
            $orden_id = agregarOrdenCompra($pdo, $numero_orden, $fecha_orden, $proveedor, $total);

            if ($orden_id > 0) {
                // 2) Insertar el único detalle y reducir stock
                agregarOrdenCompraDetalle($pdo, $orden_id, $producto_id, $cantidad, $precio_unitario);
                $_SESSION['mensaje_exito'] = 'Orden de compra creada correctamente.';
                header('Location: orden_compra_list.php');
                exit();
            } else {
                $_SESSION['mensaje_error'] = 'Error al crear la orden de compra.';
            }
        }
    }
}

// Obtener lista de productos con su stock y precio actual
$productos_disponibles = obtenerProductos($pdo);
?>
<div class="container mt-4">
  <h2>Nueva Orden de Compra</h2>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <form id="formOrden" class="needs-validation" novalidate action="orden_compra_add.php" method="post">
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="numero_orden" class="form-label">Número de Orden</label>
        <input
          type="text"
          id="numero_orden"
          name="numero_orden"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['numero_orden'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese el número de orden.
        </div>
      </div>
      <div class="col-md-4">
        <label for="fecha_orden" class="form-label">Fecha</label>
        <input
          type="date"
          id="fecha_orden"
          name="fecha_orden"
          class="form-control"
          required
          value="<?= ($_POST['fecha_orden'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Seleccione la fecha de la orden.
        </div>
      </div>
      <div class="col-md-4">
        <label for="proveedor" class="form-label">Proveedor</label>
        <input
          type="text"
          id="proveedor"
          name="proveedor"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['proveedor'] ?? '') ?>"
        >
        <div class="invalid-feedback">
          Ingrese el nombre del proveedor.
        </div>
      </div>
    </div>

    <hr>
    <h5>Detalle de Producto</h5>
    <div class="row g-3 align-items-end mb-3">
      <div class="col-md-4">
        <label for="producto_id" class="form-label">Producto</label>
        <select
          id="producto_id"
          name="producto_id"
          class="form-select"
          required
        >
          <option value="">Seleccione...</option>
          <?php foreach ($productos_disponibles as $p): ?>
            <option
              value="<?= $p['id']; ?>"
              data-price="<?= number_format((float)$p['precio'], 2, '.', ''); ?>"
            >
              <?= htmlspecialchars($p['nombre']); ?> (Stock: <?= $p['cantidad']; ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">
          Seleccione un producto.
        </div>
      </div>

      <div class="col-md-3">
        <label for="cantidad" class="form-label">Cantidad</label>
        <input
          type="number"
          id="cantidad"
          name="cantidad"
          class="form-control"
          min="1"
          required
          value="<?= intval($_POST['cantidad'] ?? 0) ?>"
        >
        <div class="invalid-feedback">
          Ingrese la cantidad (mínimo 1).
        </div>
      </div>

      <div class="col-md-3">
        <label for="precio_unitario" class="form-label">Precio Unitario (Q)</label>
        <input
          type="text"
          id="precio_unitario"
          name="precio_unitario"
          class="form-control"
          readonly
          value=""
        >
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Orden</button>
    <a href="orden_compra_list.php" class="btn btn-secondary ms-2">Cancelar</a>
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

// Capturar la selección de producto y auto-llenar el precio unitario
const selectProducto = document.getElementById('producto_id');
const inputPrecio    = document.getElementById('precio_unitario');

selectProducto.addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  const price         = selectedOption.getAttribute('data-price') || '';
  inputPrecio.value   = price;
});
</script>
