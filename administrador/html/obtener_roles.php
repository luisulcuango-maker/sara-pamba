<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$response = [];

$sql = "SELECT * FROM roles ORDER BY id_rol";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
} else {
    $response = [
        ['id_rol' => 1, 'nombre_rol' => 'Administrador'],
        ['id_rol' => 2, 'nombre_rol' => 'Mesero'],
        ['id_rol' => 3, 'nombre_rol' => 'Cocinero']
    ];
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>