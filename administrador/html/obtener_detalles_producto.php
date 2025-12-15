<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$response = ['success' => false, 'data' => []];

if (isset($_POST['id_producto'])) {
    $id_producto = intval($_POST['id_producto']);
    
    $sql = "SELECT p.*, c.nombre_categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE p.id_producto = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['data'] = $result->fetch_assoc();
    } else {
        $response['message'] = 'Producto no encontrado';
    }
    
    $stmt->close();
} else {
    $response['message'] = 'ID de producto no proporcionado';
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>