<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

include '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $id_personal = $_SESSION['id_personal'];
    
    try {
        // Verificar que el pedido pertenece al usuario
        $verificar = $conn->query("SELECT id_pedido FROM pedidos WHERE id_pedido = $id_pedido AND id_personal = $id_personal");
        
        if ($verificar->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado o no autorizado']);
            exit();
        }
        
        // Obtener información del pedido
        $pedido_query = $conn->query("
            SELECT p.*, c.nombre as cliente_nombre,
                   DATE_FORMAT(p.fecha_hora, '%d/%m/%Y %H:%i') as fecha_hora_formatted
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            WHERE p.id_pedido = $id_pedido
        ");
        
        if (!$pedido_query) {
            throw new Exception("Error en consulta del pedido: " . $conn->error);
        }
        
        $pedido = $pedido_query->fetch_assoc();
        
        if (!$pedido) {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
            exit();
        }
        
        // Obtener productos del pedido - CORREGIDO: usar precio_unitario en lugar de precio
        $productos_query = $conn->query("
            SELECT dp.*, pr.nombre as nombre_producto,
                   dp.cantidad * dp.precio_unitario as subtotal
            FROM detalles_pedido dp
            JOIN productos pr ON dp.id_producto = pr.id_producto
            WHERE dp.id_pedido = $id_pedido
            ORDER BY dp.id_detalle
        ");
        
        if (!$productos_query) {
            throw new Exception("Error en consulta de productos: " . $conn->error);
        }
        
        $productos = [];
        while ($producto = $productos_query->fetch_assoc()) {
            $productos[] = $producto;
        }
        
        // Formatear datos para la respuesta
        $response = [
            'success' => true,
            'data' => [
                'pedido' => [
                    'id_pedido' => $pedido['id_pedido'],
                    'fecha_hora' => $pedido['fecha_hora'],
                    'fecha_hora_formatted' => $pedido['fecha_hora_formatted'],
                    'cliente_nombre' => $pedido['cliente_nombre'],
                    'metodo_pago' => $pedido['metodo_pago'],
                    'metodo_pago_formatted' => ucfirst($pedido['metodo_pago']),
                    'estado' => $pedido['estado'],
                    'estado_formatted' => ucfirst($pedido['estado']),
                    'total' => $pedido['total'],
                    'notas' => $pedido['notas'] ?? ''
                ],
                'productos' => $productos
            ]
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}

$conn->close();
?>