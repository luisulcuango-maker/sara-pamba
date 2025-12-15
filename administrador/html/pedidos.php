<?php
include("../../conexion.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$mensaje = "";

// Procesar cambio de estado del pedido
if (isset($_GET['cambiar_estado'])) {
    $id_pedido = $_GET['cambiar_estado'];
    $nuevo_estado = $_GET['estado'];
    
    $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);
    
    if ($stmt->execute()) {
        $mensaje = "<div class='alert success'>✅ Estado del pedido actualizado correctamente</div>";
    } else {
        $mensaje = "<div class='alert error'>❌ Error al actualizar el estado: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Obtener parámetros de filtro
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';

// Construir consulta con filtros
$sql_pedidos = "SELECT p.*, c.nombre as cliente_nombre, per.nombre as personal_nombre 
                FROM pedidos p 
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                LEFT JOIN personal per ON p.id_personal = per.id_personal 
                WHERE 1=1";

$params = [];
$types = "";

// Aplicar filtro de estado
if (!empty($filtro_estado)) {
    $sql_pedidos .= " AND p.estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

// Aplicar filtro de fecha
if (!empty($filtro_fecha)) {
    $sql_pedidos .= " AND DATE(p.fecha_hora) = ?";
    $params[] = $filtro_fecha;
    $types .= "s";
}

// Aplicar filtro de cliente
if (!empty($filtro_cliente)) {
    $sql_pedidos .= " AND c.nombre LIKE ?";
    $params[] = "%" . $filtro_cliente . "%";
    $types .= "s";
}

$sql_pedidos .= " ORDER BY p.fecha_hora DESC";

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql_pedidos);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_pedidos = $stmt->get_result();

// Obtener detalles del pedido si se solicita
$detalles_pedido = [];
if (isset($_GET['ver_pedido'])) {
    $id_pedido = $_GET['ver_pedido'];
    
    // Información general del pedido
    $sql_pedido = "SELECT p.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono, 
                          c.direccion as cliente_direccion, per.nombre as personal_nombre
                   FROM pedidos p 
                   LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                   LEFT JOIN personal per ON p.id_personal = per.id_personal 
                   WHERE p.id_pedido = ?";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("i", $id_pedido);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();
    
    if ($result_pedido->num_rows > 0) {
        $detalles_pedido['info'] = $result_pedido->fetch_assoc();
        
        // Items del pedido
        $sql_items = "SELECT dp.*, pr.nombre as producto_nombre 
                      FROM detalles_pedido dp 
                      INNER JOIN productos pr ON dp.id_producto = pr.id_producto 
                      WHERE dp.id_pedido = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $id_pedido);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        
        $detalles_pedido['items'] = [];
        while($item = $result_items->fetch_assoc()) {
            $detalles_pedido['items'][] = $item;
        }
        $stmt_items->close();
    }
    $stmt_pedido->close();
}

// Obtener totales para estadísticas
$total_pedidos = $result_pedidos->num_rows;
$count_pendientes = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'")->fetch_assoc()['total'];
$count_completados = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'completado'")->fetch_assoc()['total'];
$ventas_hoy = $conn->query("SELECT SUM(total) as total FROM pedidos WHERE DATE(fecha_hora) = CURDATE() AND estado = 'completado'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Restaurante</title>
    
    <!-- Bootstrap 4.6.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
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
                        <h1 class="welcome-title">Gestión de <span class="admin-name">Pedidos</span></h1>
                        <p class="welcome-subtitle">Administra los pedidos del restaurante</p>
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
                    <a href="clientes.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                    <a href="pedidos.php" class="nav-item active">
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
                    <h2 class="section-title">Lista de Pedidos</h2>
                    <div class="btn-group">
                        <a href="pedidos.php" class="btn btn-outline-primary <?php echo empty($filtro_estado) ? 'active' : ''; ?>">Todos</a>
                        <a href="pedidos.php?estado=pendiente" class="btn btn-outline-warning <?php echo $filtro_estado == 'pendiente' ? 'active' : ''; ?>">Pendientes</a>
                        <a href="pedidos.php?estado=completado" class="btn btn-outline-success <?php echo $filtro_estado == 'completado' ? 'active' : ''; ?>">Completados</a>
                        <a href="pedidos.php?estado=cancelado" class="btn btn-outline-danger <?php echo $filtro_estado == 'cancelado' ? 'active' : ''; ?>">Cancelados</a>
                    </div>
                </div>

                <!-- Filtros Avanzados -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros Avanzados</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="filtro_estado" class="form-label">Estado</label>
                                <select class="form-control" id="filtro_estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="completado" <?php echo $filtro_estado == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                    <option value="cancelado" <?php echo $filtro_estado == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filtro_fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="filtro_fecha" name="fecha" value="<?php echo $filtro_fecha; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="filtro_cliente" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="filtro_cliente" name="cliente" value="<?php echo $filtro_cliente; ?>" placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Aplicar Filtros
                                    </button>
                                    <a href="pedidos.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar Filtros
                                    </a>
                                    <?php if (!empty($filtro_estado) || !empty($filtro_fecha) || !empty($filtro_cliente)): ?>
                                        <span class="badge badge-info align-self-center">
                                            <i class="fas fa-filter"></i> Filtros activos
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="quick-stat-item text-center">
                            <h4>Total Pedidos</h4>
                            <p><?php echo $total_pedidos; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="quick-stat-item text-center">
                            <h4>Pendientes</h4>
                            <p><?php echo $count_pendientes; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="quick-stat-item text-center">
                            <h4>Completados</h4>
                            <p><?php echo $count_completados; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="quick-stat-item text-center">
                            <h4>Ventas Hoy</h4>
                            <p>$<?php echo number_format($ventas_hoy ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Información de Filtros Aplicados -->
                <?php if (!empty($filtro_estado) || !empty($filtro_fecha) || !empty($filtro_cliente)): ?>
                <div class="alert alert-info mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fas fa-info-circle"></i> Filtros aplicados:</strong>
                            <?php
                            $filtros_texto = [];
                            if (!empty($filtro_estado)) $filtros_texto[] = "Estado: " . ucfirst($filtro_estado);
                            if (!empty($filtro_fecha)) $filtros_texto[] = "Fecha: " . $filtro_fecha;
                            if (!empty($filtro_cliente)) $filtros_texto[] = "Cliente: " . $filtro_cliente;
                            echo implode(" • ", $filtros_texto);
                            ?>
                            <span class="badge badge-light ml-2"><?php echo $total_pedidos; ?> resultados</span>
                        </div>
                        <a href="pedidos.php" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabla Pedidos -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha/Hora</th>
                                <th>Total</th>
                                <th>Método Pago</th>
                                <th>Estado</th>
                                <th>Atendió</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result_pedidos->num_rows > 0): ?>
                            <?php while($pedido = $result_pedidos->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $pedido['id_pedido']; ?></td>
                                    <td>
                                        <?php if (!empty($pedido['cliente_nombre'])): ?>
                                            <?php echo htmlspecialchars($pedido['cliente_nombre']); ?>
                                        <?php else: ?>
                                            <em class="text-muted">Cliente no registrado</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_hora'])); ?></td>
                                    <td><strong>$<?php echo number_format($pedido['total'], 2); ?></strong></td>
                                    <td>
                                        <span class="text-capitalize"><?php echo $pedido['metodo_pago']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($pedido['estado'] == 'pendiente'): ?>
                                            <span class="estado-pendiente">Pendiente</span>
                                        <?php elseif ($pedido['estado'] == 'completado'): ?>
                                            <span class="estado-completado">Completado</span>
                                        <?php else: ?>
                                            <span class="estado-cancelado">Cancelado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $pedido['personal_nombre']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?ver_pedido=<?php echo $pedido['id_pedido']; ?><?php echo !empty($filtro_estado) ? '&estado=' . $filtro_estado : ''; ?><?php echo !empty($filtro_fecha) ? '&fecha=' . $filtro_fecha : ''; ?><?php echo !empty($filtro_cliente) ? '&cliente=' . $filtro_cliente : ''; ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <?php if ($pedido['estado'] == 'pendiente'): ?>
                                                <a href="?cambiar_estado=<?php echo $pedido['id_pedido']; ?>&estado=completado<?php echo !empty($filtro_estado) ? '&estado=' . $filtro_estado : ''; ?><?php echo !empty($filtro_fecha) ? '&fecha=' . $filtro_fecha : ''; ?><?php echo !empty($filtro_cliente) ? '&cliente=' . $filtro_cliente : ''; ?>" 
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('¿Marcar pedido como completado?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="?cambiar_estado=<?php echo $pedido['id_pedido']; ?>&estado=cancelado<?php echo !empty($filtro_estado) ? '&estado=' . $filtro_estado : ''; ?><?php echo !empty($filtro_fecha) ? '&fecha=' . $filtro_fecha : ''; ?><?php echo !empty($filtro_cliente) ? '&cliente=' . $filtro_cliente : ''; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('¿Cancelar este pedido?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                                    <?php if (!empty($filtro_estado) || !empty($filtro_fecha) || !empty($filtro_cliente)): ?>
                                        No se encontraron pedidos con los filtros aplicados.
                                        <div class="mt-2">
                                            <a href="pedidos.php" class="btn btn-primary btn-sm">
                                                <i class="fas fa-times"></i> Limpiar filtros
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        No hay pedidos registrados.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para ver detalles del pedido con Bootstrap -->
    <?php if (isset($_GET['ver_pedido']) && !empty($detalles_pedido)): ?>
    <div class="modal fade show" id="modalDetallesPedido" tabindex="-1" aria-labelledby="modalDetallesPedidoLabel" aria-hidden="false" style="display: block; padding-right: 17px;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesPedidoLabel">
                        <i class="fas fa-shopping-cart"></i> Detalles del Pedido #<?php echo $detalles_pedido['info']['id_pedido']; ?>
                    </h5>
                    <button type="button" class="close" onclick="cerrarModal()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php $pedido = $detalles_pedido['info']; ?>
                    <?php $items = $detalles_pedido['items']; ?>
                    
                    <!-- Información general del pedido -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-info-circle"></i> Información del Pedido</h6>
                                <div class="detalle-grid">
                                    <div>
                                        <div class="detalle-label">ID</div>
                                        <div class="detalle-valor">#<?php echo $pedido['id_pedido']; ?></div>
                                    </div>
                                    <div>
                                        <div class="detalle-label">Fecha y Hora</div>
                                        <div class="detalle-valor"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_hora'])); ?></div>
                                    </div>
                                    <div>
                                        <div class="detalle-label">Estado</div>
                                        <div class="detalle-valor">
                                            <?php if ($pedido['estado'] == 'pendiente'): ?>
                                                <span class="estado-pendiente">Pendiente</span>
                                            <?php elseif ($pedido['estado'] == 'completado'): ?>
                                                <span class="estado-completado">Completado</span>
                                            <?php else: ?>
                                                <span class="estado-cancelado">Cancelado</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="detalle-label">Método de Pago</div>
                                        <div class="detalle-valor text-capitalize"><?php echo $pedido['metodo_pago']; ?></div>
                                    </div>
                                    <div>
                                        <div class="detalle-label">Total</div>
                                        <div class="detalle-valor text-success font-weight-bold">$<?php echo number_format($pedido['total'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <h6 class="font-weight-bold text-primary"><i class="fas fa-user"></i> Información del Cliente</h6>
                                <div class="detalle-grid">
                                    <div>
                                        <div class="detalle-label">Cliente</div>
                                        <div class="detalle-valor"><?php echo $pedido['cliente_nombre'] ?: 'Cliente no registrado'; ?></div>
                                    </div>
                                    <?php if ($pedido['cliente_telefono']): ?>
                                    <div>
                                        <div class="detalle-label">Teléfono</div>
                                        <div class="detalle-valor"><?php echo $pedido['cliente_telefono']; ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="detalle-label">Atendió</div>
                                        <div class="detalle-valor"><?php echo $pedido['personal_nombre']; ?></div>
                                    </div>
                                </div>
                                <?php if ($pedido['cliente_direccion']): ?>
                                <div class="mt-2">
                                    <div class="detalle-label">Dirección</div>
                                    <div class="detalle-valor"><?php echo $pedido['cliente_direccion']; ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Items del pedido -->
                    <div class="detalle-item">
                        <h6 class="font-weight-bold text-primary"><i class="fas fa-utensils"></i> Productos del Pedido</h6>
                        <div class="table-responsive">
                            <table class="table detalle-tabla">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-right">Precio Unitario</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach($items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['producto_nombre']; ?></td>
                                                <td class="text-center"><?php echo $item['cantidad']; ?></td>
                                                <td class="text-right">$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                                                <td class="text-right">$<?php echo number_format($item['cantidad'] * $item['precio_unitario'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No hay productos en este pedido</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-right font-weight-bold">Total:</td>
                                        <td class="text-right font-weight-bold">$<?php echo number_format($pedido['total'], 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <?php if (!empty($pedido['observaciones'])): ?>
                        <div class="detalle-item">
                            <h6 class="font-weight-bold text-primary"><i class="fas fa-sticky-note"></i> Observaciones</h6>
                            <div class="alert alert-info">
                                <?php echo nl2br(htmlspecialchars($pedido['observaciones'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
                    <?php if ($pedido['estado'] == 'pendiente'): ?>
                        <a href="?cambiar_estado=<?php echo $pedido['id_pedido']; ?>&estado=completado<?php echo !empty($filtro_estado) ? '&estado=' . $filtro_estado : ''; ?><?php echo !empty($filtro_fecha) ? '&fecha=' . $filtro_fecha : ''; ?><?php echo !empty($filtro_cliente) ? '&cliente=' . $filtro_cliente : ''; ?>" 
                           class="btn btn-success"
                           onclick="return confirm('¿Marcar pedido como completado?')">
                            <i class="fas fa-check"></i> Completar Pedido
                        </a>
                        <a href="?cambiar_estado=<?php echo $pedido['id_pedido']; ?>&estado=cancelado<?php echo !empty($filtro_estado) ? '&estado=' . $filtro_estado : ''; ?><?php echo !empty($filtro_fecha) ? '&fecha=' . $filtro_fecha : ''; ?><?php echo !empty($filtro_cliente) ? '&cliente=' . $filtro_cliente : ''; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('¿Cancelar este pedido?')">
                            <i class="fas fa-times"></i> Cancelar Pedido
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>

    <script>
    function cerrarModal() {
        window.location.href = 'pedidos.php?<?php 
            echo !empty($filtro_estado) ? 'estado=' . $filtro_estado . '&' : '';
            echo !empty($filtro_fecha) ? 'fecha=' . $filtro_fecha . '&' : '';
            echo !empty($filtro_cliente) ? 'cliente=' . $filtro_cliente : '';
        ?>';
    }
    </script>
    <?php endif; ?>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Auto-ocultar mensajes después de 5 segundos
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Cerrar modal con ESC
        $(document).keyup(function(e) {
            if (e.keyCode === 27 && window.location.href.indexOf('ver_pedido') > -1) {
                cerrarModal();
            }
        });

        // Mostrar filtro de fecha como hoy por defecto si está vacío
        $('#filtro_fecha').on('focus', function() {
            if (!this.value) {
                this.value = new Date().toISOString().split('T')[0];
            }
        });
    });

    function cerrarModal() {
        const urlParams = new URLSearchParams(window.location.search);
        let returnUrl = 'pedidos.php';
        
        // Mantener los parámetros de filtro
        if (urlParams.has('estado')) returnUrl += '?estado=' + urlParams.get('estado');
        if (urlParams.has('fecha')) returnUrl += (returnUrl.includes('?') ? '&' : '?') + 'fecha=' + urlParams.get('fecha');
        if (urlParams.has('cliente')) returnUrl += (returnUrl.includes('?') ? '&' : '?') + 'cliente=' + urlParams.get('cliente');
        
        window.location.href = returnUrl;
    }
    </script>
</body>
</html>

<?php 
$stmt->close();
$conn->close(); 
?>