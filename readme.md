# Plataforma Web Integral para Asenersa

## Descripción
Este repositorio contiene el código fuente y documentación de la tesis “Desarrollo e implementación de plataforma web integral para Asenersa”. El objetivo del proyecto es ofrecer una solución web capaz de gestionar de forma automatizada y centralizada los procesos clave de la empresa Asenersa:

- **Inventario**: Control de stock, precios, categorías y alertas en tiempo real.  
- **Empleados (RR.HH.)**: Registro de datos, cálculo de prestaciones (IGSS, IRTRA, vacaciones), control de vacaciones tomadas.  
- **Facturas**: Generación de facturas con validación de NIT y total, impresión en PDF, resecuenciación de IDs.  
- **Órdenes de Compra**: Emisión de órdenes, validación de stock contra inventario, impresión en PDF y re-integro de stock al eliminar.

Gracias a esta plataforma, Asenersa optimiza tiempos administrativos, reduce errores humanos y obtiene reportes de desempeño en cada área.

---

## Características Principales

1. **Módulo Inventario**  
   - Listado de productos ordenados ascendentes (ID 1 arriba).  
   - Registro/edición/borrado de productos con resecuenciación automática de IDs.  
   - Validación de cantidad y control de stock.  
   - Impresión de la tabla en PDF (oculta botones en vista de impresión).  
   - Interfaz responsiva basada en Bootstrap 5.

2. **Módulo Empleados (RR.HH.)**  
   - Listado de empleados con datos personales, puesto, fecha de contratación y salario.  
   - Cálculo automático de aportes IGSS (4.83%) e IRTRA (1%).  
   - Control de vacaciones: se toma un total de 30 días al año y se actualiza según los registros de vacaciones tomadas.  
   - Registro de toma de vacaciones y validación de saldo disponible.  
   - Eliminación de empleados con resecuenciación de IDs y devolución de registros de vacaciones.  
   - Impresión en PDF de la tabla de empleados.

3. **Módulo Facturas**  
   - Listado de facturas con número, cliente, fecha (solo YYYY-MM-DD), total (con símbolo “Q”) y NIT.  
   - Creación/edición de facturas: validación de campos, generación automática de fecha y total.  
   - Impresión en PDF (tabla e encabezado).  
   - Resecuenciación de IDs al borrar facturas.  

4. **Módulo Órdenes de Compra**  
   - Listado de órdenes con número (sin prefijo “ORD-”), proveedor, fecha (solo YYYY-MM-DD), producto, cantidad, precio unitario (con “Q”) y total.  
   - Creación/edición de órdenes: selección de producto desde inventario, validación de stock, cálculo de total, y actualización de stock.  
   - Eliminación de órdenes: devolución automática al inventario y resecuenciación de IDs.  
   - Impresión en PDF (ocultando controles).  

5. **Validaciones de Formulario**  
   - Se usan validaciones tanto en PHP como en JavaScript (Bootstrap “needs-validation”).  
   - Mensajes de error y éxito en cada operación CRUD.  

6. **Paginación y Responsividad**  
   - Tablas paginadas (30 elementos por página) para evitar cargas largas si el inventario crece.  
   - Interfaz completamente responsiva para dispositivos móviles y desktop.

---

## Tecnologías Utilizadas

- **Back-end**:  
  - PHP 8.2.x  
  - PDO (MySQL via MariaDB 10.4.x)  
- **Front-end**:  
  - HTML5, CSS3, Bootstrap 5.3  
  - JavaScript (vanilla) para manejo de checkboxes, impresión y validaciones.  
- **Base de Datos**:  
  - MariaDB / MySQL (estructura relacional con tablas: `empleados`, `vacaciones_tomadas`, `productos`, `facturas`, `factura_detalles`, `ordenes_compra`, `orden_compra_detalles`, `usuarios`).  

---

## Instalación y Configuración

1. **Clonar el repositorio**  
   ```bash
   git clone https://github.com/tu-usuario/asenersa-plataforma.git
   cd asenersa-plataforma
