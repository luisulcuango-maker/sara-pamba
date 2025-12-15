<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

if (isset($_POST['id_personal'])) {
    $id_personal = intval($_POST['id_personal']);
    
    // Usar INNER JOIN para obtener el rol también
    $sql = "SELECT p.*, r.nombre_rol FROM personal p 
            INNER JOIN roles r ON p.id_rol = r.id_rol 
            WHERE p.id_personal = $id_personal";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['data'] = $result->fetch_assoc();
    } else {
        $response['message'] = 'Personal no encontrado';
    }
} else {
    $response['message'] = 'ID de personal no proporcionado';
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>