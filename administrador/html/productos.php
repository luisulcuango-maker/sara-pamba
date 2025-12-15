<?php
include("../../conexion.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$mensaje = "";

// CREATE Producto
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $id_categoria = intval($_POST['id_categoria']);
    $stock = intval($_POST['stock']);
    $descripcion = trim($_POST['descripcion']);

    // Validaciones
    if (empty($nombre)) {
        $mensaje = "<div class='alert error'>❌ El nombre es requerido</div>";
    } elseif ($precio < 0) {
        $mensaje = "<div class='alert error'>❌ El precio no puede ser negativo</div>";
    } elseif ($precio > 999999) {
        $mensaje = "<div class='alert error'>❌ Precio demasiado alto (máx $999,999)</div>";
    } elseif ($stock < 0) {
        $mensaje = "<div class='alert error'>❌ El stock no puede ser negativo</div>";
    } elseif ($id_categoria <= 0) {
        $mensaje = "<div class='alert error'>❌ Debe seleccionar una categoría</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, id_categoria, stock, descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiis", $nombre, $precio, $id_categoria, $stock, $descripcion);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Producto registrado correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al insertar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// UPDATE Producto
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['actualizar'])) {
    $id_producto = intval($_POST['id_producto']);
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $id_categoria = intval($_POST['id_categoria']);
    $stock = intval($_POST['stock']);
    $descripcion = trim($_POST['descripcion']);

    // Validaciones
    if (empty($nombre)) {
        $mensaje = "<div class='alert error'>❌ El nombre es requerido</div>";
    } elseif ($precio < 0) {
        $mensaje = "<div class='alert error'>❌ El precio no puede ser negativo</div>";
    } elseif ($precio > 999999) {
        $mensaje = "<div class='alert error'>❌ Precio demasiado alto (máx $999,999)</div>";
    } elseif ($stock < 0) {
        $mensaje = "<div class='alert error'>❌ El stock no puede ser negativo</div>";
    } elseif ($id_categoria <= 0) {
        $mensaje = "<div class='alert error'>❌ Debe seleccionar una categoría</div>";
    } else {
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, precio=?, id_categoria=?, stock=?, descripcion=? WHERE id_producto=?");
        $stmt->bind_param("sdiisi", $nombre, $precio, $id_categoria, $stock, $descripcion, $id_producto);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Producto actualizado correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al actualizar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// DELETE Producto
if (isset($_GET['eliminar'])) {
    $id_producto = intval($_GET['eliminar']);
    
    // Verificar si el producto tiene pedidos
    $check = $conn->prepare("SELECT COUNT(*) AS total FROM detalles_pedido WHERE id_producto=?");
    $check->bind_param("i", $id_producto);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if ($res['total'] > 0) {
        $mensaje = "<div class='alert error'>❌ No se puede eliminar el producto porque tiene pedidos asociados</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto=?");
        $stmt->bind_param("i", $id_producto);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Producto eliminado correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al eliminar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Obtener datos
$result_productos = $conn->query("
    SELECT p.*, c.nombre_categoria 
    FROM productos p 
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
    ORDER BY p.id_producto DESC
");
$result_categorias = $conn->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre_categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Restaurante</title>
    <!-- Bootstrap 4.6.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS Mejorado -->
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
                        <h1 class="welcome-title">Gestión de <span class="admin-name">Productos</span></h1>
                        <p class="welcome-subtitle">Administra los productos del restaurante</p>
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
                    <a href="productos.php" class="nav-item active">
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

        <main class="admin-main">
            <div class="crud-container">
                <?php echo $mensaje; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Lista de Productos</h2>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalProducto" id="btnNuevo">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="quick-stat-item text-center">
                            <h4>Total Productos</h4>
                            <p><?php echo $result_productos->num_rows; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-stat-item text-center">
                            <h4>Sin Stock</h4>
                            <p>
                                <?php
                                $count_no_stock = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock = 0")->fetch_assoc()['total'];
                                echo $count_no_stock;
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-stat-item text-center">
                            <h4>Categorías Activas</h4>
                            <p>
                                <?php
                                $count_categorias = $conn->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1")->fetch_assoc()['total'];
                                echo $count_categorias;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Producto -->
                <div class="modal fade" id="modalProducto" tabindex="-1" role="dialog" aria-labelledby="modalProductoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tituloModal">Agregar Nuevo Producto</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form method="POST" id="formProducto">
                                <div class="modal-body">
                                    <input type="hidden" name="id_producto" id="id_producto">

                                    <div class="form-group">
                                        <label for="nombre">Nombre *</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="100">
                                    </div>

                                    <div class="form-group">
                                        <label for="precio">Precio ($) *</label>
                                        <input type="number" id="precio" name="precio" class="form-control" step="0.01" min="0" max="999999" required>
                                        <small class="form-text text-muted">Máximo $999,999.99</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="id_categoria">Categoría *</label>
                                        <select id="id_categoria" name="id_categoria" class="form-control" required>
                                            <option value="">Seleccionar categoría</option>
                                            <?php 
                                            $result_categorias->data_seek(0);
                                            while($cat = $result_categorias->fetch_assoc()): ?>
                                                <option value="<?php echo $cat['id_categoria']; ?>">
                                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="stock">Stock</label>
                                        <input type="number" id="stock" name="stock" class="form-control" min="0" value="0" max="9999">
                                        <small class="form-text text-muted">Máximo 9,999 unidades</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="descripcion">Descripción</label>
                                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" maxlength="500"></textarea>
                                        <small class="form-text text-muted">Máximo 500 caracteres</small>
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

                <!-- Tabla Productos -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result_productos->num_rows > 0): ?>
                            <?php while($prod = $result_productos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $prod['id_producto']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong>
                                        <?php if (!empty($prod['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo substr($prod['descripcion'], 0, 50); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>$<?php echo number_format($prod['precio'], 2); ?></strong></td>
                                    <td><?php echo htmlspecialchars($prod['nombre_categoria']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $prod['stock'] > 0 ? 'active' : 'inactive'; ?>">
                                            <?php echo $prod['stock']; ?> unidades
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-info btn-sm btnVerProducto" 
                                                    data-id="<?php echo $prod['id_producto']; ?>">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button class="btn btn-warning btn-sm btnEditar"
                                                    data-id="<?php echo $prod['id_producto']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                                    data-precio="<?php echo $prod['precio']; ?>"
                                                    data-categoria="<?php echo $prod['id_categoria']; ?>"
                                                    data-stock="<?php echo $prod['stock']; ?>"
                                                    data-desc="<?php echo htmlspecialchars($prod['descripcion']); ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <a href="?eliminar=<?php echo $prod['id_producto']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-utensils fa-2x mb-2 d-block"></i>
                                    No hay productos registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Ver Detalles de Producto -->
    <div class="modal fade" id="modalDetallesProducto" tabindex="-1" aria-labelledby="modalDetallesProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesProductoLabel">Detalles del Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detallesProductoBody">
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
        // Nuevo producto
        $("#btnNuevo").click(function(){
            $("#tituloModal").text("Agregar Nuevo Producto");
            $("#btnGuardar").removeClass("d-none");
            $("#btnActualizar").addClass("d-none");
            $("#formProducto")[0].reset();
            $("#id_producto").val('');
        });

        // Editar producto
        $(".btnEditar").click(function(){
            let id = $(this).data("id");
            let nombre = $(this).data("nombre");
            let precio = $(this).data("precio");
            let categoria = $(this).data("categoria");
            let stock = $(this).data("stock");
            let desc = $(this).data("desc");

            $("#id_producto").val(id);
            $("#nombre").val(nombre);
            $("#precio").val(precio);
            $("#id_categoria").val(categoria);
            $("#stock").val(stock);
            $("#descripcion").val(desc);

            $("#tituloModal").text("Editar Producto");
            $("#btnGuardar").addClass("d-none");
            $("#btnActualizar").removeClass("d-none");

            $("#modalProducto").modal("show");
        });

        // Ver detalles del producto
        $(".btnVerProducto").click(function(){
            const productId = $(this).data('id');
            
            $('#detallesProductoBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles del producto...</p>
                </div>
            `);
            
            $('#modalDetallesProducto').modal('show');
            
            $.ajax({
                url: 'obtener_detalles_producto.php',
                type: 'POST',
                data: { id_producto: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const producto = response.data;
                        let html = `
                            <div class="detalle-grid">
                                <div class="detalle-item">
                                    <div class="detalle-label">ID</div>
                                    <div class="detalle-valor">#${producto.id_producto}</div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Nombre</div>
                                    <div class="detalle-valor">${producto.nombre}</div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Precio</div>
                                    <div class="detalle-valor">$${parseFloat(producto.precio).toFixed(2)}</div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Categoría</div>
                                    <div class="detalle-valor">${producto.nombre_categoria}</div>
                                </div>
                            </div>
                            
                            <div class="detalle-grid">
                                <div class="detalle-item">
                                    <div class="detalle-label">Stock</div>
                                    <div class="detalle-valor">
                                        <span class="badge-modal ${producto.stock > 0 ? 'badge-success' : 'badge-danger'}">
                                            ${producto.stock} unidades
                                        </span>
                                    </div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Estado</div>
                                    <div class="detalle-valor">
                                        <span class="badge-modal ${producto.stock > 0 ? 'badge-success' : 'badge-warning'}">
                                            ${producto.stock > 0 ? 'Disponible' : 'Sin Stock'}
                                        </span>
                                    </div>
                                </div>
                                <div class="detalle-item">
                                    <div class="detalle-label">Fecha Registro</div>
                                    <div class="detalle-valor">${new Date(producto.fecha_registro).toLocaleDateString()}</div>
                                </div>
                            </div>
                        `;
                        
                        if (producto.descripcion) {
                            html += `
                                <div class="detalle-item">
                                    <div class="detalle-label">Descripción</div>
                                    <div class="detalle-valor">${producto.descripcion}</div>
                                </div>
                            `;
                        }
                        
                        $('#detallesProductoBody').html(html);
                    } else {
                        $('#detallesProductoBody').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del producto
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#detallesProductoBody').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del producto
                        </div>
                    `);
                }
            });
        });

        $('#modalProducto').on('hidden.bs.modal', function () {
            $("#formProducto")[0].reset();
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>