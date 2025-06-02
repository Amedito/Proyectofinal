<?php
// models/funciones.php

// ------------------------ CONEXIÓN ------------------------
function conectarDB(): PDO {
    $host    = 'localhost';
    $db      = 'asenersa_db';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}


// ========================= VACACIONES =========================
function calcularDiasVacacionesAcumulados(string $fecha_contratacion): int {
    $dateStart = new DateTime($fecha_contratacion);
    $dateNow   = new DateTime();
    $anios     = $dateNow->diff($dateStart)->y;
    return $anios * 15;
}

function obtenerDiasTomados(PDO $pdo, int $empleado_id): int {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(dias_tomados), 0)
        FROM vacaciones_tomadas
        WHERE empleado_id = :empleado_id
    ");
    $stmt->execute([':empleado_id' => $empleado_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * Devuelve los días de vacaciones disponibles para un empleado:
 *  - Llama a calcularDiasVacacionesAcumulados() para saber cuántos días ha generado
 *  - Resta obtenerDiasTomados()
 *  - Si el resultado es negativo, retorna 0
 */
function obtenerDiasVacacionesDisponibles(PDO $pdo, int $empleado_id, string $fecha_contratacion): int {
    $acumulados = calcularDiasVacacionesAcumulados($fecha_contratacion);
    $tomados    = obtenerDiasTomados($pdo, $empleado_id);
    $disponibles = $acumulados - $tomados;
    return ($disponibles > 0) ? $disponibles : 0;
}


// ========================= PRODUCTOS =========================
function agregarProducto(PDO $pdo, string $nombre, string $descripcion, float $precio, int $cantidad, string $categoria): bool {
    $stmt = $pdo->prepare("
        INSERT INTO productos (nombre, descripcion, precio, cantidad, categoria)
        VALUES (:nombre, :descripcion, :precio, :cantidad, :categoria)
    ");
    return $stmt->execute([
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':precio'      => $precio,
        ':cantidad'    => $cantidad,
        ':categoria'   => $categoria
    ]);
}

function obtenerProductoPorId(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function actualizarProducto(PDO $pdo, int $id, string $nombre, string $descripcion, float $precio, int $cantidad, string $categoria): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET nombre      = :nombre,
            descripcion = :descripcion,
            precio      = :precio,
            cantidad    = :cantidad,
            categoria   = :categoria
        WHERE id = :id
    ");
    return $stmt->execute([
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':precio'      => $precio,
        ':cantidad'    => $cantidad,
        ':categoria'   => $categoria,
        ':id'          => $id
    ]);
}

function eliminarProducto(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

function obtenerProductos(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY id ASC");
    return $stmt->fetchAll();
}

function obtenerProductosPaginado(PDO $pdo, int $limit, int $offset): array {
    $stmt = $pdo->prepare("
        SELECT *
        FROM productos
        ORDER BY id ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerTotalProductos(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM productos");
    $row  = $stmt->fetch();
    return (int) ($row['total'] ?? 0);
}

function reordenarIDsProductos(PDO $pdo): bool {
    try {
        $pdo->beginTransaction();
        $pdo->exec("SET @count = 0");
        $pdo->exec("
            UPDATE productos
            SET id = (@count := @count + 1)
            ORDER BY id ASC
        ");
        $pdo->exec("ALTER TABLE productos AUTO_INCREMENT = 1");
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}


// ========================= INVENTARIO (usando tabla productos) =========================
function obtenerInventario(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT 
            id, 
            nombre    AS producto, 
            cantidad, 
            precio    AS precio_unitario 
        FROM productos
        ORDER BY id ASC
    ");
    return $stmt->fetchAll();
}

function obtenerRegistroInventarioPorId(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            nombre    AS producto, 
            cantidad, 
            precio    AS precio_unitario 
        FROM productos
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function actualizarInventario(PDO $pdo, int $id, int $producto_id, int $cantidad, float $precio_unitario): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET cantidad = :cantidad,
            precio   = :precio_unitario
        WHERE id = :producto_id
    ");
    return $stmt->execute([
        ':cantidad'        => $cantidad,
        ':precio_unitario' => $precio_unitario,
        ':producto_id'     => $producto_id
    ]);
}

function eliminarInventario(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

function reordenarIDsInventario(PDO $pdo): bool {
    return reordenarIDsProductos($pdo);
}


// ========================= ÓRDENES DE COMPRA =========================
function agregarOrdenCompra(PDO $pdo, string $numero_orden, string $fecha_orden, string $proveedor, float $total): int {
    $stmt = $pdo->prepare("
        INSERT INTO ordenes_compra (numero_orden, fecha_orden, proveedor, total)
        VALUES (:numero_orden, :fecha_orden, :proveedor, :total)
    ");
    $stmt->execute([
        ':numero_orden' => $numero_orden,
        ':fecha_orden'  => $fecha_orden,
        ':proveedor'    => $proveedor,
        ':total'        => $total
    ]);
    return (int) $pdo->lastInsertId();
}

function agregarOrdenCompraDetalle(PDO $pdo, int $orden_id, int $producto_id, int $cantidad, float $precio_unitario): bool {
    $stmt = $pdo->prepare("
        INSERT INTO orden_compra_detalles (orden_id, producto_id, cantidad, precio_unitario)
        VALUES (:orden_id, :producto_id, :cantidad, :precio_unitario)
    ");
    $stmt->execute([
        ':orden_id'        => $orden_id,
        ':producto_id'     => $producto_id,
        ':cantidad'        => $cantidad,
        ':precio_unitario' => $precio_unitario
    ]);
    return reducirStockProducto($pdo, $producto_id, $cantidad);
}

function eliminarOrdenCompraDetallesPorOrden(PDO $pdo, int $orden_id): bool {
    $detalles = obtenerOrdenCompraDetalles($pdo, $orden_id);
    foreach ($detalles as $detalle) {
        actualizarStockProducto($pdo, $detalle['producto_id'], $detalle['cantidad']);
    }
    $stmt = $pdo->prepare("DELETE FROM orden_compra_detalles WHERE orden_id = :orden_id");
    return $stmt->execute([':orden_id' => $orden_id]);
}

function actualizarOrdenCompra(PDO $pdo, int $id, string $numero_orden, string $fecha_orden, string $proveedor, float $total): bool {
    $stmt = $pdo->prepare("
        UPDATE ordenes_compra
        SET numero_orden = :numero_orden,
            fecha_orden  = :fecha_orden,
            proveedor     = :proveedor,
            total         = :total
        WHERE id = :id
    ");
    return $stmt->execute([
        ':numero_orden' => $numero_orden,
        ':fecha_orden'  => $fecha_orden,
        ':proveedor'    => $proveedor,
        ':total'        => $total,
        ':id'           => $id
    ]);
}

function eliminarOrdenCompra(PDO $pdo, int $id): bool {
    eliminarOrdenCompraDetallesPorOrden($pdo, $id);
    $stmt = $pdo->prepare("DELETE FROM ordenes_compra WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

function obtenerOrdenCompraPorID(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM ordenes_compra WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function obtenerOrdenCompraDetalles(PDO $pdo, int $orden_id): array {
    $stmt = $pdo->prepare("
        SELECT od.*, p.nombre AS producto_nombre
        FROM orden_compra_detalles od
        JOIN productos p ON od.producto_id = p.id
        WHERE od.orden_id = :orden_id
    ");
    $stmt->execute([':orden_id' => $orden_id]);
    return $stmt->fetchAll();
}

function obtenerOrdenesCompra(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT
            oc.id            AS orden_id,
            oc.numero_orden,
            oc.proveedor,
            oc.total,
            oc.fecha_orden,
            p.nombre         AS producto,
            od.cantidad,
            od.precio_unitario
        FROM ordenes_compra oc
        LEFT JOIN orden_compra_detalles od ON oc.id = od.orden_id
        LEFT JOIN productos p ON od.producto_id = p.id
        ORDER BY oc.id ASC
    ");
    return $stmt->fetchAll();
}

function reordenarIDsOrdenesCompra(PDO $pdo): bool {
    try {
        $pdo->beginTransaction();
        $pdo->exec("SET @count = 0");
        $pdo->exec("
            UPDATE ordenes_compra
            SET id = (@count := @count + 1)
            ORDER BY id ASC
        ");
        $pdo->exec("ALTER TABLE ordenes_compra AUTO_INCREMENT = 1");
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}


// ========================= STOCK DE PRODUCTOS =========================
function reducirStockProducto(PDO $pdo, int $producto_id, int $cantidad): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET cantidad = cantidad - :cantidad
        WHERE id = :id
    ");
    return $stmt->execute([
        ':cantidad' => $cantidad,
        ':id'       => $producto_id
    ]);
}

function actualizarStockProducto(PDO $pdo, int $producto_id, int $cantidad): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET cantidad = cantidad + :cantidad
        WHERE id = :id
    ");
    return $stmt->execute([
        ':cantidad' => $cantidad,
        ':id'       => $producto_id
    ]);
}


// ========================= EMPLEADOS =========================
function obtenerEmpleados(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM empleados ORDER BY id ASC");
    return $stmt->fetchAll();
}

function obtenerEmpleadoPorId(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function agregarEmpleado(PDO $pdo, string $nombre, string $apellido, string $puesto, string $email, string $fecha_contratacion, float $salario): bool {
    $stmt = $pdo->prepare("
        INSERT INTO empleados (nombre, apellido, puesto, email, fecha_contratacion, salario)
        VALUES (:nombre, :apellido, :puesto, :email, :fecha_contratacion, :salario)
    ");
    return $stmt->execute([
        ':nombre'             => $nombre,
        ':apellido'           => $apellido,
        ':puesto'             => $puesto,
        ':email'              => $email,
        ':fecha_contratacion' => $fecha_contratacion,
        ':salario'            => $salario
    ]);
}

function actualizarEmpleado(PDO $pdo, int $id, string $nombre, string $apellido, string $puesto, string $email, string $fecha_contratacion, float $salario): bool {
    $stmt = $pdo->prepare("
        UPDATE empleados
        SET nombre             = :nombre,
            apellido           = :apellido,
            puesto             = :puesto,
            email              = :email,
            fecha_contratacion = :fecha_contratacion,
            salario            = :salario
        WHERE id = :id
    ");
    return $stmt->execute([
        ':nombre'             => $nombre,
        ':apellido'           => $apellido,
        ':puesto'             => $puesto,
        ':email'              => $email,
        ':fecha_contratacion' => $fecha_contratacion,
        ':salario'            => $salario,
        ':id'                 => $id
    ]);
}

function eliminarEmpleado(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}


// ========================= FACTURAS =========================
function obtenerFacturas(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM facturas ORDER BY id ASC");
    return $stmt->fetchAll();
}

function obtenerFacturaPorId(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM facturas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function agregarFactura(PDO $pdo, string $numero_factura, string $fecha, float $monto_total): bool {
    $stmt = $pdo->prepare("
        INSERT INTO facturas (numero_factura, fecha, monto_total)
        VALUES (:numero_factura, :fecha, :monto_total)
    ");
    return $stmt->execute([
        ':numero_factura' => $numero_factura,
        ':fecha'          => $fecha,
        ':monto_total'    => $monto_total
    ]);
}

function actualizarFactura(PDO $pdo, int $id, string $numero_factura, string $fecha, float $monto_total): bool {
    $stmt = $pdo->prepare("
        UPDATE facturas
        SET numero_factura = :numero_factura,
            fecha          = :fecha,
            monto_total    = :monto_total
        WHERE id = :id
    ");
    return $stmt->execute([
        ':numero_factura' => $numero_factura,
        ':fecha'          => $fecha,
        ':monto_total'    => $monto_total,
        ':id'             => $id
    ]);
}

function eliminarFactura(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM facturas WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}


// ========================= USUARIOS (LOGIN) =========================
function agregarUsuario(PDO $pdo, string $nombre, string $email, string $passwordHash): bool {
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, password)
        VALUES (:nombre, :email, :password)
    ");
    return $stmt->execute([
        ':nombre'   => $nombre,
        ':email'    => $email,
        ':password' => $passwordHash
    ]);
}

function obtenerUsuarioPorEmail(PDO $pdo, string $email): ?array {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetch() ?: null;
}

function autenticarUsuario(PDO $pdo, string $email, string $password): bool {
    $usuario = obtenerUsuarioPorEmail($pdo, $email);
    if ($usuario && password_verify($password, $usuario['password'])) {
        return true;
    }
    return false;
}
?>
