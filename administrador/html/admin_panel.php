<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - 8 MIL Restaurante</title>
    <!-- Bootstrap 4.6.2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Estilo Mejorado -->
    <link rel="stylesheet" href="../css/estilo_admin_mejorado.css">
</head>
<body>
    <!-- Fondo de montañas decorativo -->
    <div class="mountain-background">
        <div class="mountain-range"></div>
        <div class="mountain-range"></div>
        <div class="mountain-range"></div>
    </div>

    <div class="admin-container">
        <!-- Header Mejorado -->
        <header class="admin-header">
            <div class="header-content">
                <div class="brand-section">
                    <div class="restaurant-logo">
                        <div class="logo-icon">
                            8M
                        </div>
                        <div class="logo-text">
                            <div class="logo-main">8 MIL</div>
                            <div class="logo-subtitle">RESTAURANTE</div>
                        </div>
                    </div>
                    <div class="welcome-text">
                        <h1 class="welcome-title">Panel de <span class="admin-name">Administración</span></h1>
                        <p class="welcome-subtitle">Restaurante gourmet de montaña</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="user-role"><?php echo $_SESSION['rol']; ?> - <?php echo $_SESSION['nombre']; ?></span>
                    </div>
                </div>
            </div>
            
            <nav class="main-nav">
                <div class="nav-container">
                    <a href="admin_panel.php" class="nav-item active">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <a href="categorias.php" class="nav-item">
                        <i class="fas fa-tags"></i>
                        Categorías
                    </a>
                    <a href="productos.php" class="nav-item">
                        <i class="fas fa-utensils"></i>
                        Productos
                    </a>
                    <a href="clientes.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                    <a href="pedidos.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        Pedidos
                    </a>
                    <a href="../php/logout.php" class="nav-item logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Mostrar mensajes -->
            <?php
            if (isset($_GET['mensaje'])) {
                $tipo = $_GET['tipo'] ?? 'success';
                $texto = htmlspecialchars($_GET['mensaje'], ENT_QUOTES, 'UTF-8');
                
                $icono = $tipo == 'success' ? '✅' : '❌';
                $clase = $tipo == 'success' ? 'alert-success' : 'alert-danger';
                echo "<div class='alert $clase alert-dismissible fade show' role='alert'>
                        $icono $texto
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>";
            }
            ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            include '../../conexion.php';
                            $sql_count = "SELECT COUNT(*) as total FROM personal";
                            $result_count = $conn->query($sql_count);
                            $row_count = $result_count->fetch_assoc();
                            echo $row_count['total'];
                            $conn->close();
                            ?>
                        </h3>
                        <p>Total Personal</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            include '../../conexion.php';
                            $sql_products = "SELECT COUNT(*) as total FROM productos";
                            $result_products = $conn->query($sql_products);
                            $row_products = $result_products->fetch_assoc();
                            echo $row_products['total'];
                            $conn->close();
                            ?>
                        </h3>
                        <p>Productos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₡
                            <?php
                            include '../../conexion.php';
                            $sql_sales = "SELECT SUM(total) as total FROM pedidos WHERE DATE(fecha_hora) = CURDATE()";
                            $result_sales = $conn->query($sql_sales);
                            $row_sales = $result_sales->fetch_assoc();
                            echo number_format($row_sales['total'] ?? 0, 2);
                            $conn->close();
                            ?>
                        </h3>
                        <p>Ventas Hoy</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon clients">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            include '../../conexion.php';
                            $sql_clients = "SELECT COUNT(*) as total FROM clientes";
                            $result_clients = $conn->query($sql_clients);
                            $row_clients = $result_clients->fetch_assoc();
                            echo $row_clients['total'];
                            $conn->close();
                            ?>
                        </h3>
                        <p>Clientes</p>
                    </div>
                </div>
            </div>

            <!-- Sección de Gestión del Personal -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Gestión del Personal</h2>
                    <button class="add-btn" data-toggle="modal" data-target="#modalAgregarPersonal">
                        <i class="fas fa-plus"></i> Agregar Personal
                    </button>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../../conexion.php';
                            $sql_personal = "SELECT p.*, r.nombre_rol FROM personal p INNER JOIN roles r ON p.id_rol = r.id_rol";
                            $result_personal = $conn->query($sql_personal);
                            
                            if ($result_personal->num_rows > 0) {
                                while($row = $result_personal->fetch_assoc()) {
                                    $rol_class = strtolower($row['nombre_rol']);
                                    echo "<tr>
                                        <td>{$row['id_personal']}</td>
                                        <td>
                                            <div class='user-cell'>
                                                <div class='user-avatar small'>
                                                    <i class='fas fa-user'></i>
                                                </div>
                                                {$row['nombre']}
                                            </div>
                                        </td>
                                        <td>{$row['usuario']}</td>
                                        <td>
                                            <span class='role-badge {$rol_class}'>{$row['nombre_rol']}</span>
                                        </td>
                                        <td>
                                            <span class='status-badge " . ($row['activo'] ? 'active' : 'inactive') . "'>" . ($row['activo'] ? 'Activo' : 'Inactivo') . "</span>
                                        </td>
                                        <td>
                                            <div class='action-buttons'>
                                                <button class='btn-edit' onclick='editarPersonal({$row['id_personal']})' data-toggle='tooltip' title='Editar'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn-delete' onclick='eliminarPersonal({$row['id_personal']})' data-toggle='tooltip' title='Eliminar'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No hay personal registrado</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Resumen Rápido -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Resumen Rápido</h2>
                </div>
                <div class="quick-stats">
                    <div class="quick-stat-item">
                        <h4>Pedidos Pendientes</h4>
                        <p>
                            <?php
                            include '../../conexion.php';
                            $sql_pending = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'";
                            $result_pending = $conn->query($sql_pending);
                            $row_pending = $result_pending->fetch_assoc();
                            echo $row_pending['total'];
                            $conn->close();
                            ?>
                        </p>
                    </div>
                    <div class="quick-stat-item">
                        <h4>Productos Sin Stock</h4>
                        <p>
                            <?php
                            include '../../conexion.php';
                            $sql_no_stock = "SELECT COUNT(*) as total FROM productos WHERE stock = 0";
                            $result_no_stock = $conn->query($sql_no_stock);
                            $row_no_stock = $result_no_stock->fetch_assoc();
                            echo $row_no_stock['total'];
                            $conn->close();
                            ?>
                        </p>
                    </div>
                    <div class="quick-stat-item">
                        <h4>Ventas del Mes</h4>
                        <p>₡
                            <?php
                            include '../../conexion.php';
                            $sql_month_sales = "SELECT SUM(total) as total FROM pedidos WHERE MONTH(fecha_hora) = MONTH(CURDATE()) AND YEAR(fecha_hora) = YEAR(CURDATE())";
                            $result_month_sales = $conn->query($sql_month_sales);
                            $row_month_sales = $result_month_sales->fetch_assoc();
                            echo number_format($row_month_sales['total'] ?? 0, 2);
                            $conn->close();
                            ?>
                        </p>
                    </div>
                    <div class="quick-stat-item">
                        <h4>Mejor Producto</h4>
                        <p>
                            <?php
                            include '../../conexion.php';
                            // CORREGIDO: 'detalles_pedido' en lugar de 'detalle_pedido'
                            $sql_best_product = "SELECT p.nombre, SUM(dp.cantidad) as total_vendido 
                                                FROM detalles_pedido dp 
                                                JOIN productos p ON dp.id_producto = p.id_producto 
                                                GROUP BY dp.id_producto 
                                                ORDER BY total_vendido DESC 
                                                LIMIT 1";
                            $result_best = $conn->query($sql_best_product);
                            if ($result_best && $result_best->num_rows > 0) {
                                $row_best = $result_best->fetch_assoc();
                                echo substr($row_best['nombre'], 0, 15) . "...";
                                echo "<small class='d-block text-muted'>" . ($row_best['total_vendido'] ?? 0) . " vendidos</small>";
                            } else {
                                echo "No hay datos";
                            }
                            $conn->close();
                            ?>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Últimos Pedidos -->
            <section class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Últimos Pedidos</h2>
                    <a href="pedidos.php" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Ver Todos
                    </a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../../conexion.php';
                            $sql_recent_orders = "SELECT p.id_pedido, c.nombre, p.total, p.estado, p.fecha_hora 
                                                FROM pedidos p 
                                                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                                                ORDER BY p.fecha_hora DESC 
                                                LIMIT 5";
                            $result_recent = $conn->query($sql_recent_orders);
                            
                            if ($result_recent->num_rows > 0) {
                                while($row = $result_recent->fetch_assoc()) {
                                    $estado_clase = '';
                                    switch($row['estado']) {
                                        case 'completado': $estado_clase = 'estado-completado'; break;
                                        case 'pendiente': $estado_clase = 'estado-pendiente'; break;
                                        case 'cancelado': $estado_clase = 'estado-cancelado'; break;
                                        default: $estado_clase = 'estado-pendiente';
                                    }
                                    
                                    echo "<tr>
                                        <td>#{$row['id_pedido']}</td>
                                        <td>{$row['nombre']}</td>
                                        <td>₡" . number_format($row['total'], 2) . "</td>
                                        <td><span class='$estado_clase'>{$row['estado']}</span></td>
                                        <td>" . date('d/m/Y H:i', strtotime($row['fecha_hora'])) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No hay pedidos recientes</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para Agregar Personal -->
    <div class="modal fade" id="modalAgregarPersonal" tabindex="-1" role="dialog" aria-labelledby="modalAgregarPersonalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarPersonalLabel">Agregar Nuevo Personal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAgregarPersonal" action="procesar_personal.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="agregar">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="usuario">Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required placeholder="Ej: juan.perez" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label for="contrasena">Contraseña</label>
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required placeholder="Mínimo 6 caracteres">
                            <small class="form-text text-muted">Usa una combinación de letras y números</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_contrasena">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required placeholder="Repite la contraseña">
                        </div>
                        <div class="form-group">
                            <label for="id_rol">Rol</label>
                            <select class="form-control" id="id_rol" name="id_rol" required>
                                <?php
                                include '../../conexion.php';
                                $sql_roles = "SELECT * FROM roles";
                                $result_roles = $conn->query($sql_roles);
                                while($row_rol = $result_roles->fetch_assoc()) {
                                    echo "<option value='{$row_rol['id_rol']}'>{$row_rol['nombre_rol']}</option>";
                                }
                                $conn->close();
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="activo">
                                    Usuario Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnAgregarPersonal">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Personal -->
    <div class="modal fade" id="modalEditarPersonal" tabindex="-1" role="dialog" aria-labelledby="modalEditarPersonalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarPersonalLabel">Editar Personal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarPersonal" action="procesar_personal.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" id="editar_id_personal" name="id_personal">
                        <div class="form-group">
                            <label for="editar_nombre">Nombre Completo</label>
                            <input type="text" class="form-control" id="editar_nombre" name="nombre" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="editar_usuario">Usuario</label>
                            <input type="text" class="form-control" id="editar_usuario" name="usuario" required maxlength="50">
                        </div>
                        <div class="form-group">
                            <label for="editar_contrasena">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                            <input type="password" class="form-control" id="editar_contrasena" name="contrasena" placeholder="Opcional">
                        </div>
                        <div class="form-group">
                            <label for="editar_id_rol">Rol</label>
                            <select class="form-control" id="editar_id_rol" name="id_rol" required>
                                <?php
                                include '../../conexion.php';
                                $sql_roles = "SELECT * FROM roles";
                                $result_roles = $conn->query($sql_roles);
                                while($row_rol = $result_roles->fetch_assoc()) {
                                    echo "<option value='{$row_rol['id_rol']}'>{$row_rol['nombre_rol']}</option>";
                                }
                                $conn->close();
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editar_activo" name="activo" value="1">
                                <label class="form-check-label" for="editar_activo">
                                    Usuario Activo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnEditarPersonal">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Inicializar tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Validación del formulario de agregar
    $('#formAgregarPersonal').on('submit', function(e) {
        const contrasena = $('#contrasena').val();
        const confirmarContrasena = $('#confirmar_contrasena').val();
        
        if (contrasena !== confirmarContrasena) {
            e.preventDefault();
            alert('❌ Las contraseñas no coinciden');
            return false;
        }
        
        if (contrasena.length < 6) {
            e.preventDefault();
            alert('❌ La contraseña debe tener al menos 6 caracteres');
            return false;
        }

        // Mostrar loading
        const submitBtn = $('#btnAgregarPersonal');
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        submitBtn.prop('disabled', true);
    });

    // Validación del formulario de editar
    $('#formEditarPersonal').on('submit', function(e) {
        const nuevaContrasena = $('#editar_contrasena').val();
        
        if (nuevaContrasena !== '' && nuevaContrasena.length < 6) {
            e.preventDefault();
            alert('❌ La nueva contraseña debe tener al menos 6 caracteres');
            return false;
        }

        // Mostrar loading
        const submitBtn = $('#btnEditarPersonal');
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        submitBtn.prop('disabled', true);
    });

    // Función para editar personal
    function editarPersonal(id) {
        $.ajax({
            url: 'obtener_personal.php',
            type: 'POST',
            data: {id_personal: id},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editar_id_personal').val(response.data.id_personal);
                    $('#editar_nombre').val(response.data.nombre);
                    $('#editar_usuario').val(response.data.usuario);
                    $('#editar_id_rol').val(response.data.id_rol);
                    $('#editar_activo').prop('checked', response.data.activo == 1);
                    $('#editar_contrasena').val('');
                    
                    $('#modalEditarPersonal').modal('show');
                } else {
                    alert('❌ Error: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error al cargar los datos del personal');
            }
        });
    }

    // Función para eliminar personal
    function eliminarPersonal(id) {
        if (confirm('⚠️ ¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: 'procesar_personal.php',
                type: 'POST',
                data: {
                    accion: 'eliminar',
                    id_personal: id
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Recargar la página con mensaje de éxito
                            window.location.href = 'admin_panel.php?mensaje=' + encodeURIComponent(result.message) + '&tipo=success';
                        } else {
                            alert('❌ Error: ' + result.message);
                        }
                    } catch (e) {
                        alert('❌ Error al procesar la respuesta');
                    }
                },
                error: function() {
                    alert('❌ Error de conexión al eliminar');
                }
            });
        }
    }

    // Auto-ocultar mensajes después de 5 segundos
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        console.log('✅ Admin Panel cargado correctamente');
    });

    // Prevenir que se envíe el formulario con Enter en campos individuales
    $(document).on('keypress', 'form input:not([type="submit"])', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
    </script>
</body>
</html>