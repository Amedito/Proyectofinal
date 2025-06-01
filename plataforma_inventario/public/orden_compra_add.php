<?php
// public/orden_compra_add.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// 5) Determinar si estamos en modo edición (GET?id=XX)
$editMode = false;
$orden    = null;
$detalles = [];

// Si reciben un ID por GET, cargamos la orden y sus detalles
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editMode = true;
    $id = (int)$_GET['id'];
    $orden = obtenerOrdenCompraPorID($pdo, $id);
    if ($orden) {
        $detalles = obtenerOrdenCompraDetalles($pdo, $id);
    } else {
        // Si no existe, volvemos a la lista
        header('Location: orden_compra_list.php');
        exit();
    }
}

// Obtenemos todos los productos para el select (nombre, precio y stock)
$productos = obtenerProductos($pdo);

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 8–9) Capturar datos del formulario
    $numero_orden = trim($_POST['numero_orden'] ?? '');
    $proveedor    = trim($_POST['proveedor'] ?? '');
    $producto_id  = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
    $cantidad     = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
    $fecha        = trim($_POST['fecha'] ?? ''); // "YYYY-MM-DD"

    // 8) Validaciones: número de orden, proveedor, producto, cantidad, fecha
    if ($numero_orden === '') {
        $errores[] = 'El número de orden es obligatorio.';
    }
    if ($proveedor === '') {
        $errores[] = 'El nombre del proveedor es obligatorio.';
    }
    if ($producto_id <= 0) {
        $errores[] = 'Debes seleccionar un producto.';
    }
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad solicitada debe ser mayor a 0.';
    }
    if ($fecha === '') {
        $errores[] = 'La fecha de la orden es obligatoria.';
    }

    // 9) Validar stock: obtenemos el producto de inventario
    $prodInfo = null;
    if ($producto_id > 0) {
        $prodInfo = obtenerProductoPorID($pdo, $producto_id);
        if (!$prodInfo) {
            $errores[] = 'Producto no encontrado.';
        } else {
            if ($cantidad > (int)$prodInfo['cantidad']) {
                $errores[] = "Stock insuficiente. Hay solamente {$prodInfo['cantidad']} unidades disponibles.";
            }
        }
    }

    if (empty($errores)) {
        // Concatenamos la fecha con hora "00:00:00"
        $fechaDatetime = $fecha . ' 00:00:00';
        // Precio unitario obtenido desde inventario
        $precio_unitario = (float)$prodInfo['precio'];
        // Total = cantidad × precio_unitario
        $total = $cantidad * $precio_unitario;

        if ($editMode) {
            // 5) MODO EDICIÓN: Actualizar cabecera y detalles
            $okCab = actualizarOrdenCompra(
                $pdo,
                $orden['id'],
                $numero_orden,
                $fechaDatetime,
                $proveedor,
                $total
            );
            // Primero eliminamos todos los detalles antiguos
            eliminarOrdenCompraDetallesPorOrden($pdo, $orden['id']);
            // Insertamos un nuevo detalle (en esta versión básica, solo un producto/una fila)
            $okDet = agregarOrdenCompraDetalle(
                $pdo,
                $orden['id'],
                $producto_id,
                $cantidad,
                $precio_unitario
            );
            // Reducir stock en inventario: restamos la diferencia
            // Para simplicidad, asumimos que antes en detalles anteriores había X unidades
            // y ahora solicitamos Y unidades. Aquí, como borramos todo y volvimos a insertar,
            // la forma sencilla es: restar Y (cantidad) del stock actual y luego
            // (si hubiera stock aplicado antes) habría que devolver el stock anterior.
            // Para no complicar, asumimos que la edición reemplaza 100% el detalle y restamos Y:
            $nuevoStock = $prodInfo['cantidad'] - $cantidad;
            actualizarStockProducto($pdo, $producto_id, $nuevoStock);

            if ($okCab && $okDet) {
                $_SESSION['mensaje_exito'] = 'Orden actualizada correctamente.';
                header('Location: orden_compra_list.php');
                exit();
            } else {
                $errores[] = 'Error al actualizar la orden.';
            }
        } else {
            // 5) MODO NUEVA ORDEN: Insertar cabecera y detalle
            $newId = agregarOrdenCompra(
                $pdo,
                $numero_orden,
                $fechaDatetime,
                $proveedor,
                $total
            );
            // Insertar detalle
            $okDet = agregarOrdenCompraDetalle(
                $pdo,
                $newId,
                $producto_id,
                $cantidad,
                $precio_unitario
            );
            // 9) Reducir stock en inventario
            $nuevoStock = $prodInfo['cantidad'] - $cantidad;
            actualizarStockProducto($pdo, $producto_id, $nuevoStock);

            if ($newId > 0 && $okDet) {
                $_SESSION['mensaje_exito'] = 'Orden creada correctamente.';
                header('Location: orden_compra_list.php');
                exit();
            } else {
                $errores[] = 'Error al guardar la orden.';
            }
        }
    }
}
?>

<div class="container mt-4">
  <h2><?= $editMode ? "Editar Orden (ID: {$orden['id']})" : "Nueva Orden de Compra" ?></h2>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="<?= $editMode ? "orden_compra_add.php?id={$orden['id']}" : 'orden_compra_add.php' ?>" method="post" class="needs-validation" novalidate>
    <div class="row g-3">
      <!-- Número de Orden -->
      <div class="col-md-6">
        <label for="numero_orden" class="form-label">Número de Orden</label>
        <input
          type="text"
          id="numero_orden"
          name="numero_orden"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['numero_orden'] ?? $orden['numero_orden'] ?? '') ?>"
        >
        <div class="invalid-feedback">Ingresa el número de orden.</div>
      </div>

      <!-- Proveedor -->
      <div class="col-md-6">
        <label for="proveedor" class="form-label">Proveedor</label>
        <input
          type="text"
          id="proveedor"
          name="proveedor"
          class="form-control"
          required
          value="<?= htmlspecialchars($_POST['proveedor'] ?? $orden['proveedor'] ?? '') ?>"
        >
        <div class="invalid-feedback">Ingresa el nombre del proveedor.</div>
      </div>

      <!-- Fecha (solo fecha, sin hora) -->
      <div class="col-md-4">
        <label for="fecha" class="form-label">Fecha</label>
        <input
          type="date"
          id="fecha"
          name="fecha"
          class="form-control"
          required
          value="<?= htmlspecialchars(
            $_POST['fecha'] 
            ?? (isset($orden['fecha_orden']) ? substr($orden['fecha_orden'], 0, 10) : '')
          ) ?>"
        >
        <div class="invalid-feedback">Ingresa la fecha de la orden.</div>
      </div>

      <!-- Producto -->
      <div class="col-md-4">
        <label for="producto_id" class="form-label">Producto</label>
        <select id="producto_id" name="producto_id" class="form-select" required>
          <option value="">-- Selecciona un producto --</option>
          <?php foreach ($productos as $p): ?>
            <option
              value="<?= $p['id'] ?>"
              <?= ((int)($orden['id'] ?? 0) > 0 && !empty($detalles) && $detalles[0]['producto_id'] == $p['id']) ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($p['nombre']) ?> (Stock: <?= $p['cantidad'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">Debes seleccionar un producto.</div>
      </div>

      <!-- Cantidad -->
      <div class="col-md-4">
        <label for="cantidad" class="form-label">Cantidad Solicitada</label>
        <input
          type="number"
          id="cantidad"
          name="cantidad"
          class="form-control"
          min="1"
          required
          value="<?= htmlspecialchars(
            $_POST['cantidad'] 
            ?? (!empty($detalles) ? $detalles[0]['cantidad'] : '')
          ) ?>"
        >
        <div class="invalid-feedback">Ingresa una cantidad válida (> 0).</div>
      </div>

      <!-- Botones -->
      <div class="col-12">
        <button type="submit" class="btn btn-success"><?= $editMode ? 'Actualizar' : 'Guardar' ?></button>
        <a href="orden_compra_list.php" class="btn btn-secondary">Cancelar</a>
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
