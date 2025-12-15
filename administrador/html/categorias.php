<?php
include("../../conexion.php");
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$mensaje = "";

// CREATE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crear'])) {
    $nombre_categoria = trim($_POST['nombre_categoria']);
    $descripcion = trim($_POST['descripcion']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!empty($nombre_categoria)) {
        $stmt = $conn->prepare("INSERT INTO categorias (nombre_categoria, descripcion, activo) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nombre_categoria, $descripcion, $activo);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Categoría registrada correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al insertar: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $mensaje = "<div class='alert error'>❌ El nombre de la categoría es requerido</div>";
    }
}

// UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['actualizar'])) {
    $id_categoria = $_POST['id_categoria'];
    $nombre_categoria = trim($_POST['nombre_categoria']);
    $descripcion = trim($_POST['descripcion']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!empty($nombre_categoria)) {
        $stmt = $conn->prepare("UPDATE categorias SET nombre_categoria=?, descripcion=?, activo=? WHERE id_categoria=?");
        $stmt->bind_param("ssii", $nombre_categoria, $descripcion, $activo, $id_categoria);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Categoría actualizada correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al actualizar: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $mensaje = "<div class='alert error'>❌ El nombre de la categoría es requerido</div>";
    }
}

// DELETE
if (isset($_GET['eliminar'])) {
    $id_categoria = $_GET['eliminar'];
    $check = $conn->prepare("SELECT COUNT(*) AS total FROM productos WHERE id_categoria=?");
    $check->bind_param("i", $id_categoria);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if ($res['total'] > 0) {
        $mensaje = "<div class='alert error'>❌ No se puede eliminar la categoría porque tiene productos asociados</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM categorias WHERE id_categoria=?");
        $stmt->bind_param("i", $id_categoria);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Categoría eliminada correctamente</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al eliminar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

$result_categorias = $conn->query("SELECT * FROM categorias ORDER BY id_categoria DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Restaurante</title>
    <!-- Bootstrap 4.6.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                        <h1 class="welcome-title">Gestión de <span class="admin-name">Categorías</span></h1>
                        <p class="welcome-subtitle">Administra las categorías de productos del restaurante</p>
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
                    <a href="categorias.php" class="nav-item active">
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

        <main class="admin-main">
            <div class="crud-container">
                <?php echo $mensaje; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Lista de Categorías</h2>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalCategoria" id="btnNueva">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="modalCategoria" tabindex="-1" role="dialog" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tituloModal">Agregar Nueva Categoría</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form method="POST" id="formCategoria">
                                <div class="modal-body">
                                    <input type="hidden" name="id_categoria" id="id_categoria">

                                    <div class="form-group">
                                        <label for="nombre_categoria">Nombre *</label>
                                        <input type="text" id="nombre_categoria" name="nombre_categoria" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="descripcion">Descripción</label>
                                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                                    </div>

                                    <div class="form-group form-check">
                                        <input type="checkbox" id="activo" name="activo" class="form-check-input" checked value="1">
                                        <label for="activo" class="form-check-label">Activo</label>
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

                <!-- Tabla -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result_categorias->num_rows > 0): ?>
                            <?php while($cat = $result_categorias->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $cat['id_categoria']; ?></td>
                                    <td><?php echo htmlspecialchars($cat['nombre_categoria']); ?></td>
                                    <td><?php echo $cat['descripcion'] ?: '<em class="text-muted">Sin descripción</em>'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $cat['activo'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $cat['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-warning btn-sm btnEditar"
                                                    data-id="<?php echo $cat['id_categoria']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($cat['nombre_categoria']); ?>"
                                                    data-desc="<?php echo htmlspecialchars($cat['descripcion']); ?>"
                                                    data-activo="<?php echo $cat['activo']; ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <a href="?eliminar=<?php echo $cat['id_categoria']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                    No hay categorías registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function(){
        // Nueva categoría
        $("#btnNueva").click(function(){
            $("#tituloModal").text("Agregar Nueva Categoría");
            $("#btnGuardar").removeClass("d-none");
            $("#btnActualizar").addClass("d-none");
            $("#formCategoria")[0].reset();
            $("#id_categoria").val('');
            $("#activo").prop("checked", true);
        });

        // Editar categoría
        $(".btnEditar").click(function(){
            let id = $(this).data("id");
            let nombre = $(this).data("nombre");
            let desc = $(this).data("desc");
            let activo = $(this).data("activo");

            $("#id_categoria").val(id);
            $("#nombre_categoria").val(nombre);
            $("#descripcion").val(desc);
            $("#activo").prop("checked", activo == 1);

            $("#tituloModal").text("Editar Categoría");
            $("#btnGuardar").addClass("d-none");
            $("#btnActualizar").removeClass("d-none");

            $("#modalCategoria").modal("show");
        });

        // Cerrar modal y limpiar URL
        $('#modalCategoria').on('hidden.bs.modal', function () {
            $("#formCategoria")[0].reset();
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>