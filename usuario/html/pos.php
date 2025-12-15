<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion.php';

$mensaje = "";

// DEBUG: Mostrar datos POST si existen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['realizar_pedido'])) {
    error_log("=== DATOS POST RECIBIDOS ===");
    error_log("ID Cliente: " . ($_POST['id_cliente'] ?? 'NULL'));
    error_log("Método pago: " . ($_POST['metodo_pago'] ?? 'N/A'));
    error_log("Cambio: " . ($_POST['cambio'] ?? '0'));
    
    $id_cliente = !empty($_POST['id_cliente']) ? intval($_POST['id_cliente']) : NULL;
    $metodo_pago = $_POST['metodo_pago'];
    $cambio = floatval($_POST['cambio'] ?? 0);
    
    // Obtener items del formulario
    $items = [];
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        $items = $_POST['items'];
        error_log("Items recibidos: " . count($items));
    }
    
    // Calcular total
    $total = 0;
    foreach ($items as $index => $item) {
        if (isset($item['id_producto'], $item['cantidad'], $item['precio'])) {
            $precio = floatval($item['precio']);
            $cantidad = intval($item['cantidad']);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            error_log("Item $index: ID=" . $item['id_producto'] . ", Cantidad=$cantidad, Precio=$precio, Subtotal=$subtotal");
        }
    }
    
    error_log("Total calculado: $total");
    error_log("Cambio: $cambio");
    
    if ($total > 0) {
        // Insertar pedido
        $stmt = $conn->prepare("INSERT INTO Pedidos (fecha_hora, total, metodo_pago, id_personal, id_cliente, estado) VALUES (NOW(), ?, ?, ?, ?, 'completado')");
        $stmt->bind_param("dsii", $total, $metodo_pago, $_SESSION['id_personal'], $id_cliente);
        
        if ($stmt->execute()) {
            $id_pedido = $stmt->insert_id;
            error_log("Pedido insertado con ID: $id_pedido");
            
            // Insertar detalles del pedido
            $stmt_detalle = $conn->prepare("INSERT INTO Detalles_Pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($items as $item) {
                if (isset($item['id_producto'], $item['cantidad'], $item['precio'])) {
                    $id_producto = intval($item['id_producto']);
                    $cantidad = intval($item['cantidad']);
                    $precio = floatval($item['precio']);
                    $subtotal = $precio * $cantidad;
                    
                    $stmt_detalle->bind_param("iiidd", $id_pedido, $id_producto, $cantidad, $precio, $subtotal);
                    
                    if ($stmt_detalle->execute()) {
                        error_log("Detalle insertado: Producto $id_producto, Cantidad $cantidad");
                        
                        // Actualizar stock
                        $update_stmt = $conn->prepare("UPDATE Productos SET stock = stock - ? WHERE id_producto = ?");
                        $update_stmt->bind_param("ii", $cantidad, $id_producto);
                        $update_stmt->execute();
                        $update_stmt->close();
                    } else {
                        error_log("Error en detalle: " . $stmt_detalle->error);
                    }
                }
            }
            
            $stmt_detalle->close();
            
            // Mostrar mensaje de éxito
            $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <strong>¡Pedido realizado con éxito!</strong><br>
                Pedido #' . $id_pedido . ' - Total: $' . number_format($total, 2) . '<br>
                <small>Será redirigido en unos segundos...</small>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            
            // Redirigir después de 3 segundos
            echo '<script>
                setTimeout(function() {
                    window.location.href = "mis_pedidos.php";
                }, 3000);
            </script>';
            
        } else {
            $mensaje = '<div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Error al guardar el pedido: ' . $conn->error . '
            </div>';
            error_log("Error en pedido: " . $conn->error);
        }
        $stmt->close();
    } else {
        $mensaje = '<div class="alert alert-warning">
            <i class="fas fa-exclamation-circle"></i> El carrito está vacío. Agrega productos antes de realizar un pedido.
        </div>';
    }
}

// Obtener productos con categorías
$productos = $conn->query("SELECT p.*, c.nombre_categoria 
                          FROM Productos p 
                          LEFT JOIN Categorias c ON p.id_categoria = c.id_categoria 
                          WHERE stock > 0 ORDER BY p.nombre");

// Obtener clientes
$clientes = $conn->query("SELECT * FROM Clientes ORDER BY nombre");

// Obtener categorías para el filtro
$categorias = $conn->query("SELECT * FROM Categorias WHERE activo = TRUE ORDER BY nombre_categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - Restaurante 8 MIL</title>
    <link rel="stylesheet" href="../css/estilo_insertar.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<div class="admin-container">
    <header class="admin-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1 class="welcome-title">Punto de <span class="admin-name">Venta</span></h1>
                <p class="welcome-subtitle">Sistema de ventas - Restaurante 8 MIL</p>
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
                <a href="user_panel.php" class="nav-item"><i class="fas fa-home"></i> Inicio</a>
                <a href="pos.php" class="nav-item active"><i class="fas fa-cash-register"></i> Punto de Venta</a>
                <a href="mis_pedidos.php" class="nav-item"><i class="fas fa-receipt"></i> Mis Pedidos</a>
                <a href="../php/logout.php" class="nav-item logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <main class="admin-main">
        <div class="crud-container">
            <?php echo $mensaje; ?>

            <div class="row">
                <!-- Columna de Productos -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos Disponibles</h5>
                            <!-- Filtro por categoría -->
                            <div class="filter-section">
                                <select id="filtro-categoria" class="form-control form-control-sm" style="width: 220px;">
                                    <option value="all">Todas las categorías</option>
                                    <?php while($categoria = $categorias->fetch_assoc()): ?>
                                        <option value="<?php echo $categoria['id_categoria']; ?>">
                                            <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row" id="productos-container">
                                <?php while($producto = $productos->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-3 product-item" 
                                         data-categoria="<?php echo $producto['id_categoria']; ?>">
                                        <div class="card product-card" 
                                             data-id="<?php echo $producto['id_producto']; ?>"
                                             data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                             data-precio="<?php echo $producto['precio']; ?>"
                                             data-stock="<?php echo $producto['stock']; ?>">
                                            <div class="card-body text-center position-relative">
                                                <span class="badge categoria-badge">
                                                    <?php echo $producto['nombre_categoria']; ?>
                                                </span>
                                                <h6 class="card-title mt-2"><?php echo $producto['nombre']; ?></h6>
                                                <p class="card-text text-success">
                                                    <strong>$<?php echo number_format($producto['precio'], 2); ?></strong>
                                                </p>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-box"></i> Stock: <?php echo $producto['stock']; ?>
                                                </small>
                                                <button class="btn btn-sm btn-primary mt-2 add-to-cart">
                                                    <i class="fas fa-cart-plus"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna del Carrito -->
                <div class="col-md-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Carrito de Compra</h5>
                        </div>
                        <div class="card-body">
                            <form id="pedidoForm" method="POST" action="">
                                <div class="form-group">
                                    <label for="id_cliente"><i class="fas fa-user"></i> Cliente (Opcional)</label>
                                    <select id="id_cliente" name="id_cliente" class="form-control">
                                        <option value="">Cliente no registrado</option>
                                        <?php 
                                        // Reiniciar el puntero del resultado
                                        $clientes->data_seek(0);
                                        while($cliente = $clientes->fetch_assoc()): ?>
                                            <option value="<?php echo $cliente['id_cliente']; ?>">
                                                <?php echo htmlspecialchars($cliente['nombre']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="metodo_pago"><i class="fas fa-money-bill-wave"></i> Método de Pago</label>
                                    <select id="metodo_pago" name="metodo_pago" class="form-control" required>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="sinpe">Sinpe Móvil</option>
                                    </select>
                                </div>

                                <!-- Sección para entrada de efectivo -->
                                <div id="efectivo-section" class="p-3 bg-light rounded mb-3 border" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label for="efectivo_recibido"><i class="fas fa-cash-register"></i> Efectivo Recibido</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control" id="efectivo_recibido" 
                                                   step="0.01" min="0" placeholder="Ingrese cantidad recibida" required>
                                        </div>
                                        <small class="form-text text-muted">Ingrese la cantidad recibida del cliente</small>
                                    </div>
                                    
                                    <div class="resultado-cambio">
                                        <div class="cambio-info alert alert-success mt-2" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-exchange-alt"></i> <strong>Cambio a devolver:</strong></span>
                                                <span id="cambio-monto" class="font-weight-bold h5 mb-0">$0.00</span>
                                            </div>
                                        </div>
                                        
                                        <div class="faltante-info alert alert-danger mt-2" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-exclamation-triangle"></i> <strong>Faltante:</strong></span>
                                                <span id="faltante-monto" class="font-weight-bold h5 mb-0">$0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="carrito-items" class="mb-3">
                                    <!-- Los items del carrito se agregarán aquí -->
                                    <div class="text-center text-muted py-4" id="carrito-vacio">
                                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                        <p>El carrito está vacío<br><small>Agrega productos de la izquierda</small></p>
                                    </div>
                                </div>

                                <div class="total-section bg-light p-3 rounded mb-3 border border-success">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Total:</h5>
                                        <h3 class="mb-0 text-success" id="total-pedido">$0.00</h3>
                                    </div>
                                </div>

                                <!-- Campo oculto para el cambio -->
                                <input type="hidden" name="cambio" id="cambio_input" value="0">
                                <!-- Campo para indicar que se está realizando el pedido -->
                                <input type="hidden" name="realizar_pedido" value="1">

                                <button type="button" id="btn-realizar-pedido" class="btn btn-success btn-lg btn-block" disabled>
                                    <i class="fas fa-check-circle"></i> Realizar Pedido
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para confirmar pago en efectivo -->
<div class="modal fade" id="confirmarPagoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Confirmar Pago en Efectivo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="text-muted">Total a Pagar</h6>
                                    <h2 class="text-primary" id="modal-total">$0.00</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="text-muted">Efectivo Recibido</h6>
                                    <h2 class="text-success" id="modal-recibido">$0.00</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="cambio-container" style="display: none;">
                        <div class="cambio-card card border-warning">
                            <div class="card-body">
                                <h4><i class="fas fa-exchange-alt text-warning"></i> Cambio a Devolver</h4>
                                <h1 id="modal-cambio" class="text-warning mb-0">$0.00</h1>
                            </div>
                        </div>
                    </div>
                    
                    <div id="faltante-container" style="display: none;">
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="faltante-text">El efectivo recibido es insuficiente</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="confirmar-pago-btn">
                    <i class="fas fa-check"></i> Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    let carrito = [];
    let total = 0;

    // Filtro por categoría
    $('#filtro-categoria').change(function(){
        let categoriaId = $(this).val();
        
        if(categoriaId === 'all') {
            $('.product-item').fadeIn(300);
        } else {
            $('.product-item').hide();
            $(`.product-item[data-categoria="${categoriaId}"]`).fadeIn(300);
        }
    });

    // Mostrar/ocultar sección de efectivo
    $('#metodo_pago').change(function(){
        let metodo = $(this).val();
        
        if(metodo === 'efectivo') {
            $('#efectivo-section').slideDown();
            $('#efectivo_recibido').val('').focus();
        } else {
            $('#efectivo-section').slideUp();
            $('.cambio-info').hide();
            $('.faltante-info').hide();
        }
    });

    // Calcular cambio en tiempo real
    $('#efectivo_recibido').on('input', function(){
        calcularCambio();
    });

    function calcularCambio() {
        let recibido = parseFloat($('#efectivo_recibido').val()) || 0;
        let cambio = recibido - total;
        
        // Ocultar ambos primero
        $('.cambio-info').hide();
        $('.faltante-info').hide();
        
        if(recibido > 0) {
            if (cambio >= 0) {
                // Hay cambio para devolver
                $('.cambio-info').show();
                $('#cambio-monto').text('$' + cambio.toFixed(2));
            } else {
                // Falta dinero
                $('.faltante-info').show();
                $('#faltante-monto').text('$' + Math.abs(cambio).toFixed(2));
            }
        }
    }

    // Agregar producto al carrito
    $('.add-to-cart').click(function(){
        const card = $(this).closest('.product-card');
        const id = parseInt(card.data('id'));
        const nombre = card.data('nombre');
        const precio = parseFloat(card.data('precio'));
        const stock = parseInt(card.data('stock'));

        // Buscar si ya existe en el carrito
        let itemIndex = -1;
        for(let i = 0; i < carrito.length; i++) {
            if(carrito[i].id_producto === id) {
                itemIndex = i;
                break;
            }
        }
        
        if (itemIndex >= 0) {
            // Si ya existe, incrementar cantidad
            if (carrito[itemIndex].cantidad < stock) {
                carrito[itemIndex].cantidad++;
            } else {
                alert('⚠️ No hay suficiente stock disponible para este producto');
                return;
            }
        } else {
            // Si no existe, agregar nuevo item
            if (stock > 0) {
                carrito.push({
                    id_producto: id,
                    nombre: nombre,
                    precio: precio,
                    cantidad: 1
                });
            } else {
                alert('❌ Este producto no tiene stock disponible');
                return;
            }
        }

        actualizarCarrito();
        
        // Efecto visual
        $(this).html('<i class="fas fa-check"></i> Agregado');
        $(this).removeClass('btn-primary').addClass('btn-success');
        
        setTimeout(() => {
            $(this).html('<i class="fas fa-cart-plus"></i> Agregar');
            $(this).removeClass('btn-success').addClass('btn-primary');
        }, 1000);
    });

    // Actualizar carrito
    function actualizarCarrito() {
        $('#carrito-items').empty();
        total = 0;

        if (carrito.length === 0) {
            $('#carrito-items').html(`
                <div class="text-center text-muted py-4" id="carrito-vacio">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>El carrito está vacío<br><small>Agrega productos de la izquierda</small></p>
                </div>
            `);
            $('#btn-realizar-pedido').prop('disabled', true);
        } else {
            // Mostrar items del carrito
            carrito.forEach((item, index) => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;

                $('#carrito-items').append(`
                    <div class="cart-item border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <strong class="d-block">${item.nombre}</strong>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary" onclick="modificarCantidad(${index}, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-dark" disabled>
                                            ${item.cantidad}
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="modificarCantidad(${index}, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <span class="text-muted ml-2">$${item.precio.toFixed(2)} c/u</span>
                                </div>
                            </div>
                            <div class="text-right ml-3">
                                <h6 class="mb-1 text-success">$${subtotal.toFixed(2)}</h6>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="items[${index}][id_producto]" value="${item.id_producto}">
                        <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
                        <input type="hidden" name="items[${index}][precio]" value="${item.precio}">
                    </div>
                `);
            });
            
            $('#btn-realizar-pedido').prop('disabled', false);
        }

        // Actualizar total
        $('#total-pedido').text('$' + total.toFixed(2));
        $('#modal-total').text('$' + total.toFixed(2));

        // Recalcular cambio si hay efectivo
        if ($('#metodo_pago').val() === 'efectivo') {
            calcularCambio();
        }
    }

    // Modificar cantidad de un item
    window.modificarCantidad = function(index, cambio) {
        const productoOriginal = $(`.product-card[data-id="${carrito[index].id_producto}"]`);
        const stock = parseInt(productoOriginal.data('stock'));
        
        if (cambio === 1 && carrito[index].cantidad >= stock) {
            alert('⚠️ No puedes agregar más unidades de este producto');
            return;
        }
        
        if (cambio === -1 && carrito[index].cantidad <= 1) {
            if (confirm('¿Eliminar este producto del carrito?')) {
                eliminarDelCarrito(index);
            }
            return;
        }
        
        carrito[index].cantidad += cambio;
        actualizarCarrito();
    };

    // Eliminar del carrito
    window.eliminarDelCarrito = function(index) {
        if (confirm('¿Estás seguro de eliminar este producto del carrito?')) {
            carrito.splice(index, 1);
            actualizarCarrito();
            alert('✅ Producto eliminado del carrito');
        }
    };

    // Manejar clic en el botón de realizar pedido
    $('#btn-realizar-pedido').click(function(){
        if (carrito.length === 0) {
            alert('⚠️ Agrega al menos un producto al carrito');
            return false;
        }
        
        let metodoPago = $('#metodo_pago').val();
        
        if (metodoPago === 'efectivo') {
            let efectivoRecibido = parseFloat($('#efectivo_recibido').val()) || 0;
            
            if (efectivoRecibido <= 0) {
                alert('⚠️ Ingrese la cantidad de efectivo recibido');
                $('#efectivo_recibido').focus();
                return false;
            }
            
            let cambio = efectivoRecibido - total;
            
            // Mostrar modal de confirmación
            mostrarModalPago(efectivoRecibido, cambio);
        } else {
            // Para otros métodos de pago, confirmar directamente
            if (confirm(`¿Confirmar pedido por $${total.toFixed(2)}?\nMétodo de pago: ${metodoPago}`)) {
                // Establecer cambio como 0
                $('#cambio_input').val('0');
                
                // Mostrar mensaje de procesamiento
                $('.crud-container').prepend(`
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-spinner fa-spin"></i> Procesando pedido, por favor espera...
                    </div>
                `);
                
                // Enviar formulario
                document.getElementById('pedidoForm').submit();
            }
        }
    });

    function mostrarModalPago(recibido, cambio) {
        $('#modal-recibido').text('$' + recibido.toFixed(2));
        
        // Mostrar/ocultar secciones según el cambio
        if (cambio >= 0) {
            $('#cambio-container').show();
            $('#faltante-container').hide();
            $('#modal-cambio').text('$' + cambio.toFixed(2));
            $('#confirmar-pago-btn').prop('disabled', false);
        } else {
            $('#cambio-container').hide();
            $('#faltante-container').show();
            $('#faltante-text').text('El efectivo recibido es insuficiente. Faltan $' + Math.abs(cambio).toFixed(2));
            $('#confirmar-pago-btn').prop('disabled', true);
        }
        
        $('#confirmarPagoModal').modal('show');
    }

    // Confirmar pago desde modal
    $('#confirmar-pago-btn').click(function(){
        let efectivoRecibido = parseFloat($('#efectivo_recibido').val());
        let cambio = efectivoRecibido - total;
        
        if (cambio >= 0) {
            $('#cambio_input').val(cambio.toFixed(2));
            $('#confirmarPagoModal').modal('hide');
            
            // Mostrar mensaje de procesamiento
            $('.crud-container').prepend(`
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-spinner fa-spin"></i> Procesando pedido, por favor espera...
                </div>
            `);
            
            // Enviar formulario
            document.getElementById('pedidoForm').submit();
        }
    });

    // Inicializar carrito
    actualizarCarrito();
});
</script>

<style>
.product-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    height: 100%;
    border-radius: 10px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border-color: var(--primary);
}

.categoria-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(135deg, var(--stone-600) 0%, var(--stone-700) 100%);
    color: white;
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.cart-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 12px 15px;
    border-radius: 8px;
    border-left: 4px solid var(--secondary);
    margin-bottom: 10px;
    transition: all 0.3s ease;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.cart-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.total-section {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border: 2px solid #28a745;
    border-radius: 10px;
}

#efectivo-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    border: 2px solid #cce5ff;
    border-radius: 10px;
    animation: fadeIn 0.5s ease;
}

.cambio-info {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid #155724;
    border-radius: 8px;
    animation: pulse 2s infinite;
}

.faltante-info {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border: 2px solid #721c24;
    border-radius: 8px;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Modal styles */
.cambio-card {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 3px solid #ffc107;
}

/* Scrollbar personalizado */
#productos-container {
    max-height: 600px;
    overflow-y: auto;
    padding-right: 10px;
}

#productos-container::-webkit-scrollbar {
    width: 8px;
}

#productos-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#productos-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 10px;
}

#productos-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
}

/* Responsive */
@media (max-width: 768px) {
    .product-card {
        margin-bottom: 15px;
    }
    
    #filtro-categoria {
        width: 100% !important;
        margin-top: 10px;
    }
    
    .cart-item {
        padding: 10px;
    }
}

/* Botones mejorados */
.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(44, 85, 48, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

/* Estilos para el input de efectivo */
#efectivo_recibido {
    font-size: 1.1rem;
    font-weight: 500;
}

.input-group-text {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    font-weight: bold;
    border: none;
}

/* Animación para el spinner de carga */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

/* Solo estilos necesarios específicos para POS */
#productos-container {
    max-height: 600px;
    overflow-y: auto;
    padding-right: 10px;
}

#productos-container::-webkit-scrollbar {
    width: 8px;
}

#productos-container::-webkit-scrollbar-track {
    background: var(--stone-100);
    border-radius: 10px;
}

#productos-container::-webkit-scrollbar-thumb {
    background: var(--primary-gradient);
    border-radius: 10px;
}

#productos-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
}

/* Clases específicas para badges de categoría */
.categoria-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--primary-gradient);
    color: white;
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-weight: 600;
    letter-spacing: 0.5px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Estilos para botones de carrito */
.btn-outline-secondary {
    border-color: var(--stone-300);
    color: var(--stone-600);
}

.btn-outline-secondary:hover {
    background-color: var(--stone-200);
    border-color: var(--stone-400);
}

/* Estilos para modal de confirmación */
.cambio-card {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 3px solid var(--warning);
    border-radius: 10px;
}

/* Animaciones */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Responsive */
@media (max-width: 768px) {
    .product-card {
        margin-bottom: 15px;
    }
    
    #filtro-categoria {
        width: 100% !important;
        margin-top: 10px;
    }
    
    .cart-item {
        padding: 10px;
    }
}
</style>

<?php $conn->close(); ?>

