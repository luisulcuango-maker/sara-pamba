<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$id_personal = $_SESSION['id_personal'];

// Obtener parámetros de filtro
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_metodo = isset($_GET['metodo']) ? $_GET['metodo'] : '';
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$filtro_id = isset($_GET['id_pedido']) ? $_GET['id_pedido'] : '';

// Construir la consulta base
$sql = "
    SELECT p.*, c.nombre as cliente_nombre,
           COUNT(dp.id_detalle) as total_items,
           SUM(dp.cantidad) as total_cantidad
    FROM pedidos p
    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
    LEFT JOIN detalles_pedido dp ON p.id_pedido = dp.id_pedido
    WHERE p.id_personal = $id_personal
";

// Aplicar filtros
$condiciones = [];

if (!empty($filtro_estado) && $filtro_estado != 'todos') {
    $condiciones[] = "p.estado = '" . $conn->real_escape_string($filtro_estado) . "'";
}

if (!empty($filtro_metodo) && $filtro_metodo != 'todos') {
    $condiciones[] = "p.metodo_pago = '" . $conn->real_escape_string($filtro_metodo) . "'";
}

if (!empty($filtro_cliente)) {
    $condiciones[] = "c.nombre LIKE '%" . $conn->real_escape_string($filtro_cliente) . "%'";
}

if (!empty($filtro_id)) {
    $condiciones[] = "p.id_pedido = " . intval($filtro_id);
}

if (!empty($filtro_fecha_desde)) {
    $condiciones[] = "DATE(p.fecha_hora) >= '" . $conn->real_escape_string($filtro_fecha_desde) . "'";
}

if (!empty($filtro_fecha_hasta)) {
    $condiciones[] = "DATE(p.fecha_hora) <= '" . $conn->real_escape_string($filtro_fecha_hasta) . "'";
}

// Agregar condiciones a la consulta
if (!empty($condiciones)) {
    $sql .= " AND " . implode(" AND ", $condiciones);
}

// Agrupar y ordenar
$sql .= " GROUP BY p.id_pedido ORDER BY p.fecha_hora DESC";

$pedidos = $conn->query($sql);

// Obtener estadísticas para mostrar en los filtros
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total_pedidos,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados,
        SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
        SUM(CASE WHEN estado = 'proceso' THEN 1 ELSE 0 END) as proceso
    FROM pedidos 
    WHERE id_personal = $id_personal
");
$stats = $stats_query ? $stats_query->fetch_assoc() : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Restaurante 8 MIL</title>
    <link rel="stylesheet" href="../css/estilo_insertar.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
<div class="admin-container">
    <header class="admin-header">
        <div class="header-content">
            <div class="welcome-section">
                <div class="restaurant-logo">
                    <i class="fas fa-mountain flame-icon"></i>
                    8 MIL
                </div>
                <h1 class="welcome-title">Mis <span class="admin-name">Pedidos</span></h1>
                <p class="welcome-subtitle">Historial de pedidos realizados</p>
            </div>
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                    </div>
                    <span class="user-role"><?php echo $_SESSION['nombre']; ?></span>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="nav-container">
                <a href="user_panel.php" class="nav-item">Inicio</a>
                <a href="pos.php" class="nav-item">Punto de Venta</a>
                <a href="mis_pedidos.php" class="nav-item active">Mis Pedidos</a>
                <a href="../php/logout.php" class="nav-item logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
    </header>

    <main class="admin-main">
        <div class="crud-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title">Historial de Pedidos</h2>
                <div>
                    <button type="button" class="btn btn-primary mr-2" id="btnMostrarFiltros">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                    <a href="pos.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Nuevo Pedido
                    </a>
                </div>
            </div>

            <!-- Panel de Filtros -->
            <div class="filtros-panel mb-4" id="panelFiltros" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-sliders-h text-primary"></i> Filtros de Búsqueda
                        </h5>
                        <form method="GET" action="" class="row g-3">
                            <!-- Fila 1 -->
                            <div class="col-md-3">
                                <label for="filtro_id" class="form-label small">ID del Pedido</label>
                                <input type="number" class="form-control form-control-sm" id="filtro_id" 
                                       name="id_pedido" value="<?php echo htmlspecialchars($filtro_id); ?>" 
                                       placeholder="Ej: 123">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filtro_cliente" class="form-label small">Cliente</label>
                                <input type="text" class="form-control form-control-sm" id="filtro_cliente" 
                                       name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>" 
                                       placeholder="Nombre del cliente">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filtro_estado" class="form-label small">Estado</label>
                                <select class="form-control form-control-sm" id="filtro_estado" name="estado">
                                    <option value="todos">Todos los estados</option>
                                    <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="proceso" <?php echo $filtro_estado == 'proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                    <option value="completado" <?php echo $filtro_estado == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                    <option value="cancelado" <?php echo $filtro_estado == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filtro_metodo" class="form-label small">Método de Pago</label>
                                <select class="form-control form-control-sm" id="filtro_metodo" name="metodo">
                                    <option value="todos">Todos los métodos</option>
                                    <option value="efectivo" <?php echo $filtro_metodo == 'efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                                    <option value="tarjeta" <?php echo $filtro_metodo == 'tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                                    <option value="sinpe" <?php echo $filtro_metodo == 'sinpe' ? 'selected' : ''; ?>>Sinpe</option>
                                </select>
                            </div>
                            
                            <!-- Fila 2 -->
                            <div class="col-md-3">
                                <label for="filtro_fecha_desde" class="form-label small">Fecha Desde</label>
                                <input type="text" class="form-control form-control-sm datepicker" 
                                       id="filtro_fecha_desde" name="fecha_desde" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>"
                                       placeholder="dd/mm/aaaa">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="filtro_fecha_hasta" class="form-label small">Fecha Hasta</label>
                                <input type="text" class="form-control form-control-sm datepicker" 
                                       id="filtro_fecha_hasta" name="fecha_hasta" 
                                       value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>"
                                       placeholder="dd/mm/aaaa">
                            </div>
                            
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                        <i class="fas fa-search"></i> Aplicar Filtros
                                    </button>
                                    <a href="mis_pedidos.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Estadísticas Rápidas -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-chart-bar"></i> Resumen de Pedidos
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge badge-primary">
                                        Total: <?php echo $stats['total_pedidos'] ?? 0; ?>
                                    </span>
                                    <span class="badge badge-warning">
                                        Pendientes: <?php echo $stats['pendientes'] ?? 0; ?>
                                    </span>
                                    <span class="badge" style="background-color: #8B4513; color: white;">
                                        En Proceso: <?php echo $stats['proceso'] ?? 0; ?>
                                    </span>
                                    <span class="badge badge-success">
                                        Completados: <?php echo $stats['completados'] ?? 0; ?>
                                    </span>
                                    <span class="badge badge-danger">
                                        Cancelados: <?php echo $stats['cancelados'] ?? 0; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mostrar filtros activos -->
            <?php if (!empty($filtro_estado) || !empty($filtro_metodo) || !empty($filtro_cliente) || !empty($filtro_fecha_desde) || !empty($filtro_id)): ?>
            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-info-circle"></i> 
                <strong>Filtros aplicados:</strong>
                <?php
                $filtros_aplicados = [];
                if (!empty($filtro_id)) $filtros_aplicados[] = "ID: $filtro_id";
                if (!empty($filtro_cliente)) $filtros_aplicados[] = "Cliente: $filtro_cliente";
                if (!empty($filtro_estado) && $filtro_estado != 'todos') $filtros_aplicados[] = "Estado: " . ucfirst($filtro_estado);
                if (!empty($filtro_metodo) && $filtro_metodo != 'todos') $filtros_aplicados[] = "Método: " . ucfirst($filtro_metodo);
                if (!empty($filtro_fecha_desde)) $filtros_aplicados[] = "Desde: $filtro_fecha_desde";
                if (!empty($filtro_fecha_hasta)) $filtros_aplicados[] = "Hasta: $filtro_fecha_hasta";
                
                echo implode(' • ', $filtros_aplicados);
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Cliente</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Método Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pedidos->num_rows > 0): ?>
                        <?php while($pedido = $pedidos->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $pedido['id_pedido']; ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_hora'])); ?></td>
                                <td><?php echo $pedido['cliente_nombre'] ?: '<em class="text-muted">Cliente no registrado</em>'; ?></td>
                                <td><?php echo $pedido['total_items']; ?> items</td>
                                <td><strong>₡<?php echo number_format($pedido['total'], 2); ?></strong></td>
                                <td>
                                    <span class="payment-badge metodo-<?php echo $pedido['metodo_pago']; ?>">
                                        <i class="fas fa-credit-card"></i> <?php echo ucfirst($pedido['metodo_pago']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge estado-<?php echo $pedido['estado']; ?>">
                                        <?php echo ucfirst($pedido['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-detalles" 
                                            onclick="verDetalles(<?php echo $pedido['id_pedido']; ?>)"
                                            data-pedido-id="<?php echo $pedido['id_pedido']; ?>">
                                        <i class="fas fa-eye"></i> Detalles
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                <?php if (!empty($condiciones)): ?>
                                    No se encontraron pedidos con los filtros aplicados.
                                <?php else: ?>
                                    No has realizado ningún pedido.
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

<!-- Modal para detalles del pedido -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt text-primary"></i>
                    Detalles del Pedido #<span id="modalPedidoId"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetalles">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando detalles del pedido...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Datepicker -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
// Mostrar/ocultar panel de filtros
$('#btnMostrarFiltros').click(function() {
    $('#panelFiltros').slideToggle();
    $(this).find('i').toggleClass('fa-filter fa-times');
});

// Configurar datepicker
flatpickr(".datepicker", {
    dateFormat: "Y-m-d",
    locale: "es",
    allowInput: true
});

function verDetalles(idPedido) {
    // Mostrar el modal inmediatamente
    $('#modalPedidoId').text(idPedido);
    $('#detallesModal').modal('show');
    
    // Realizar petición AJAX para obtener los detalles
    $.ajax({
        url: 'obtener_detalles_pedido.php',
        type: 'POST',
        data: { id_pedido: idPedido },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarDetalles(response.data);
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            mostrarError('Error al cargar los detalles del pedido.');
        }
    });
}

function mostrarDetalles(datos) {
    let html = `
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="detail-card">
                    <h6><i class="fas fa-calendar-alt text-primary"></i> Fecha y Hora</h6>
                    <p>${datos.pedido.fecha_hora_formatted}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-card">
                    <h6><i class="fas fa-user text-primary"></i> Cliente</h6>
                    <p>${datos.pedido.cliente_nombre || 'Cliente no registrado'}</p>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="detail-card">
                    <h6><i class="fas fa-credit-card text-primary"></i> Método de Pago</h6>
                    <p><span class="payment-badge metodo-${datos.pedido.metodo_pago}">${datos.pedido.metodo_pago_formatted}</span></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-card">
                    <h6><i class="fas fa-tag text-primary"></i> Estado</h6>
                    <p><span class="status-badge estado-${datos.pedido.estado}">${datos.pedido.estado_formatted}</span></p>
                </div>
            </div>
        </div>
        
        <div class="products-section">
            <h6 class="section-subtitle mb-3">
                <i class="fas fa-list-ul text-primary"></i> Productos del Pedido
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Precio Unit.</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    // Agregar productos
    datos.productos.forEach(producto => {
        html += `
            <tr>
                <td>
                    <strong>${producto.nombre_producto}</strong>
                    ${producto.notas ? `<br><small class="text-muted"><i>Notas: ${producto.notas}</i></small>` : ''}
                </td>
                <td class="text-center">${producto.cantidad}</td>
                <td class="text-right">₡${parseFloat(producto.precio_unitario).toFixed(2)}</td>
                <td class="text-right">₡${parseFloat(producto.subtotal).toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right"><strong>₡${parseFloat(datos.pedido.total).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    `;
    
    // Si hay notas generales del pedido, mostrarlas
    if (datos.pedido.notas) {
        html += `
        <div class="notes-section mt-4">
            <h6 class="section-subtitle mb-2">
                <i class="fas fa-sticky-note text-primary"></i> Notas del Pedido
            </h6>
            <div class="alert alert-light border">
                ${datos.pedido.notas}
            </div>
        </div>
        `;
    }
    
    $('#modalDetalles').html(html);
}

function mostrarError(mensaje) {
    $('#modalDetalles').html(`
        <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
            <h5>Error</h5>
            <p>${mensaje}</p>
        </div>
    `);
}

// Cerrar modal cuando se oculta
$('#detallesModal').on('hidden.bs.modal', function () {
    $('#modalDetalles').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando detalles del pedido...</p>
        </div>
    `);
});
</script>

<style>
.estado-pendiente { background: var(--warning-gradient); color: white; }
.estado-completado { background: var(--success-gradient); color: white; }
.estado-cancelado { background: var(--danger-gradient); color: white; }
.estado-proceso { background: var(--brown-gradient); color: white; }

.metodo-efectivo { background: var(--success-gradient); color: white; }
.metodo-tarjeta { background: var(--primary-gradient); color: white; }
.metodo-sinpe { background: var(--brown-gradient); color: white; }

.detail-card {
    background: var(--stone-50);
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
    margin-bottom: 1rem;
}

.detail-card h6 {
    color: var(--stone-700);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.detail-card p {
    color: var(--stone-900);
    font-weight: 500;
    margin: 0;
}

.section-subtitle {
    color: var(--stone-700);
    font-weight: 600;
    border-bottom: 2px solid var(--stone-200);
    padding-bottom: 0.5rem;
}

.payment-badge, .status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-detalles {
    background: var(--brown-gradient);
    border: none;
    color: white;
    transition: all var(--transition-normal);
}

.btn-detalles:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.notes-section .alert {
    background: var(--stone-50);
    border-color: var(--stone-300);
    color: var(--stone-700);
}

/* Mejoras para la tabla en el modal */
.table-responsive {
    border-radius: 8px;
    border: 1px solid var(--stone-200);
}

.table-responsive table {
    margin-bottom: 0;
}

.table-responsive th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table-responsive td {
    vertical-align: middle;
}

/* Estilos adicionales para los filtros */
.filtros-panel {
    transition: all 0.3s ease;
}

.datepicker {
    cursor: pointer;
}

.btn-group .btn {
    border-radius: 4px !important;
}

/* Mejoras visuales para los filtros */
.form-label {
    font-weight: 500;
    color: var(--stone-700);
}

.card {
    background: var(--stone-50);
    border: 1px solid var(--stone-200);
}

/* Animación para el panel de filtros */
.filtros-panel {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para badges de estadísticas */
.badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
}
</style>
</body>
</html>
<style>
/* Variables CSS - Debe coincidir con estilo.css */
:root {
  --primary: #2c5530;
  --primary-dark: #1e3a24;
  --primary-light: #4a7c59;
  --secondary: #d4a017;
  --accent: #8f7547;
  --danger: #c53030;
  --warning: #d4a017;
  --success: #38a169;
  --brown-light: #a78c5d;
  --brown-dark: #5d4037;
  --stone-50: #fafaf9;
  --stone-100: #f5f5f4;
  --stone-200: #e7e5e4;
  --stone-300: #d6d3d1;
  --stone-400: #a8a29e;
  --stone-500: #78716c;
  --stone-600: #57534e;
  --stone-700: #44403c;
  --stone-800: #292524;
  --stone-900: #1c1917;
  --primary-gradient: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  --accent-gradient: linear-gradient(135deg, var(--secondary) 0%, #e5b227 100%);
  --success-gradient: linear-gradient(135deg, var(--success) 0%, #48bb78 100%);
  --warning-gradient: linear-gradient(135deg, var(--warning) 0%, #e5b227 100%);
  --danger-gradient: linear-gradient(135deg, var(--danger) 0%, #e53e3e 100%);
  --brown-gradient: linear-gradient(135deg, var(--accent) 0%, var(--brown-light) 100%);
  --transition-normal: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
  --shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.12);
}

/* Badges consistentes */
.estado-pendiente { 
    background: var(--warning-gradient); 
    color: white; 
}
.estado-completado { 
    background: var(--success-gradient); 
    color: white; 
}
.estado-cancelado { 
    background: var(--danger-gradient); 
    color: white; 
}
.estado-proceso { 
    background: var(--brown-gradient); 
    color: white; 
}

.metodo-efectivo { 
    background: var(--success-gradient); 
    color: white; 
}
.metodo-tarjeta { 
    background: var(--primary-gradient); 
    color: white; 
}
.metodo-sinpe { 
    background: var(--brown-gradient); 
    color: white; 
}

.detail-card {
    background: var(--stone-50);
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
    margin-bottom: 1rem;
    border: 1px solid var(--stone-200);
}

.detail-card h6 {
    color: var(--stone-700);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.detail-card p {
    color: var(--stone-900);
    font-weight: 500;
    margin: 0;
}

.section-subtitle {
    color: var(--stone-700);
    font-weight: 600;
    border-bottom: 2px solid var(--stone-200);
    padding-bottom: 0.5rem;
}

.payment-badge, .status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-block;
    min-width: 100px;
    text-align: center;
}

.btn-detalles {
    background: var(--brown-gradient);
    border: none;
    color: white;
    transition: all var(--transition-normal);
}

.btn-detalles:hover {
    background: #7a6140;
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.notes-section .alert {
    background: var(--stone-50);
    border-color: var(--stone-300);
    color: var(--stone-700);
}

/* Mejoras para la tabla en el modal */
.table-responsive {
    border-radius: 8px;
    border: 1px solid var(--stone-200);
    background: white;
}

.table-responsive table {
    margin-bottom: 0;
}

.table-responsive th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: var(--stone-50);
}

.table-responsive td {
    vertical-align: middle;
}

/* Estilos adicionales para los filtros */
.filtros-panel {
    transition: all 0.3s ease;
}

.datepicker {
    cursor: pointer;
}

.btn-group .btn {
    border-radius: 4px !important;
}

/* Mejoras visuales para los filtros */
.form-label {
    font-weight: 500;
    color: var(--stone-700);
}

.card {
    background: var(--stone-50);
    border: 1px solid var(--stone-200);
}

/* Animación para el panel de filtros */
.filtros-panel {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Estilos para badges de estadísticas */
.badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
}

.badge-primary {
    background: var(--primary-gradient);
}

.badge-success {
    background: var(--success-gradient);
}

.badge-warning {
    background: var(--warning-gradient);
}

.badge-danger {
    background: var(--danger-gradient);
}

/* Estilo especial para badge de proceso */
.badge[style*="8B4513"] {
    background: var(--brown-gradient) !important;
    color: white !important;
}
</style>

<?php $conn->close(); ?>