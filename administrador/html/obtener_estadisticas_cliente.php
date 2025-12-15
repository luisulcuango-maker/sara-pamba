<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$response = ['success' => false, 'total_pedidos' => 0, 'total_gastado' => 0, 'ultimo_pedido' => 'N/A'];

if (isset($_POST['id_cliente'])) {
    $id_cliente = intval($_POST['id_cliente']);
    
    // Total de pedidos
    $sql_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql_pedidos);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $response['total_pedidos'] = $row['total'];
    $stmt->close();
    
    // Total gastado
    $sql_total = "SELECT SUM(total) as total FROM pedidos WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql_total);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $response['total_gastado'] = $row['total'] ?: 0;
    $stmt->close();
    
    // Último pedido
    $sql_ultimo = "SELECT fecha_hora FROM pedidos WHERE id_cliente = ? ORDER BY fecha_hora DESC LIMIT 1";
    $stmt = $conn->prepare($sql_ultimo);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['ultimo_pedido'] = date('d/m/Y', strtotime($row['fecha_hora']));
    }
    $stmt->close();
    
    $response['success'] = true;
} else {
    $response['message'] = 'ID de cliente no proporcionado';
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>