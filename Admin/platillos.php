<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';
require_once '../includes/funciones.php';

$mensaje = '';

// AGREGAR PLATILLO
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria_id = intval($_POST['categoria_id']);
    $imagen = 'default.jpg';
    
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $resultado = subirImagen($_FILES['imagen'], '../subir/');
        if($resultado) $imagen = $resultado;
    }
    
    $stmt = $pdo->prepare("INSERT INTO platillos (nombre, descripcion, precio, imagen, categoria_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $imagen, $categoria_id]);
    $mensaje = '✅ Platillo agregado correctamente';
}

// EDITAR PLATILLO
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria_id = intval($_POST['categoria_id']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $resultado = subirImagen($_FILES['imagen'], '../subir/');
        if($resultado) {
            $stmt = $pdo->prepare("UPDATE platillos SET nombre=?, descripcion=?, precio=?, imagen=?, categoria_id=?, disponible=? WHERE id=?");
            $stmt->execute([$nombre, $descripcion, $precio, $resultado, $categoria_id, $disponible, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE platillos SET nombre=?, descripcion=?, precio=?, categoria_id=?, disponible=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $disponible, $id]);
    }
    $mensaje = '✅ Platillo actualizado correctamente';
}

// ELIMINAR
if(isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM platillos WHERE id = ?");
    $stmt->execute([intval($_GET['eliminar'])]);
    $mensaje = '✅ Platillo eliminado';
}

// PAGINACIÓN
$por_pagina = 5;
$total_platillos = $pdo->query("SELECT COUNT(*) FROM platillos")->fetchColumn();
$total_paginas = ceil($total_platillos / $por_pagina);
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Obtener platillos con límite
$stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria FROM platillos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY c.nombre, p.nombre LIMIT ? OFFSET ?");
$stmt->execute([$por_pagina, $offset]);
$platillos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY orden")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platillos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-danger shadow sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-speedometer2"></i> Panel de Administración
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>
                </span>
                <a href="cerrar-sesion.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <nav class="col-md-2 bg-dark min-vh-100 py-4" style="background: linear-gradient(180deg, #212529 0%, #343a40 100%);">
                <div class="px-3">
                    <h6 class="text-muted text-uppercase small fw-bold px-2 mb-3">Menú Principal</h6>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a href="index.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a></li>
                        <li class="nav-item"><a href="platillos.php" class="nav-link rounded-3 active bg-danger text-white"><i class="bi bi-egg-fried me-2"></i> Platillos</a></li>
                        <li class="nav-item"><a href="categorias.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-tags me-2"></i> Categorías</a></li>
                        <li class="nav-item"><a href="pedidos.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-box-seam me-2"></i> Pedidos</a></li>
                        <li class="nav-item"><a href="usuarios.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-people me-2"></i> Clientes</a></li>
                        <li class="nav-item"><a href="promociones.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-megaphone me-2"></i> Promociones</a></li>
                    </ul>
                    <hr class="text-secondary my-3">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a href="perfil.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-person-gear me-2"></i> Mi Perfil</a></li>
                        <li class="nav-item"><a href="../index.php" target="_blank" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-eye me-2"></i> Ver tienda</a></li>
                    </ul>
                </div>
            </nav>

            <!-- CONTENIDO -->
            <main class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold"><i class="bi bi-egg-fried text-danger"></i> Platillos</h2>
                        <p class="text-muted">Total: <strong><?php echo $total_platillos; ?></strong> platillos</p>
                    </div>
                    <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                        <i class="bi bi-plus-lg"></i> Agregar Platillo
                    </button>
                </div>

                <?php if($mensaje): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?php echo $mensaje; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="ps-4">Imagen</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($platillos as $p): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <img src="../subir/<?php echo htmlspecialchars($p['imagen']); ?>" 
                                                 width="60" height="60" class="rounded-3" style="object-fit: cover;">
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['categoria']); ?></span></td>
                                        <td class="text-success fw-bold">$<?php echo number_format($p['precio'], 2); ?></td>
                                        <td>
                                            <?php if($p['disponible']): ?>
                                                <span class="badge bg-success">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Agotado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $p['id']; ?>"><i class="bi bi-pencil"></i></button>
                                            <a href="?eliminar=<?php echo $p['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>

                                    <?php if(count($platillos) == 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No hay platillos registrados</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PAGINACIÓN -->
                <?php if($total_paginas > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>

                        <?php 
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($total_paginas, $pagina_actual + 2);
                        
                        if($inicio > 1): ?>
                            <li class="page-item"><a class="page-link" href="?pagina=1">1</a></li>
                            <?php if($inicio > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for($i = $inicio; $i <= $fin; $i++): ?>
                            <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if($fin < $total_paginas): ?>
                            <?php if($fin < $total_paginas - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?pagina=<?php echo $total_paginas; ?>"><?php echo $total_paginas; ?></a></li>
                        <?php endif; ?>

                        <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                    <p class="text-center text-muted small">
                        Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?> 
                        (Mostrando <?php echo count($platillos); ?> de <?php echo $total_platillos; ?> platillos)
                    </p>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- MODAL AGREGAR -->
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Agregar Platillo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" name="precio" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="agregar" class="btn btn-danger">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODALES EDITAR -->
    <?php foreach($platillos as $p): ?>
    <div class="modal fade" id="modalEditar<?php echo $p['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Editar: <?php echo htmlspecialchars($p['nombre']); ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($p['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?php echo htmlspecialchars($p['descripcion']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" name="precio" class="form-control" value="<?php echo $p['precio']; ?>" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria_id" class="form-select" required>
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $p['categoria_id'] ? 'selected' : ''; ?>><?php echo $cat['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Imagen (dejar vacío para mantener)</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="disponible" class="form-check-input" <?php echo $p['disponible'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Disponible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="editar" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <style>
    .hover-effect:hover { background-color: rgba(220, 53, 69, 0.2) !important; color: white !important; }
    .nav-link { transition: all 0.2s ease; }
    .page-link { color: #dc3545; }
    .page-item.active .page-link { background-color: #dc3545; border-color: #dc3545; }
    .page-link:hover { color: #dc3545; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>