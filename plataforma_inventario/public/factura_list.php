<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/funciones.php';
require_once __DIR__ . '/../includes/header.php';

$facturas = obtenerFacturas($pdo);
?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Facturas</h2>
            <a href="factura_add.php" class="btn btn-primary">Nueva Factura</a>
        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Número de Factura</th>
                    <th>Cliente</th>
                    <th>Total (Q)</th>
                    <th>Fecha</th>
                    <th>NIT</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($facturas) > 0): ?>
                <?php foreach ($facturas as $factura): ?>
                <tr>
                    <td><?= $factura['id'] ?></td>
                    <td><?= htmlspecialchars($factura['numero_factura']) ?></td>
                    <td><?= htmlspecialchars($factura['cliente']) ?></td>
                    <td>Q <?= number_format($factura['total'], 2) ?></td>
                    <td><?= date('Y-m-d', strtotime($factura['fecha_factura'])) ?></td>
                    <td><?= htmlspecialchars($factura['nit']) ?></td>
                    <td>
                        <a href="factura_edit.php?id=<?= $factura['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="factura_delete.php?id=<?= $factura['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Seguro que deseas borrar esta factura?');">Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No hay facturas registradas.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
