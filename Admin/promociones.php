<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$mensaje = '';

// Guardar promoción
if(isset($_POST['guardar'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $activa = isset($_POST['activa']) ? 1 : 0;
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    
    $stmt = $pdo->prepare("INSERT INTO promociones (titulo, descripcion, activa, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descripcion, $activa, $fecha_inicio, $fecha_fin]);
    $mensaje = '✅ Promoción creada correctamente';
}

// Eliminar
if(isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM promociones WHERE id = ?");
    $stmt->execute([intval($_GET['eliminar'])]);
    $mensaje = '✅ Promoción eliminada';
}

// Toggle activar/desactivar
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $pdo->prepare("UPDATE promociones SET activa = NOT activa WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: promociones.php');
    exit;
}

$promociones = $pdo->query("SELECT * FROM promociones ORDER BY fecha_inicio DESC")->fetchAll();
$activas = count(array_filter($promociones, fn($p) => $p['activa']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promociones - Admin</title>
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
                        <li class="nav-item"><a href="platillos.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-egg-fried me-2"></i> Platillos</a></li>
                        <li class="nav-item"><a href="categorias.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-tags me-2"></i> Categorías</a></li>
                        <li class="nav-item"><a href="pedidos.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-box-seam me-2"></i> Pedidos</a></li>
                        <li class="nav-item"><a href="usuarios.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-people me-2"></i> Clientes</a></li>
                        <li class="nav-item"><a href="promociones.php" class="nav-link rounded-3 active bg-danger text-white"><i class="bi bi-megaphone me-2"></i> Promociones</a></li>
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
                        <h2 class="fw-bold"><i class="bi bi-megaphone text-danger"></i> Promociones</h2>
                        <p class="text-muted">Gestiona ofertas y descuentos</p>
                    </div>
                </div>

                <?php if($mensaje): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?php echo $mensaje; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <!-- Cards resumen -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h4 class="fw-bold mb-0"><?php echo $activas; ?></h4><small class="text-white-50">Promociones activas</small></div>
                                <i class="bi bi-megaphone fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-info bg-gradient text-white">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h4 class="fw-bold mb-0"><?php echo count($promociones); ?></h4><small class="text-white-50">Total promociones</small></div>
                                <i class="bi bi-tags fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-warning bg-gradient text-white">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h4 class="fw-bold mb-0">🎉</h4><small class="text-white-50">¡Atrae más clientes!</small></div>
                                <i class="bi bi-star fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Formulario -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Promoción</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Título</label>
                                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: 3x2 en alitas">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Descripción</label>
                                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Válido solo viernes de 5pm a 10pm"></textarea>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-bold">Fecha inicio</label>
                                            <input type="date" name="fecha_inicio" class="form-control" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-bold">Fecha fin</label>
                                            <input type="date" name="fecha_fin" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="activa" class="form-check-input" id="activaCheck" checked>
                                        <label class="form-check-label" for="activaCheck">Activar promoción ahora</label>
                                    </div>
                                    <button type="submit" name="guardar" class="btn btn-danger w-100 fw-bold">
                                        <i class="bi bi-check-lg"></i> Guardar promoción
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de promociones -->
                    <div class="col-lg-8">
                        <?php if(count($promociones) == 0): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-megaphone text-muted" style="font-size: 4rem;"></i>
                                <h4 class="text-muted mt-3">No hay promociones</h4>
                                <p class="text-muted">Crea tu primera promoción para atraer clientes</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($promociones as $promo): ?>
                            <div class="card shadow-sm border-0 mb-3 <?php echo $promo['activa'] ? 'border-success border-2' : ''; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <h5 class="mb-1">
                                                <?php echo htmlspecialchars($promo['titulo']); ?>
                                                <?php if($promo['activa']): ?>
                                                    <span class="badge bg-success ms-2"><i class="bi bi-circle-fill small me-1"></i>Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2">Inactiva</span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($promo['descripcion']); ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> 
                                                <?php echo date('d/m/Y', strtotime($promo['fecha_inicio'])); ?> - 
                                                <?php echo date('d/m/Y', strtotime($promo['fecha_fin'])); ?>
                                            </small>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="?toggle=<?php echo $promo['id']; ?>" 
                                               class="btn btn-sm <?php echo $promo['activa'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>"
                                               title="<?php echo $promo['activa'] ? 'Desactivar' : 'Activar'; ?>">
                                                <?php if($promo['activa']): ?>
                                                    <i class="bi bi-pause"></i> Desactivar
                                                <?php else: ?>
                                                    <i class="bi bi-play"></i> Activar
                                                <?php endif; ?>
                                            </a>
                                            <a href="?eliminar=<?php echo $promo['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('¿Eliminar esta promoción?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <style>
    .hover-effect:hover { background-color: rgba(220, 53, 69, 0.2) !important; color: white !important; }
    .nav-link { transition: all 0.2s ease; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>