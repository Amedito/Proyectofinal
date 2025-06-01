<?php
// public/inventario_list.php

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

// Mostrar mensaje de éxito (si lo hubiera)
if (!empty($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success mt-3 mx-3">' 
         . htmlspecialchars($_SESSION['mensaje_exito']) .
         '</div>';
    unset($_SESSION['mensaje_exito']);
}

// Paginación
$perPage = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }

$totalProductosDB = obtenerTotalProductos($pdo);
$totalPages = (int) ceil($totalProductosDB / $perPage);
$offset = ($page - 1) * $perPage;

// Obtener productos paginados
$productos = obtenerProductosPaginado($pdo, $perPage, $offset);

// Saber si solo hay 1 producto para pintar en rojo
$soloUnProducto = ($totalProductosDB === 1);
?>

<style>
  @media print {
    .no-print { display: none !important; }
  }
  .precio-col {
    min-width: 120px;
    white-space: nowrap;
  }
</style>

<div class="container mt-4">
  <h2>Inventario</h2>

  <!-- Botones de acción -->
  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
      <button id="btnEdit" class="btn btn-warning me-2" disabled>Editar</button>
      <button id="btnDelete" class="btn btn-danger me-2" disabled>Borrar</button>
      <button id="btnPrint" class="btn btn-secondary">Imprimir PDF</button>
    </div>
    <a href="inventario_add.php" class="btn btn-primary">Nuevo Producto</a>
  </div>

  <!-- Tabla responsiva -->
  <div class="table-responsive">
    <table id="tablaInventario" class="table table-striped table-hover">
      <thead class="table-light">
        <tr>
          <th class="no-print"><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>Nombre</th>
          <th>Cantidad</th>
          <th class="precio-col">Precio</th>
          <th>Categoría</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
          <?php $rowClass = $soloUnProducto ? 'text-danger' : ''; ?>
          <tr class="<?= $rowClass ?>">
            <td class="no-print">
              <input type="checkbox" class="select-row" data-id="<?= $p['id'] ?>">
            </td>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= $p['cantidad'] ?></td>
            <td class="precio-col">Q <?= number_format($p['precio'], 2) ?></td>
            <td><?= htmlspecialchars($p['categoria']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Paginación de Bootstrap -->
  <?php if ($totalPages > 1): ?>
    <nav aria-label="Paginación Inventario">
      <ul class="pagination justify-content-center mt-4">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Anterior">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Siguiente">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php
// Incluir footer (Bootstrap JS y cierre de </body></html>)
require_once __DIR__ . '/../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const selectAllCheckbox = document.getElementById('selectAll');
  const rowCheckboxes     = document.querySelectorAll('.select-row');
  const btnEdit           = document.getElementById('btnEdit');
  const btnDelete         = document.getElementById('btnDelete');
  const btnPrint          = document.getElementById('btnPrint');

  function actualizarBotones() {
    const selected = document.querySelectorAll('.select-row:checked');
    btnDelete.disabled = (selected.length === 0);
    btnEdit.disabled   = (selected.length !== 1);
  }

  // Seleccionar/deseleccionar todas las filas
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      rowCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
      actualizarBotones();
    });
  }

  // Evento individual para cada fila
  rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      if (!this.checked) {
        selectAllCheckbox.checked = false;
      } else {
        const allChecked = Array.from(rowCheckboxes).every(ch => ch.checked);
        selectAllCheckbox.checked = allChecked;
      }
      actualizarBotones();
    });
  });

  // Acción al hacer clic en “Editar”
  btnEdit.addEventListener('click', function() {
    const checked = document.querySelectorAll('.select-row:checked');
    if (checked.length === 1) {
      const id = checked[0].getAttribute('data-id');
      window.location.href = 'inventario_edit.php?id=' + id;
    }
  });

  // Acción al hacer clic en “Borrar”
  btnDelete.addEventListener('click', function() {
    const checked = document.querySelectorAll('.select-row:checked');
    if (checked.length === 0) return;
    const ids = Array.from(checked).map(ch => ch.getAttribute('data-id'));
    const params = ids.map(id => 'ids[]=' + encodeURIComponent(id)).join('&');
    window.location.href = 'inventario_delete.php?' + params;
  });

  // Acción al hacer clic en “Imprimir PDF”
  btnPrint.addEventListener('click', function() {
    window.print();
  });
});
</script>
