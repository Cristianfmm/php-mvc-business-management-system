<?php
/**
  * Funciones de Base de Datos - Módulo de Facturación
  */
 
 // ==================== CLIENTES ====================
 
 function obtenerClientes($base) {
     try {
         $link = conexionMySQLi($base);
         
         $query = "SELECT * FROM clientes WHERE estado = 'activo' ORDER BY nombre_completo ASC";
         $result = $link->query($query);
         
         $clientes = [];
         while ($row = $result->fetch_assoc()) {
             $clientes[] = $row;
         }
         
         $link->close();
         return response($clientes);
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 function crearCliente($base, $datos) {
     try {
         $link = conexionMySQLi($base);
         
         // Verificar si ya existe el documento
         $checkQuery = "SELECT id FROM clientes WHERE numero_documento = ?";
         $stmt = $link->prepare($checkQuery);
         $stmt->bind_param("s", $datos['numero_documento']);
         $stmt->execute();
         $result = $stmt->get_result();
         
         if ($result->num_rows > 0) {
             $link->close();
             return response(false, "El número de documento ya está registrado");
         }
         
         $query = "INSERT INTO clientes (tipo_documento, numero_documento, nombre_completo, direccion, telefono, email, ciudad) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
         
         $stmt = $link->prepare($query);
         $stmt->bind_param("sssssss", 
             $datos['tipo_documento'],
             $datos['numero_documento'],
             $datos['nombre_completo'],
             $datos['direccion'],
             $datos['telefono'],
             $datos['email'],
             $datos['ciudad']
         );
         
         if ($stmt->execute()) {
             $clienteId = $stmt->insert_id;
             $link->close();
             return response(['id' => $clienteId], "Cliente creado exitosamente");
         } else {
             $link->close();
             return response(false, "Error al crear cliente");
         }
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 // ==================== PRODUCTOS ====================
 
 function obtenerProductos($base) {
     try {
         $link = conexionMySQLi($base);
         
         $query = "SELECT * FROM productos WHERE estado = 'activo' ORDER BY nombre ASC";
         $result = $link->query($query);
         
         $productos = [];
         while ($row = $result->fetch_assoc()) {
             $productos[] = $row;
         }
         
         $link->close();
         return response($productos);
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 function crearProducto($base, $datos) {
     try {
         $link = conexionMySQLi($base);
         
         $query = "INSERT INTO productos (codigo, nombre, descripcion, tipo, precio_unitario, iva_porcentaje, stock, unidad_medida) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
         
         $stmt = $link->prepare($query);
         $stmt->bind_param("ssssddis", 
             $datos['codigo'],
             $datos['nombre'],
             $datos['descripcion'],
             $datos['tipo'],
             $datos['precio_unitario'],
             $datos['iva_porcentaje'],
             $datos['stock'],
             $datos['unidad_medida']
         );
         
         if ($stmt->execute()) {
             $productoId = $stmt->insert_id;
             $link->close();
             return response(['id' => $productoId], "Producto creado exitosamente");
         } else {
             $link->close();
             return response(false, "Error al crear producto");
         }
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 // ==================== FACTURAS ====================
 
 function obtenerFacturas($base, $filtros = []) {
     try {
         $link = conexionMySQLi($base);
         
         $query = "SELECT f.*, c.nombre_completo as cliente_nombre, c.numero_documento 
                   FROM facturas f 
                   INNER JOIN clientes c ON f.cliente_id = c.id 
                   WHERE 1=1";
         
         // Aplicar filtros si existen
         if (!empty($filtros['estado'])) {
             $query .= " AND f.estado = '{$filtros['estado']}'";
         }
         
         if (!empty($filtros['fecha_desde'])) {
             $query .= " AND f.fecha_emision >= '{$filtros['fecha_desde']}'";
         }
         
         if (!empty($filtros['fecha_hasta'])) {
             $query .= " AND f.fecha_emision <= '{$filtros['fecha_hasta']}'";
         }
         
         $query .= " ORDER BY f.created_at DESC";
         
         $result = $link->query($query);
         
         $facturas = [];
         while ($row = $result->fetch_assoc()) {
             $facturas[] = $row;
         }
         
         $link->close();
         return response($facturas);
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 function crearFactura($base, $datos) {
     try {
         $link = conexionMySQLi($base);
         $link->begin_transaction();
         
         // Obtener el siguiente consecutivo
         $queryConsecutivo = "SELECT COALESCE(MAX(consecutivo), 0) + 1 as siguiente FROM facturas WHERE prefijo = ?";
         $stmtConsec = $link->prepare($queryConsecutivo);
         $stmtConsec->bind_param("s", $datos['prefijo']);
         $stmtConsec->execute();
         $resultConsec = $stmtConsec->get_result();
         $consecutivo = $resultConsec->fetch_assoc()['siguiente'];
         
         // Generar número de factura
         $numeroFactura = $datos['prefijo'] . '-' . str_pad($consecutivo, 6, '0', STR_PAD_LEFT);
         
         // Calcular totales
         $subtotal = 0;
         $ivaTotal = 0;
         
         foreach ($datos['items'] as $item) {
             $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
             $itemIva = $itemSubtotal * ($item['iva_porcentaje'] / 100);
             $subtotal += $itemSubtotal;
             $ivaTotal += $itemIva;
         }
         
         $descuento = isset($datos['descuento']) ? $datos['descuento'] : 0;
         $total = $subtotal + $ivaTotal - $descuento;
         
         // Insertar factura
         $queryFactura = "INSERT INTO facturas (numero_factura, prefijo, consecutivo, cliente_id, fecha_emision, 
                          fecha_vencimiento, subtotal, iva_total, descuento, total, estado, metodo_pago, observaciones) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
         
         $stmt = $link->prepare($queryFactura);
         $stmt->bind_param("ssiissdddssss",
             $numeroFactura,
             $datos['prefijo'],
             $consecutivo,
             $datos['cliente_id'],
             $datos['fecha_emision'],
             $datos['fecha_vencimiento'],
             $subtotal,
             $ivaTotal,
             $descuento,
             $total,
             $datos['estado'],
             $datos['metodo_pago'],
             $datos['observaciones']
         );
         
         if (!$stmt->execute()) {
             $link->rollback();
             $link->close();
             return response(false, "Error al crear la factura");
         }
         
         $facturaId = $stmt->insert_id;
         
         // Insertar detalles
         $queryDetalle = "INSERT INTO factura_detalles (factura_id, producto_id, cantidad, precio_unitario, 
                          iva_porcentaje, subtotal, iva, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
         
         $stmtDetalle = $link->prepare($queryDetalle);
         
         foreach ($datos['items'] as $item) {
             $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
             $itemIva = $itemSubtotal * ($item['iva_porcentaje'] / 100);
             $itemTotal = $itemSubtotal + $itemIva;
             
             $stmtDetalle->bind_param("iidddddd",
                 $facturaId,
                 $item['producto_id'],
                 $item['cantidad'],
                 $item['precio_unitario'],
                 $item['iva_porcentaje'],
                 $itemSubtotal,
                 $itemIva,
                 $itemTotal
             );
             
             if (!$stmtDetalle->execute()) {
                 $link->rollback();
                 $link->close();
                 return response(false, "Error al insertar detalles de la factura");
             }
             
             // Actualizar stock si es producto
             if ($item['tipo'] == 'producto') {
                 $updateStock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
                 $stmtStock = $link->prepare($updateStock);
                 $stmtStock->bind_param("di", $item['cantidad'], $item['producto_id']);
                 $stmtStock->execute();
             }
         }
         
         $link->commit();
         $link->close();
         
         return response([
             'id' => $facturaId,
             'numero_factura' => $numeroFactura,
             'total' => $total
         ], "Factura creada exitosamente");
         
     } catch (Exception $e) {
         $link->rollback();
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 function obtenerDetalleFactura($base, $facturaId) {
     try {
         $link = conexionMySQLi($base);
         
         // Obtener factura
         $queryFactura = "SELECT f.*, c.* 
                          FROM facturas f 
                          INNER JOIN clientes c ON f.cliente_id = c.id 
                          WHERE f.id = ?";
         
         $stmt = $link->prepare($queryFactura);
         $stmt->bind_param("i", $facturaId);
         $stmt->execute();
         $result = $stmt->get_result();
         $factura = $result->fetch_assoc();
         
         if (!$factura) {
             $link->close();
             return response(false, "Factura no encontrada");
         }
         
         // Obtener detalles
         $queryDetalles = "SELECT fd.*, p.nombre as producto_nombre, p.codigo as producto_codigo 
                           FROM factura_detalles fd 
                           INNER JOIN productos p ON fd.producto_id = p.id 
                           WHERE fd.factura_id = ?";
         
         $stmtDetalles = $link->prepare($queryDetalles);
         $stmtDetalles->bind_param("i", $facturaId);
         $stmtDetalles->execute();
         $resultDetalles = $stmtDetalles->get_result();
         
         $detalles = [];
         while ($row = $resultDetalles->fetch_assoc()) {
             $detalles[] = $row;
         }
         
         $factura['detalles'] = $detalles;
         
         $link->close();
         return response($factura);
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }
 
 function obtenerEstadisticas($base) {
     try {
         $link = conexionMySQLi($base);
         
         // Total de facturas
         $queryTotal = "SELECT COUNT(*) as total FROM facturas";
         $resultTotal = $link->query($queryTotal);
         $totalFacturas = $resultTotal->fetch_assoc()['total'];
         
         // Total facturado
         $queryMonto = "SELECT SUM(total) as total_facturado FROM facturas WHERE estado != 'anulada'";
         $resultMonto = $link->query($queryMonto);
         $totalFacturado = $resultMonto->fetch_assoc()['total_facturado'] ?? 0;
         
         // Facturas pendientes
         $queryPendientes = "SELECT COUNT(*) as total, SUM(total) as monto 
                             FROM facturas WHERE estado = 'pendiente'";
         $resultPendientes = $link->query($queryPendientes);
         $pendientes = $resultPendientes->fetch_assoc();
         
         // Facturas del mes
         $queryMes = "SELECT COUNT(*) as total, SUM(total) as monto 
                      FROM facturas 
                      WHERE MONTH(fecha_emision) = MONTH(CURRENT_DATE()) 
                      AND YEAR(fecha_emision) = YEAR(CURRENT_DATE())
                      AND estado != 'anulada'";
         $resultMes = $link->query($queryMes);
         $delMes = $resultMes->fetch_assoc();
         
         $link->close();
         
         return response([
             'total_facturas' => $totalFacturas,
             'total_facturado' => $totalFacturado,
             'pendientes_cantidad' => $pendientes['total'] ?? 0,
             'pendientes_monto' => $pendientes['monto'] ?? 0,
             'mes_cantidad' => $delMes['total'] ?? 0,
             'mes_monto' => $delMes['monto'] ?? 0
         ]);
         
     } catch (Exception $e) {
         return array('code' => 500, 'response' => $e->getMessage());
     }
 }


?>