<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_factura = $_POST['numero_factura'];
    $fecha_factura  = $_POST['fecha_factura'];
    $cliente        = $_POST['cliente'];
    $nit            = $_POST['nit'];
    $total          = floatval($_POST['total']);

    if (agregarFactura($pdo, $numero_factura, $fecha_factura, $cliente, $nit, $total)) {
        header('Location: factura_list.php');
        exit;
    } else {
        $error = "No se pudo agregar la factura.";
    }
}
?>
<div class="container mt-5">
    <h2 class="mb-4">Nueva Factura</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Número de Factura</label>
            <input type="text" class="form-control" name="numero_factura" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha</label>
            <input type="date" class="form-control" name="fecha_factura" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Cliente</label>
            <input type="text" class="form-control" name="cliente" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">NIT</label>
            <input type="text" class="form-control" name="nit" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Total (Q)</label>
            <input type="number" step="0.01" class="form-control" name="total" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="factura_list.php" class="btn btn-secondary">Regresar al listado</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
