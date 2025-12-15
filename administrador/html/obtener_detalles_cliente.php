<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$response = ['success' => false, 'data' => []];

if (isset($_POST['id_cliente'])) {
    $id_cliente = intval($_POST['id_cliente']);
    
    $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['data'] = $result->fetch_assoc();
    } else {
        $response['message'] = 'Cliente no encontrado';
    }
    
    $stmt->close();
} else {
    $response['message'] = 'ID de cliente no proporcionado';
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>