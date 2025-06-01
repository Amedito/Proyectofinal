<?php // index.php ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reportes Diarios</title>
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header>
    <h1>Bienvenido a Reportes Diarios</h1>
  </header>
  <main>
    <div class="meses">
      <?php
      $meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
      foreach ($meses as $i => $m) {
          $clase = ($m === 'Abril') ? 'activo' : 'inactivo';
          echo "<button class='mes $clase' data-mes='" . ($i+1) . "'>$m</button>";
      }
      ?>
    </div>
    <section id="reporte"></section>
  </main>

  <!-- jQuery y DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

  <script src="assets/js/main.js"></script>
  <script src="assets/js/tablesort.js"></script>
</body>
</html>



