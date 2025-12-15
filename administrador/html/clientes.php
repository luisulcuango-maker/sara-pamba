<?php
include("../../conexion.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$mensaje = "";

// CREATE Cliente
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);

    if (!empty($nombre)) {
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono, correo, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $telefono, $correo, $direccion);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Cliente registrado correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al insertar: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $mensaje = "<div class='alert error'>❌ El nombre es requerido</div>";
    }
}

// UPDATE Cliente
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['actualizar'])) {
    $id_cliente = $_POST['id_cliente'];
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);

    if (!empty($nombre)) {
        $stmt = $conn->prepare("UPDATE clientes SET nombre=?, telefono=?, correo=?, direccion=? WHERE id_cliente=?");
        $stmt->bind_param("ssssi", $nombre, $telefono, $correo, $direccion, $id_cliente);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Cliente actualizado correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al actualizar: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $mensaje = "<div class='alert error'>❌ El nombre es requerido</div>";
    }
}

// DELETE Cliente
if (isset($_GET['eliminar'])) {
    $id_cliente = $_GET['eliminar'];
    
    // Verificar si el cliente tiene pedidos
    $check = $conn->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE id_cliente=?");
    $check->bind_param("i", $id_cliente);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if ($res['total'] > 0) {
        $mensaje = "<div class='alert error'>❌ No se puede eliminar el cliente porque tiene pedidos asociados</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM clientes WHERE id_cliente=?");
        $stmt->bind_param("i", $id_cliente);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Cliente eliminado correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al eliminar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

$result_clientes = $conn->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Restaurante</title>
    <link rel="stylesheet" href="../css/estilo_admin_mejorado.css">
    
    <!-- Bootstrap 4.6.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Fondo de montañas decorativo -->
    <div class="mountain-background">
        <div class="mountain-range"></div>
        <div class="mountain-range"></div>
        <div class="mountain-range"></div>
    </div>

    <div class="admin-container">
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
                        <h1 class="welcome-title">Gestión de <span class="admin-name">Clientes</span></h1>
                        <p class="welcome-subtitle">Administra los clientes del restaurante</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="user-role"><?php echo $_SESSION['rol']; ?></span>
                    </div>
                </div>
            </div>
            <nav class="main-nav">
                <div class="nav-container">
                    <a href="admin_panel.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        Inicio
                    </a>
                    <a href="categorias.php" class="nav-item">
                        <i class="fas fa-tags"></i>
                        Categorías
                    </a>
                    <a href="productos.php" class="nav-item">
                        <i class="fas fa-utensils"></i>
                        Productos
                    </a>
                    <a href="clientes.php" class="nav-item active">
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

        <main class="admin-main">
            <div class="crud-container">
                <?php echo $mensaje; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Lista de Clientes</h2>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalCliente" id="btnNuevo">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </button>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="quick-stat-item text-center">
                            <h4>Total Clientes</h4>
                            <p><?php echo $result_clientes->num_rows; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-stat-item text-center">
                            <h4>Con Pedidos</h4>
                            <p>
                                <?php
                                $count_con_pedidos = $conn->query("SELECT COUNT(DISTINCT id_cliente) as total FROM pedidos")->fetch_assoc()['total'];
                                echo $count_con_pedidos;
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-stat-item text-center">
                            <h4>Clientes Nuevos (Mes)</h4>
                            <p>
                                <?php
                                $count_nuevos_mes = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())")->fetch_assoc()['total'];
                                echo $count_nuevos_mes;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Cliente -->
                <div class="modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-labelledby="modalClienteLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tituloModal">Agregar Nuevo Cliente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form method="POST" id="formCliente">
                                <div class="modal-body">
                                    <input type="hidden" name="id_cliente" id="id_cliente">

                                    <div class="form-group">
                                        <label for="nombre">Nombre *</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Ej: +1234567890">
                                    </div>

                                    <div class="form-group">
                                        <label for="correo">Correo Electrónico</label>
                                        <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@correo.com">
                                    </div>

                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <textarea id="direccion" name="direccion" class="form-control" rows="3" placeholder="Dirección completa..."></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" name="crear" class="btn btn-primary" id="btnGuardar">Guardar</button>
                                    <button type="submit" name="actualizar" class="btn btn-warning d-none" id="btnActualizar">Actualizar</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tabla Clientes -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result_clientes->num_rows > 0): ?>
                            <?php while($cli = $result_clientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $cli['id_cliente']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cli['nombre']); ?></strong>
                                        <?php if (!empty($cli['direccion'])): ?>
                                            <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo substr($cli['direccion'], 0, 30); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($cli['telefono'])): ?>
                                            <span class="text-primary"><?php echo $cli['telefono']; ?></span>
                                        <?php else: ?>
                                            <em class="text-muted">No especificado</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($cli['correo'])): ?>
                                            <span class="text-info"><?php echo $cli['correo']; ?></span>
                                        <?php else: ?>
                                            <em class="text-muted">No especificado</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cli['fecha_registro'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-info btn-sm btnVerCliente" 
                                                    data-id="<?php echo $cli['id_cliente']; ?>">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button class="btn btn-warning btn-sm btnEditar"
                                                    data-id="<?php echo $cli['id_cliente']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($cli['nombre']); ?>"
                                                    data-telefono="<?php echo htmlspecialchars($cli['telefono']); ?>"
                                                    data-correo="<?php echo htmlspecialchars($cli['correo']); ?>"
                                                    data-direccion="<?php echo htmlspecialchars($cli['direccion']); ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <a href="?eliminar=<?php echo $cli['id_cliente']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    No hay clientes registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Ver Detalles de Cliente -->
    <div class="modal fade" id="modalDetallesCliente" tabindex="-1" aria-labelledby="modalDetallesClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesClienteLabel">Detalles del Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detallesClienteBody">
                    <!-- Los detalles se cargarán aquí via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function(){
        // Nuevo cliente
        $("#btnNuevo").click(function(){
            $("#tituloModal").text("Agregar Nuevo Cliente");
            $("#btnGuardar").removeClass("d-none");
            $("#btnActualizar").addClass("d-none");
            $("#formCliente")[0].reset();
            $("#id_cliente").val('');
        });

        // Editar cliente
        $(".btnEditar").click(function(){
            let id = $(this).data("id");
            let nombre = $(this).data("nombre");
            let telefono = $(this).data("telefono");
            let correo = $(this).data("correo");
            let direccion = $(this).data("direccion");

            $("#id_cliente").val(id);
            $("#nombre").val(nombre);
            $("#telefono").val(telefono);
            $("#correo").val(correo);
            $("#direccion").val(direccion);

            $("#tituloModal").text("Editar Cliente");
            $("#btnGuardar").addClass("d-none");
            $("#btnActualizar").removeClass("d-none");

            $("#modalCliente").modal("show");
        });

        // Ver detalles del cliente
        $(".btnVerCliente").click(function(){
            const clienteId = $(this).data('id');
            
            // Mostrar loading
            $('#detallesClienteBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles del cliente...</p>
                </div>
            `);
            
            $('#modalDetallesCliente').modal('show');
            
            // Cargar datos via AJAX
            $.ajax({
                url: 'obtener_detalles_cliente.php',
                type: 'POST',
                data: { id_cliente: clienteId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const cliente = response.data;
                        let html = `
                            <div class="detalle-grid">
                                <div class="detalle-item">
                                    <div class="detalle-label">ID</div>
                                    <div class="detalle-valor">#${cliente.id_cliente}</div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Nombre</div>
                                    <div class="detalle-valor">${cliente.nombre}</div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Teléfono</div>
                                    <div class="detalle-valor">${cliente.telefono || 'No especificado'}</div>
                                </div>
                            </div>
                            
                            <div class="detalle-grid">
                                <div class="detalle-item">
                                    <div class="detalle-label">Correo</div>
                                    <div class="detalle-valor">${cliente.correo || 'No especificado'}</div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Fecha Registro</div>
                                    <div class="detalle-valor">${new Date(cliente.fecha_registro).toLocaleDateString()}</div>
                                </div>
                            </div>
                        `;
                        
                        if (cliente.direccion) {
                            html += `
                                <div class="detalle-item">
                                    <div class="detalle-label">Dirección</div>
                                    <div class="detalle-valor">${cliente.direccion}</div>
                                </div>
                            `;
                        }
                        
                        // Obtener estadísticas del cliente
                        $.ajax({
                            url: 'obtener_estadisticas_cliente.php',
                            type: 'POST',
                            data: { id_cliente: clienteId },
                            dataType: 'json',
                            success: function(stats) {
                                if (stats.success) {
                                    html += `
                                        <div class="detalle-item">
                                            <div class="detalle-label">Estadísticas</div>
                                            <div class="detalle-valor">
                                                <div class="row text-center">
                                                    <div class="col-md-4">
                                                        <h5 class="text-primary">${stats.total_pedidos}</h5>
                                                        <small>Total Pedidos</small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h5 class="text-success">$${parseFloat(stats.total_gastado).toFixed(2)}</h5>
                                                        <small>Total Gastado</small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h5 class="text-info">${stats.ultimo_pedido}</h5>
                                                        <small>Último Pedido</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }
                                $('#detallesClienteBody').html(html);
                            },
                            error: function() {
                                $('#detallesClienteBody').html(html);
                            }
                        });
                    } else {
                        $('#detallesClienteBody').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del cliente
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#detallesClienteBody').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del cliente
                        </div>
                    `);
                }
            });
        });

        // Cerrar modal y limpiar
        $('#modalCliente').on('hidden.bs.modal', function () {
            $("#formCliente")[0].reset();
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>