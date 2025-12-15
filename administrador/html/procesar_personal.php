<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$response = ['success' => false, 'message' => ''];

// Validar que exista la acción
if (!isset($_POST['accion'])) {
    $response['message'] = 'Acción no especificada';
    echo json_encode($response);
    exit();
}

$accion = $_POST['accion'];

if ($accion == 'agregar') {
    // Validar campos requeridos
    if (empty($_POST['nombre']) || empty($_POST['usuario']) || empty($_POST['contrasena'])) {
        $response['message'] = 'Todos los campos son requeridos';
        echo json_encode($response);
        exit();
    }
    
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $usuario = $conn->real_escape_string(trim($_POST['usuario']));
    $contrasena = $_POST['contrasena'];
    $id_rol = intval($_POST['id_rol']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validar longitud de contraseña
    if (strlen($contrasena) < 6) {
        $response['message'] = 'La contraseña debe tener al menos 6 caracteres';
        echo json_encode($response);
        exit();
    }
    
    // Hashear contraseña
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Verificar si el usuario ya existe
    $sql_check = "SELECT id_personal FROM personal WHERE usuario = '$usuario'";
    $result_check = $conn->query($sql_check);
    
    if ($result_check->num_rows > 0) {
        $response['message'] = 'El usuario ya existe';
    } else {
        $sql = "INSERT INTO personal (nombre, usuario, contrasena, id_rol, activo) 
                VALUES ('$nombre', '$usuario', '$hashed_password', $id_rol, $activo)";
        
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Personal agregado correctamente';
        } else {
            $response['message'] = 'Error al agregar personal: ' . $conn->error;
        }
    }
} elseif ($accion == 'editar') {
    // Validar ID
    if (!isset($_POST['id_personal']) || empty($_POST['id_personal'])) {
        $response['message'] = 'ID de personal no especificado';
        echo json_encode($response);
        exit();
    }
    
    $id_personal = intval($_POST['id_personal']);
    
    // Validar campos requeridos
    if (empty($_POST['nombre']) || empty($_POST['usuario'])) {
        $response['message'] = 'Nombre y usuario son requeridos';
        echo json_encode($response);
        exit();
    }
    
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $usuario = $conn->real_escape_string(trim($_POST['usuario']));
    $id_rol = intval($_POST['id_rol']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Verificar si el usuario ya existe (excluyendo el actual)
    $sql_check = "SELECT id_personal FROM personal WHERE usuario = '$usuario' AND id_personal != $id_personal";
    $result_check = $conn->query($sql_check);
    
    if ($result_check->num_rows > 0) {
        $response['message'] = 'El usuario ya existe';
    } else {
        $sql = "UPDATE personal SET 
                nombre = '$nombre', 
                usuario = '$usuario', 
                id_rol = $id_rol, 
                activo = $activo";
        
        // Actualizar contraseña solo si se proporcionó una nueva
        if (!empty($_POST['contrasena'])) {
            $contrasena = $_POST['contrasena'];
            if (strlen($contrasena) < 6) {
                $response['message'] = 'La nueva contraseña debe tener al menos 6 caracteres';
                echo json_encode($response);
                exit();
            }
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql .= ", contrasena = '$hashed_password'";
        }
        
        $sql .= " WHERE id_personal = $id_personal";
        
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Personal actualizado correctamente';
        } else {
            $response['message'] = 'Error al actualizar personal: ' . $conn->error;
        }
    }
} elseif ($accion == 'eliminar') {
    // Validar ID
    if (!isset($_POST['id_personal']) || empty($_POST['id_personal'])) {
        $response['message'] = 'ID de personal no especificado';
        echo json_encode($response);
        exit();
    }
    
    $id_personal = intval($_POST['id_personal']);
    
    // Verificar si es el último administrador (protección)
    $sql_check_admin = "SELECT COUNT(*) as total FROM personal WHERE id_rol = 1 AND id_personal != $id_personal";
    $result_check = $conn->query($sql_check_admin);
    $row_admin = $result_check->fetch_assoc();
    
    if ($row_admin['total'] == 0) {
        $response['message'] = 'No se puede eliminar el último administrador del sistema';
    } else {
        $sql = "DELETE FROM personal WHERE id_personal = $id_personal";
        
        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Personal eliminado correctamente';
        } else {
            $response['message'] = 'Error al eliminar personal: ' . $conn->error;
        }
    }
} else {
    $response['message'] = 'Acción no válida';
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>