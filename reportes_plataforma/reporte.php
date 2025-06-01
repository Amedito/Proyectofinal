<?php
require 'config/db.php';
$mes = intval($_GET['mes'] ?? 0);
if ($mes !== 4) {
    echo '<p>Reporte disponible solo para Abril.</p>';
    exit;
}

// Logos
echo '<div class="header-report">';
echo '<img src="logo1.png" class="logo left">';
echo '<img src="logo2.png" class="logo right">';
echo '</div>';

// Botones
echo '<button id="genPdf" onclick="window.print()">GENERAR REPORTE</button>';
echo '<button id="btnAdd">Agregar Reporte</button>';

// Modal de ingreso
echo <<<HTML
<div id="modalForm" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Nuevo Reporte</h2>
    <form id="formReporte">
      <label>Tienda:<br>
        <select name="tienda" id="f_tienda" required></select>
      </label>
      <label>Supervisor:<br>
        <select name="supervisor" id="f_supervisor" required></select>
      </label>
      <label>Tipo:<br>
        <select name="tipo" required>
          <option value="NUEVO">Nuevo</option>
          <option value="SEGUIMIENTO">Seguimiento</option>
        </select>
      </label>
      <label>Fecha:<br>
        <input type="date" name="fecha" required>
      </label>
      <label>Hora:<br>
        <input type="time" name="hora" required>
      </label>
      <label>Estado:<br>
        <select name="estado" required>
          <option>PENDIENTE</option><option>CERRADO</option>
        </select>
      </label>
      <label>Severidad:<br>
        <select name="severidad" required>
          <option>BAJA</option><option>MEDIA</option><option>ALTA</option>
        </select>
      </label>
      <label>Aplicativo:<br>
        <select name="aplicativo" id="f_aplicativo" required></select>
      </label>
      <label>Circuito:<br>
        <select name="circuito" id="f_circuito" required></select>
      </label>
      <label>Detalle:<br>
        <textarea name="detalle" rows="3" required></textarea>
      </label>
      <button type="submit">Guardar</button>
    </form>
  </div>
</div>
HTML;

// Obtener datos
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE MONTH(fecha)=? ORDER BY fecha, hora");
$stmt->execute([$mes]);
$datos = $stmt->fetchAll();

// Tabla
echo '<table id="tablaDatos" class="display">';
echo '<thead><tr>'
   . '<th>N°</th><th>Tienda</th><th>Tipo</th><th>Fecha</th><th>Hora</th>'
   . '<th>Estado</th><th>Severidad</th><th>Aplicativo</th><th>Circuito</th>'
   . '<th>Supervisor</th><th>Detalle</th>'
   . '</tr></thead><tbody>';

foreach($datos as $i => $row) {
    echo '<tr>'
       . '<td>'.($i+1).'</td>'
       . '<td>'.htmlspecialchars($row['tienda']).'</td>'
       . '<td>'.htmlspecialchars($row['tipo']).'</td>'
       . '<td>'.htmlspecialchars($row['fecha']).'</td>'
       . '<td>'.htmlspecialchars($row['hora']).'</td>'
       . '<td>'.htmlspecialchars($row['estado']).'</td>'
       . '<td>'.htmlspecialchars($row['severidad']).'</td>'
       . '<td>'.htmlspecialchars($row['aplicativo']).'</td>'
       . '<td>'.htmlspecialchars($row['circuito']).'</td>'
       . '<td>'.htmlspecialchars($row['supervisor']).'</td>'
       . '<td>'.htmlspecialchars($row['detalle']).'</td>'
       . '</tr>';
}

echo '</tbody></table>';


