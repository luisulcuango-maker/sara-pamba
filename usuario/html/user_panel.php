<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';
$id_personal = $_SESSION['id_personal'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - 8 MIL Restaurante</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="mountain-decoration"></div>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-content">
                <div class="welcome-section">
                    <div class="restaurant-logo">
                        <span>8 MIL</span>
                        <div class="flame-icon">
                            <svg viewBox="0 0 24 24" width="32" height="32">
                                <path fill="currentColor" d="M17.66,11.2C17.43,10.9 17.15,10.64 16.89,10.38C16.22,9.78 15.46,9.35 14.82,8.72C13.33,7.26 13,4.85 13.95,3C13,3.23 12.17,3.75 11.46,4.32C8.87,6.4 7.85,10.07 9.07,13.22C9.11,13.32 9.15,13.42 9.15,13.55C9.15,13.77 9,13.97 8.8,14.05C8.57,14.15 8.33,14.09 8.14,13.93C8.08,13.88 8.04,13.83 8,13.76C6.87,12.33 6.69,10.28 7.45,8.64C5.78,10 4.87,12.3 5,14.47C5.06,14.97 5.12,15.47 5.29,15.97C5.43,16.57 5.7,17.17 6,17.7C7.08,19.43 8.95,20.67 10.96,20.92C13.1,21.19 15.39,20.8 17.03,19.32C18.86,17.66 19.5,15 18.56,12.72L18.43,12.46C18.22,12 17.66,11.2 17.66,11.2M14.5,17.5C14.22,17.74 13.76,18 13.4,18.1C12.28,18.5 11.16,17.94 10.5,17.28C11.69,17 12.4,16.12 12.61,15.23C12.78,14.43 12.46,13.77 12.33,13C12.21,12.26 12.23,11.63 12.5,10.94C12.69,11.32 12.89,11.7 13.13,12C13.93,13 15.1,13.44 15.37,14.8C15.41,14.94 15.43,15.08 15.43,15.23C15.46,16.05 15.1,16.95 14.5,17.5H14.5Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="welcome-subtitle">Sistema de gestión - Restaurante de Montaña</p>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar">
                            <svg viewBox="0 0 24 24" width="32" height="32">
                                <path fill="currentColor" d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
                            </svg>
                        </div>
                        <span class="user-role"><?php echo $_SESSION['nombre']; ?></span>
                    </div>
                </div>
            </div>
            
            <nav class="main-nav">
                <div class="nav-container">
                    <a href="user_panel.php" class="nav-item active">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path fill="currentColor" d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z" />
                        </svg>
                        Inicio
                    </a>
                    <a href="pos.php" class="nav-item">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path fill="currentColor" d="M12,13A5,5 0 0,1 7,8H9A3,3 0 0,0 12,11A3,3 0 0,0 15,8H17A5,5 0 0,1 12,13M12,3A3.39,3.39 0 0,0 9.05,3.59A5,5 0 0,1 9.05,10.41A3.39,3.39 0 0,0 12,11A3.39,3.39 0 0,0 14.95,10.41A5,5 0 0,1 14.95,3.59A3.39,3.39 0 0,0 12,3M19,6H17A5,5 0 0,1 12,1A5,5 0 0,1 7,6H5C3.89,6 3,6.89 3,8V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V8C21,6.89 20.1,6 19,6Z" />
                        </svg>
                        Punto de Venta
                    </a>
                    <a href="mis_pedidos.php" class="nav-item">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path fill="currentColor" d="M9,20A2,2 0 0,1 7,22A2,2 0 0,1 5,20A2,2 0 0,1 7,18A2,2 0 0,1 9,20M17,18A2,2 0 0,0 15,20A2,2 0 0,0 17,22A2,2 0 0,0 19,20A2,2 0 0,0 17,18M7.2,14.63C7.19,14.67 7.19,14.71 7.2,14.75A0.25,0.25 0 0,0 7.45,15H19V17H7A2,2 0 0,1 5,15C5,14.65 5.07,14.31 5.24,14L6.6,11.59L3,4H1V2H4.27L5.21,4H20A1,1 0 0,1 21,5C21,5.17 20.95,5.34 20.88,5.5L17.3,12C16.94,12.62 16.27,13 15.55,13H8.1L7.2,14.63M9,9.5H13V11.5L16,8.5L13,5.5V7.5H9V9.5Z" />
                        </svg>
                        Mis Pedidos
                    </a>
                    <a href="../php/logout.php" class="nav-item logout-btn">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path fill="currentColor" d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />
                        </svg>
                        Cerrar Sesión
                    </a>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="pos.php" class="add-btn">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="currentColor" d="M12,13A5,5 0 0,1 7,8H9A3,3 0 0,0 12,11A3,3 0 0,0 15,8H17A5,5 0 0,1 12,13M12,3A3.39,3.39 0 0,0 9.05,3.59A5,5 0 0,1 9.05,10.41A3.39,3.39 0 0,0 12,11A3.39,3.39 0 0,0 14.95,10.41A5,5 0 0,1 14.95,3.59A3.39,3.39 0 0,0 12,3M19,6H17A5,5 0 0,1 12,1A5,5 0 0,1 7,6H5C3.89,6 3,6.89 3,8V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V8C21,6.89 20.1,6 19,6Z" />
                    </svg>
                    Nuevo Pedido
                </a>
                <a href="mis_pedidos.php" class="add-btn" style="background: linear-gradient(135deg, #8f7547 0%, #a78c5d 100%);">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="currentColor" d="M9,20A2,2 0 0,1 7,22A2,2 0 0,1 5,20A2,2 0 0,1 7,18A2,2 0 0,1 9,20M17,18A2,2 0 0,0 15,20A2,2 0 0,0 17,22A2,2 0 0,0 19,20A2,2 0 0,0 17,18M7.2,14.63C7.19,14.67 7.19,14.71 7.2,14.75A0.25,0.25 0 0,0 7.45,15H19V17H7A2,2 0 0,1 5,15C5,14.65 5.07,14.31 5.24,14L6.6,11.59L3,4H1V2H4.27L5.21,4H20A1,1 0 0,1 21,5C21,5.17 20.95,5.34 20.88,5.5L17.3,12C16.94,12.62 16.27,13 15.55,13H8.1L7.2,14.63M9,9.5H13V11.5L16,8.5L13,5.5V7.5H9V9.5Z" />
                    </svg>
                    Ver Mis Pedidos
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <svg viewBox="0 0 24 24" width="32" height="32">
                            <path fill="currentColor" d="M3,22L4.5,20.5L6,22L7.5,20.5L9,22L10.5,20.5L12,22L13.5,20.5L15,22L16.5,20.5L18,22L19.5,20.5L21,22V2L19.5,3.5L18,2L16.5,3.5L15,2L13.5,3.5L12,2L10.5,3.5L9,2L7.5,3.5L6,2L4.5,3.5L3,2M18,9H6V7H18M18,13H6V11H18M18,17H6V15H18V17Z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $sql_today_sales = "SELECT COUNT(*) as total FROM pedidos WHERE id_personal = $id_personal AND DATE(fecha_hora) = CURDATE()";
                            $result_today = $conn->query($sql_today_sales);
                            $row_today = $result_today->fetch_assoc();
                            echo $row_today['total'];
                            ?>
                        </h3>
                        <p>Pedidos Hoy</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <svg viewBox="0 0 24 24" width="32" height="32">
                            <path fill="currentColor" d="M12,13A5,5 0 0,1 7,8H9A3,3 0 0,0 12,11A3,3 0 0,0 15,8H17A5,5 0 0,1 12,13M12,3A3.39,3.39 0 0,0 9.05,3.59A5,5 0 0,1 9.05,10.41A3.39,3.39 0 0,0 12,11A3.39,3.39 0 0,0 14.95,10.41A5,5 0 0,1 14.95,3.59A3.39,3.39 0 0,0 12,3M19,6H17A5,5 0 0,1 12,1A5,5 0 0,1 7,6H5C3.89,6 3,6.89 3,8V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V8C21,6.89 20.1,6 19,6Z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $sql_total_sales = "SELECT COUNT(*) as total FROM pedidos WHERE id_personal = $id_personal";
                            $result_total = $conn->query($sql_total_sales);
                            $row_total = $result_total->fetch_assoc();
                            echo $row_total['total'];
                            ?>
                        </h3>
                        <p>Total Pedidos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon clients">
                        <svg viewBox="0 0 24 24" width="32" height="32">
                            <path fill="currentColor" d="M17,18C15.89,18 15,18.89 15,20A2,2 0 0,0 17,22A2,2 0 0,0 19,20C19,18.89 18.1,18 17,18M1,2V4H3L6.6,11.59L5.24,14.04C5.09,14.32 5,14.65 5,15A2,2 0 0,0 7,17H19V15H7.42C7.29,15 7.17,14.89 7.17,14.75L7.2,14.63L8.1,13H15.55C16.3,13 16.96,12.58 17.3,11.97L20.88,5.5C20.95,5.34 21,5.17 21,5A1,1 0 0,0 20,4H5.21L4.27,2M7,18C5.89,18 5,18.89 5,20A2,2 0 0,0 7,22A2,2 0 0,0 9,20C9,18.89 8.1,18 7,18Z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $sql_total_ingresos = "SELECT COALESCE(SUM(total), 0) as total FROM pedidos WHERE id_personal = $id_personal";
                            $result_ingresos = $conn->query($sql_total_ingresos);
                            $row_ingresos = $result_ingresos->fetch_assoc();
                            echo "₡" . number_format($row_ingresos['total'], 0);
                            ?>
                        </h3>
                        <p>Total Ventas</p>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Mis Pedidos Recientes</h2>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_pedidos = "SELECT p.*, c.nombre as cliente_nombre 
                                          FROM pedidos p 
                                          LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                                          WHERE p.id_personal = $id_personal 
                                          ORDER BY p.fecha_hora DESC 
                                          LIMIT 5";
                            $result_pedidos = $conn->query($sql_pedidos);
                            
                            if ($result_pedidos->num_rows > 0) {
                                while($row = $result_pedidos->fetch_assoc()) {
                                    echo "<tr>
                                        <td>#{$row['id_pedido']}</td>
                                        <td>
                                            <div class='user-cell'>
                                                <div class='user-avatar small'>
                                                    <svg viewBox='0 0 24 24' width='16' height='16'>
                                                        <path fill='currentColor' d='M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z' />
                                                    </svg>
                                                </div>
                                                " . ($row['cliente_nombre'] ?: 'Cliente no registrado') . "
                                            </div>
                                        </td>
                                        <td>
                                            <span class='status-badge estado-{$row['estado']}'>" . ucfirst($row['estado']) . "</span>
                                        </td>
                                        <td>" . date('d M, Y', strtotime($row['fecha_hora'])) . "</td>
                                        <td>₡" . number_format($row['total'], 2) . "</td>
                                        <td>
                                            <div class='action-buttons'>
                                                <button class='btn-info' onclick='verDetalles({$row['id_pedido']})'>
                                                    <i class='fas fa-eye'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No hay pedidos registrados</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

    <script>
    function verDetalles(idPedido) {
        window.location.href = `mis_pedidos.php?ver=${idPedido}`;
    }
    </script>
</body>
</html>