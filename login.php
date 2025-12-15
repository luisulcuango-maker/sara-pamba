<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    // Consulta corregida para la nueva estructura
    $sql = "SELECT p.*, r.nombre_rol 
            FROM personal p 
            INNER JOIN roles r ON p.id_rol = r.id_rol 
            WHERE p.usuario = '$usuario' AND p.contrasena = '$clave' AND p.activo = TRUE";
    
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['rol'] = $row['nombre_rol'];
        $_SESSION['id_personal'] = $row['id_personal'];
        $_SESSION['nombre'] = $row['nombre'];

        // Redirección basada en el rol
        if ($row['nombre_rol'] == 'admin') {
            header("Location: administrador/html/admin_panel.php");
        } else {
            header("Location: usuario/html/user_panel.php");
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 8 MIL Restaurante</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="mountain-decoration"></div>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="logo-section">
                <div class="restaurant-logo">
                    <span>8 MIL</span>
                    <div class="flame-icon">
                        <svg viewBox="0 0 24 24" width="48" height="48">
                            <path fill="currentColor" d="M17.66,11.2C17.43,10.9 17.15,10.64 16.89,10.38C16.22,9.78 15.46,9.35 14.82,8.72C13.33,7.26 13,4.85 13.95,3C13,3.23 12.17,3.75 11.46,4.32C8.87,6.4 7.85,10.07 9.07,13.22C9.11,13.32 9.15,13.42 9.15,13.55C9.15,13.77 9,13.97 8.8,14.05C8.57,14.15 8.33,14.09 8.14,13.93C8.08,13.88 8.04,13.83 8,13.76C6.87,12.33 6.69,10.28 7.45,8.64C5.78,10 4.87,12.3 5,14.47C5.06,14.97 5.12,15.47 5.29,15.97C5.43,16.57 5.7,17.17 6,17.7C7.08,19.43 8.95,20.67 10.96,20.92C13.1,21.19 15.39,20.8 17.03,19.32C18.86,17.66 19.5,15 18.56,12.72L18.43,12.46C18.22,12 17.66,11.2 17.66,11.2M14.5,17.5C14.22,17.74 13.76,18 13.4,18.1C12.28,18.5 11.16,17.94 10.5,17.28C11.69,17 12.4,16.12 12.61,15.23C12.78,14.43 12.46,13.77 12.33,13C12.21,12.26 12.23,11.63 12.5,10.94C12.69,11.32 12.89,11.7 13.13,12C13.93,13 15.1,13.44 15.37,14.8C15.41,14.94 15.43,15.08 15.43,15.23C15.46,16.05 15.1,16.95 14.5,17.5H14.5Z" />
                        </svg>
                    </div>
                </div>
                <p class="system-name">Sistema de Gestión - Restaurante de Montaña</p>
            </div>
            
            <h2 class="login-title">Inicio de Sesión</h2>
            
            <form action="login.php" method="post" class="login-form">
                <div class="input-group">
                    <input type="text" name="usuario" placeholder="Usuario" required class="form-input">
                    <span class="input-icon">👤</span>
                </div>
                
                <div class="input-group">
                    <input type="password" name="clave" placeholder="Contraseña" required class="form-input">
                    <span class="input-icon">🔒</span>
                </div>
                
                <button type="submit" class="login-button">
                    <span>Entrar al Sistema</span>
                    <svg class="button-icon" viewBox="0 0 24 24" width="20" height="20">
                        <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                    </svg>
                </button>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <svg viewBox="0 0 24 24" width="18" height="18">
                            <path fill="currentColor" d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                        </svg>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
            </form>
            
            <div class="login-footer">
                <p>¿Necesitas ayuda? Contacta al administrador</p>
            </div>
        </div>
    </div>
</body>
</html>