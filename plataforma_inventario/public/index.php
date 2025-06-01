<?php
// public/index.php

// Incluir el header (Bootstrap CSS y navbar)
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Contenedor principal de la página -->
<div class="container text-center mt-5">
  <h1 class="fw-bold">BIENVENIDO A LA PLATAFORMA DE GRUPO G&amp;M</h1>
  <p class="lead">Selecciona una de las opciones del menú para comenzar.</p>
  <div class="d-flex justify-content-center gap-3 mt-4">
    <a href="inventario_list.php" class="btn btn-primary btn-lg">Inventario</a>
    <a href="empleados_list.php" class="btn btn-secondary btn-lg">Empleados</a>
    <a href="factura_list.php" class="btn btn-success btn-lg">Facturas</a>
    <a href="orden_compra_list.php" class="btn btn-warning btn-lg">Órdenes de compra</a>
  </div>
</div>

<?php
// Incluir el footer (Bootstrap JS)
require_once __DIR__ . '/../includes/footer.php';
?>

