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

/**
 * Obtiene la cantidad de días de vacaciones ya tomados por un empleado.
 * (Si no existe tabla de vacaciones, devuelve 0 para evitar errores.)
 */
function obtenerDiasTomados(PDO $pdo, int $empleado_id): int {
    // Si no existe tabla de vacaciones, siempre 0:
    return 0;
}


// *************************************
// MÓDULO USUARIOS
// *************************************

/**
 * Obtiene todos los usuarios ordenados por ID ascendente.
 */
function obtenerUsuarios(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene un usuario por su ID.
 */
function obtenerUsuarioPorID(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Agrega un nuevo usuario a la base de datos.
 */
function agregarUsuario(PDO $pdo, string $username, string $passwordHash, string $nombre_completo, string $rol): bool {
    $stmt = $pdo->prepare("
        INSERT INTO usuarios 
          (username, password, nombre_completo, rol, creado_en)
        VALUES 
          (:username, :password, :nombre_completo, :rol, NOW())
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
 * Agrega un nuevo producto a la base de datos.
 */
function agregarProducto(PDO $pdo, string $nombre, string $descripcion, float $precio, int $cantidad, string $categoria): bool {
    $stmt = $pdo->prepare("
        INSERT INTO productos 
          (nombre, descripcion, precio, cantidad, categoria, creado_en)
        VALUES 
          (:nombre, :descripcion, :precio, :cantidad, :categoria, NOW())
    ");
    return $stmt->execute([
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':precio'      => $precio,
        ':cantidad'    => $cantidad,
        ':categoria'   => $categoria
    ]);
}

/**
 * Actualiza un producto existente.
 */
function actualizarProducto(PDO $pdo, int $id, string $nombre, string $descripcion, float $precio, int $cantidad, string $categoria): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET nombre = :nombre,
            descripcion = :descripcion,
            precio = :precio,
            cantidad = :cantidad,
            categoria = :categoria
        WHERE id = :id
    ");
    return $stmt->execute([
        ':id'           => $id,
        ':nombre'       => $nombre,
        ':descripcion'  => $descripcion,
        ':precio'       => $precio,
        ':cantidad'     => $cantidad,
        ':categoria'    => $categoria
    ]);
}

/**
 * Elimina un producto por ID.
 */
function eliminarProducto(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Actualiza la cantidad de un producto (útil cuando se resta stock).
 */
function actualizarStockProducto(PDO $pdo, int $idProducto, int $nuevaCantidad): bool {
    $stmt = $pdo->prepare("
        UPDATE productos
        SET cantidad = :cantidad
        WHERE id = :id
    ");
    return $stmt->execute([
        ':id'       => $idProducto,
        ':cantidad' => $nuevaCantidad
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

/**
 * Obtiene el total de productos para paginación.
 */
function obtenerTotalProductos(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    return (int) $stmt->fetchColumn();
}

/**
 * Obtiene productos paginados.
 */
function obtenerProductosPaginado(PDO $pdo, int $limit, int $offset): array {
    $stmt = $pdo->prepare("SELECT * FROM productos ORDER BY id ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// *************************************
// MÓDULO FACTURAS Y DETALLES
// *************************************

/**
 * Agrega una nueva factura a la base de datos.
 */
function agregarFactura(PDO $pdo, int $cliente_id, float $total): bool {
    $stmt = $pdo->prepare("
        INSERT INTO facturas
          (cliente_id, total, creado_en)
        VALUES
          (:cliente_id, :total, NOW())
    ");
    return $stmt->execute([
        ':cliente_id' => $cliente_id,
        ':total'      => $total
    ]);
}

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
 * Obtiene los detalles de una factura.
 */
function obtenerFacturaDetalles(PDO $pdo, int $factura_id): array {
    $stmt = $pdo->prepare("
        SELECT fd.*, p.nombre AS producto_nombre
        FROM factura_detalles fd
        JOIN productos p ON fd.producto_id = p.id
        WHERE fd.factura_id = :factura_id
    ");
    $stmt->execute([':factura_id' => $factura_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Agrega un detalle de factura a la base de datos.
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
 * Elimina una factura por ID (y sus detalles asociados).
 */
function eliminarFactura(PDO $pdo, int $id): bool {
    // Primero eliminar detalles
    $stmtDet = $pdo->prepare("DELETE FROM factura_detalles WHERE factura_id = :factura_id");
    $stmtDet->execute([':factura_id' => $id]);
    // Luego eliminar cabecera
    $stmt = $pdo->prepare("DELETE FROM facturas WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Resequencia los IDs de la tabla facturas para que queden 1,2,3,...
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
// MÓDULO ÓRDENES DE COMPRA Y DETALLES
// *************************************

/**
 * Agrega una nueva orden de compra a la base de datos.
 */
function agregarOrdenCompra(PDO $pdo, int $proveedor_id, float $total): bool {
    $stmt = $pdo->prepare("
        INSERT INTO ordenes_compra
          (proveedor_id, total, creado_en)
        VALUES
          (:proveedor_id, :total, NOW())
    ");
    return $stmt->execute([
        ':proveedor_id' => $proveedor_id,
        ':total'        => $total
    ]);
}

/**
 * Obtiene todas las órdenes de compra ordenadas por ID ascendente.
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
 * Obtiene todos los detalles de una orden de compra.
 */
function obtenerOrdenCompraDetalles(PDO $pdo, int $orden_id): array {
    $stmt = $pdo->prepare("
        SELECT od.*, p.nombre AS producto_nombre
        FROM orden_compra_detalles od
        JOIN productos p ON od.producto_id = p.id
        WHERE od.orden_id = :orden_id
    ");
    $stmt->execute([':orden_id' => $orden_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Agrega un detalle de orden de compra a la base de datos.
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
 * Elimina todos los detalles de una orden de compra (antes de borrar la cabecera).
 */
function eliminarOrdenCompraDetallesPorOrden(PDO $pdo, int $orden_id): bool {
    $stmt = $pdo->prepare("DELETE FROM orden_compra_detalles WHERE orden_id = :orden_id");
    return $stmt->execute([':orden_id' => $orden_id]);
}

/**
 * Elimina una orden de compra por ID (la cabecera y detalles).
 */
function eliminarOrdenCompra(PDO $pdo, int $id): bool {
    // Primero eliminar detalles
    eliminarOrdenCompraDetallesPorOrden($pdo, $id);
    // Luego eliminar cabecera
    $stmt = $pdo->prepare("DELETE FROM ordenes_compra WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Resequencia los IDs de la tabla ordenes_compra para que queden 1,2,3,...
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
?>
