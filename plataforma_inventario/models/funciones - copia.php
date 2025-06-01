<?php
// models/funciones.php

// *************************************
// MÓDULO EMPLEADOS
// *************************************

/**
 * Obtiene todos los empleados ordenados por ID ascendente.
 */
function obtenerEmpleados(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM empleados ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene un empleado por su ID.
 */
function obtenerEmpleadoPorID(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Agrega un nuevo empleado a la base de datos.
 */
function agregarEmpleado(PDO $pdo, string $nombre, string $apellido, string $puesto, string $email, string $fecha_contratacion, float $salario): bool {
    $stmt = $pdo->prepare("
        INSERT INTO empleados 
          (nombre, apellido, puesto, email, fecha_contratacion, salario, creado_en)
        VALUES 
          (:nombre, :apellido, :puesto, :email, :fecha_contratacion, :salario, NOW())
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

/**
 * Actualiza un empleado existente.
 */
function actualizarEmpleado(PDO $pdo, int $id, string $nombre, string $apellido, string $puesto, string $email, string $fecha_contratacion, float $salario): bool {
    $stmt = $pdo->prepare("
        UPDATE empleados
        SET nombre = :nombre,
            apellido = :apellido,
            puesto = :puesto,
            email = :email,
            fecha_contratacion = :fecha_contratacion,
            salario = :salario
        WHERE id = :id
    ");
    return $stmt->execute([
        ':id'                 => $id,
        ':nombre'             => $nombre,
        ':apellido'           => $apellido,
        ':puesto'             => $puesto,
        ':email'              => $email,
        ':fecha_contratacion' => $fecha_contratacion,
        ':salario'            => $salario
    ]);
}

/**
 * Elimina un empleado por ID.
 */
function eliminarEmpleado(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Resecuencia los IDs de la tabla empleados para que queden 1,2,3,...
 * Úsalo después de eliminar uno o varios registros.
 */
function resecuenciarEmpleados(PDO $pdo): bool {
    try {
        $pdo->beginTransaction();
        $pdo->exec("SET @count = 0");
        $pdo->exec("
            UPDATE empleados
            SET id = (@count := @count + 1)
            ORDER BY id ASC
        ");
        $pdo->exec("ALTER TABLE empleados AUTO_INCREMENT = 1");
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

// *************************************
// MÓDULO VACACIONES TOMADAS
// *************************************

/**
 * Devuelve la suma de días que un empleado ya ha tomado.
 * Si no hay registros, devuelve 0.
 */
function obtenerDiasTomados(PDO $pdo, int $empleado_id): int {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(dias_tomados), 0) AS total_tomados
        FROM vacaciones_tomadas
        WHERE empleado_id = :eid
    ");
    $stmt->execute([':eid' => $empleado_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$row['total_tomados'];
}

/**
 * Inserta un registro de vacaciones tomadas para un empleado.
 */
function registrarVacacionesTomadas(PDO $pdo, int $empleado_id, int $dias_tomados): bool {
    $stmt = $pdo->prepare("
        INSERT INTO vacaciones_tomadas (empleado_id, dias_tomados)
        VALUES (:eid, :dias)
    ");
    return $stmt->execute([
        ':eid'  => $empleado_id,
        ':dias' => $dias_tomados
    ]);
}

/**
 * Elimina todos los registros de vacaciones tomadas de un empleado.
 */
function eliminarVacacionesTomadasPorEmpleado(PDO $pdo, int $empleado_id): bool {
    $stmt = $pdo->prepare("
        DELETE FROM vacaciones_tomadas
        WHERE empleado_id = :eid
    ");
    return $stmt->execute([':eid' => $empleado_id]);
}

// *************************************
// MÓDULO PRODUCTOS (Inventario)
// *************************************

/**
 * Obtiene todos los productos ordenados por ID ascendente.
 */
function obtenerProductos(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene un producto por su ID.
 */
function obtenerProductoPorID(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Actualiza la cantidad de un producto (útil cuando se resta stock).
 */
function actualizarStockProducto(PDO $pdo, int $idProducto, int $nuevaCantidad): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET cantidad = :cant
        WHERE id = :id
    ");
    return $stmt->execute([
        ':cant' => $nuevaCantidad,
        ':id'   => $idProducto
    ]);
}

/**
 * Resecuencia los IDs de la tabla productos para que queden 1,2,3,...
 */
function resecuenciarProductos(PDO $pdo): bool {
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

// *************************************
// MÓDULO FACTURAS
// *************************************

/**
 * Obtiene todas las facturas ordenadas por ID ascendente.
 */
function obtenerFacturas(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM facturas ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene una factura por su ID.
 */
function obtenerFacturaPorID(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM facturas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Agrega una nueva factura (incluyendo campo nit).
 */
function agregarFactura(PDO $pdo, string $numero_factura, string $fecha_factura, string $cliente, float $total, string $nit): bool {
    $stmt = $pdo->prepare("
        INSERT INTO facturas (numero_factura, fecha_factura, cliente, total, nit)
        VALUES (:numero_factura, :fecha_factura, :cliente, :total, :nit)
    ");
    return $stmt->execute([
        ':numero_factura' => $numero_factura,
        ':fecha_factura'  => $fecha_factura,
        ':cliente'        => $cliente,
        ':total'          => $total,
        ':nit'            => $nit
    ]);
}

/**
 * Actualiza una factura existente (por ID, incluyendo nit).
 */
function actualizarFactura(PDO $pdo, int $id, string $numero_factura, string $fecha_factura, string $cliente, float $total, string $nit): bool {
    $stmt = $pdo->prepare("
        UPDATE facturas
        SET numero_factura = :numero_factura,
            fecha_factura  = :fecha_factura,
            cliente        = :cliente,
            total          = :total,
            nit            = :nit
        WHERE id = :id
    ");
    return $stmt->execute([
        ':id'             => $id,
        ':numero_factura' => $numero_factura,
        ':fecha_factura'  => $fecha_factura,
        ':cliente'        => $cliente,
        ':total'          => $total,
        ':nit'            => $nit
    ]);
}

/**
 * Elimina una factura por ID.
 */
function eliminarFactura(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM facturas WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Resecuencia los IDs de la tabla facturas para que queden 1,2,3,...
 * Úsalo después de eliminar uno o varios registros.
 */
function resecuenciarFacturas(PDO $pdo): bool {
    try {
        $pdo->beginTransaction();
        $pdo->exec("SET @count = 0");
        $pdo->exec("
            UPDATE facturas
            SET id = (@count := @count + 1)
            ORDER BY id ASC
        ");
        $pdo->exec("ALTER TABLE facturas AUTO_INCREMENT = 1");
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

// *************************************
// MÓDULO FACTURA_DETALLES
// *************************************

/**
 * Obtiene todos los detalles de una factura.
 */
function obtenerFacturaDetalles(PDO $pdo, int $factura_id): array {
    $stmt = $pdo->prepare("
        SELECT fd.*, p.nombre AS producto_nombre
        FROM factura_detalles fd
        JOIN productos p ON fd.producto_id = p.id
        WHERE fd.factura_id = :fid
    ");
    $stmt->execute([':fid' => $factura_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Agrega un detalle a una factura.
 */
function agregarFacturaDetalle(PDO $pdo, int $factura_id, int $producto_id, int $cantidad, float $precio_unitario): bool {
    $stmt = $pdo->prepare("
        INSERT INTO factura_detalles (factura_id, producto_id, cantidad, precio_unitario)
        VALUES (:factura_id, :producto_id, :cantidad, :precio_unitario)
    ");
    return $stmt->execute([
        ':factura_id'     => $factura_id,
        ':producto_id'    => $producto_id,
        ':cantidad'       => $cantidad,
        ':precio_unitario'=> $precio_unitario
    ]);
}

/**
 * Elimina todos los detalles de una factura.
 */
function eliminarFacturaDetallesPorFactura(PDO $pdo, int $factura_id): bool {
    $stmt = $pdo->prepare("DELETE FROM factura_detalles WHERE factura_id = :fid");
    return $stmt->execute([':fid' => $factura_id]);
}

// *************************************
// MÓDULO ÓRDENES_DE_COMPRA
// *************************************

/**
 * Obtiene todas las órdenes de compra ordenadas por ID ascendente (ID 1 arriba).
 */
function obtenerOrdenesCompra(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM ordenes_compra ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene una orden de compra por su ID.
 */
function obtenerOrdenCompraPorID(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM ordenes_compra WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Agrega una nueva orden de compra (cabecera) y devuelve el ID insertado.
 */
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
    return (int)$pdo->lastInsertId();
}

/**
 * Actualiza la cabecera de una orden de compra existente (por ID).
 */
function actualizarOrdenCompra(PDO $pdo, int $id, string $numero_orden, string $fecha_orden, string $proveedor, float $total): bool {
    $stmt = $pdo->prepare("
        UPDATE ordenes_compra
        SET numero_orden = :numero_orden,
            fecha_orden  = :fecha_orden,
            proveedor    = :proveedor,
            total        = :total
        WHERE id = :id
    ");
    return $stmt->execute([
        ':id'           => $id,
        ':numero_orden' => $numero_orden,
        ':fecha_orden'  => $fecha_orden,
        ':proveedor'    => $proveedor,
        ':total'        => $total
    ]);
}

/**
 * Elimina una orden de compra por ID.
 */
function eliminarOrdenCompra(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM ordenes_compra WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Resecuencia los IDs de la tabla ordenes_compra para que queden 1,2,3,...
 */
function resecuenciarOrdenesCompra(PDO $pdo): bool {
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

// *************************************
// MÓDULO ORDEN_COMPRA_DETALLES
// *************************************

/**
 * Obtiene todos los detalles de una orden de compra.
 */
function obtenerOrdenCompraDetalles(PDO $pdo, int $orden_id): array {
    $stmt = $pdo->prepare("
        SELECT od.*, p.nombre AS producto_nombre
        FROM orden_compra_detalles od
        JOIN productos p ON od.producto_id = p.id
        WHERE od.orden_id = :oid
    ");
    $stmt->execute([':oid' => $orden_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Agrega un detalle a una orden de compra.
 */
function agregarOrdenCompraDetalle(PDO $pdo, int $orden_id, int $producto_id, int $cantidad, float $precio_unitario): bool {
    $stmt = $pdo->prepare("
        INSERT INTO orden_compra_detalles (orden_id, producto_id, cantidad, precio_unitario)
        VALUES (:orden_id, :producto_id, :cantidad, :precio_unitario)
    ");
    return $stmt->execute([
        ':orden_id'       => $orden_id,
        ':producto_id'    => $producto_id,
        ':cantidad'       => $cantidad,
        ':precio_unitario'=> $precio_unitario
    ]);
}

/**
 * Elimina todos los detalles de una orden de compra.
 */
function eliminarOrdenCompraDetallesPorOrden(PDO $pdo, int $orden_id): bool {
    $stmt = $pdo->prepare("DELETE FROM orden_compra_detalles WHERE orden_id = :oid");
    return $stmt->execute([':oid' => $orden_id]);
}

// *************************************
// MÓDULO USUARIOS (ejemplo mínimo)
// *************************************

/**
 * Obtiene todos los usuarios.
 */
function obtenerUsuarios(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Agrega un nuevo usuario.
 */
function agregarUsuario(PDO $pdo, string $username, string $passwordHash, string $nombre_completo, string $rol): bool {
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (username, password, nombre_completo, rol, creado_en)
        VALUES (:username, :password, :nombre_completo, :rol, NOW())
    ");
    return $stmt->execute([
        ':username'         => $username,
        ':password'         => $passwordHash,
        ':nombre_completo'  => $nombre_completo,
        ':rol'              => $rol
    ]);
}

/**
 * Elimina un usuario por ID.
 */
function eliminarUsuario(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
