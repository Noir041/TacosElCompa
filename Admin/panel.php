<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// Estadísticas rápidas
$total_platillos = $pdo->query("SELECT COUNT(*) FROM platillos")->fetchColumn();
$total_pedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn();
$pedidos_pendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
$total_ganancias = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado != 'cancelado'")->fetchColumn();

// Promedio de calificación
$promedio = $pdo->query("SELECT ROUND(AVG(puntuacion), 1) FROM calificaciones")->fetchColumn();
$promedio = $promedio ?: 0;

// Total de calificaciones
$total_calificaciones = $pdo->query("SELECT COUNT(*) FROM calificaciones")->fetchColumn();

// Pedidos recientes
$pedidos = $pdo->query("
    SELECT p.*, u.nombre as cliente, u.foto_perfil 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha_pedido DESC 
    LIMIT 10
")->fetchAll();

// Últimas calificaciones
$ultimasCal = $pdo->query("
    SELECT c.*, u.nombre as cliente, u.foto_perfil, p.nombre as platillo 
    FROM calificaciones c 
    JOIN usuarios u ON c.usuario_id = u.id 
    JOIN platillos p ON c.platillo_id = p.id 
    ORDER BY c.fecha_calificacion DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- NAVBAR SUPERIOR (IGUAL QUE LA TIENDA) -->
    <nav class="navbar navbar-dark bg-danger shadow sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-speedometer2"></i> Panel de Administración
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white">
                    <i class="bi bi-person-circle"></i> 
                    <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>
                    <span class="badge bg-light text-danger ms-1"><?php echo $_SESSION['admin_rol']; ?></span>
                </span>
                <a href="cerrar-sesion.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR UNIFICADO -->
            <nav class="col-md-2 bg-dark min-vh-100 py-4" style="background: linear-gradient(180deg, #212529 0%, #343a40 100%);">
                <div class="px-3">
                    <h6 class="text-muted text-uppercase small fw-bold px-2 mb-3">Menú Principal</h6>
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link rounded-3 active bg-danger text-white">
                                <i class="bi bi-grid-1x2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="platillos.php" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-egg-fried me-2"></i> Platillos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="categorias.php" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-tags me-2"></i> Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pedidos.php" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-box-seam me-2"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="usuarios.php" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-people me-2"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="promociones.php" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-megaphone me-2"></i> Promociones
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-secondary my-3">
                    
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a href="perfil.php" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-person-gear me-2"></i> Mi Perfil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php" target="_blank" class="nav-link rounded-3 text-white-50 hover-effect">
                                <i class="bi bi-eye me-2"></i> Ver tienda
                                <i class="bi bi-box-arrow-up-right small ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-0"><i class="bi bi-grid-1x2 text-danger"></i> Dashboard</h2>
                        <p class="text-muted mb-0">Resumen general de tu negocio</p>
                    </div>
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-circle-fill small me-1"></i> En línea
                    </span>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm bg-danger bg-gradient text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-egg-fried fs-1 opacity-50"></i>
                                <h3 class="fw-bold mb-0 mt-2"><?php echo $total_platillos; ?></h3>
                                <small class="text-white-50">Platillos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm bg-warning bg-gradient text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-clock fs-1 opacity-50"></i>
                                <h3 class="fw-bold mb-0 mt-2"><?php echo $pedidos_pendientes; ?></h3>
                                <small class="text-white-50">Pendientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm bg-success bg-gradient text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle fs-1 opacity-50"></i>
                                <h3 class="fw-bold mb-0 mt-2"><?php echo $total_pedidos; ?></h3>
                                <small class="text-white-50">Total pedidos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm bg-info bg-gradient text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-people fs-1 opacity-50"></i>
                                <h3 class="fw-bold mb-0 mt-2"><?php echo $total_clientes; ?></h3>
                                <small class="text-white-50">Clientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm bg-primary bg-gradient text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                                <h3 class="fw-bold mb-0 mt-2">$<?php echo number_format($total_ganancias, 0); ?></h3>
                                <small class="text-white-50">Ganancias</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm bg-warning bg-gradient text-dark h-100">
                            <div class="card-body text-center">
                                <div class="text-warning mb-1">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= round($promedio) ? '-fill' : ''; ?> small"></i>
                                    <?php endfor; ?>
                                </div>
                                <h3 class="fw-bold mb-0 mt-1"><?php echo $promedio; ?></h3>
                                <small><?php echo $total_calificaciones; ?> calificaciones</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos pedidos -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ul text-danger"></i> Últimos Pedidos</h5>
                        <a href="pedidos.php" class="btn btn-outline-danger btn-sm">Ver todos</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th class="text-end pe-4">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pedidos as $ped): 
                                        $badgeColor = match($ped['estado']) {
                                            'pendiente' => 'warning',
                                            'en_cocina' => 'info',
                                            'listo' => 'success',
                                            'entregado' => 'secondary',
                                            'cancelado' => 'danger',
                                            default => 'light'
                                        };
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $ped['id']; ?></td>
                                        <td>
                                            <img src="../subir/<?php echo $ped['foto_perfil'] ?? 'default-user.png'; ?>" 
                                                 class="rounded-circle me-2" width="30" height="30" 
                                                 style="object-fit: cover;">
                                            <?php echo htmlspecialchars($ped['cliente']); ?>
                                        </td>
                                        <td class="fw-bold text-success">$<?php echo number_format($ped['total'], 2); ?></td>
                                        <td><span class="badge bg-<?php echo $badgeColor; ?>"><?php echo $ped['estado']; ?></span></td>
                                        <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></td>
                                        <td class="text-end pe-4">
                                            <a href="pedidos.php" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Últimas calificaciones -->
                <?php if(count($ultimasCal) > 0): ?>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-star-fill text-warning"></i> Últimas Reseñas</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach($ultimasCal as $cal): ?>
                        <div class="d-flex align-items-center p-3 border-bottom">
                            <img src="../subir/<?php echo $cal['foto_perfil'] ?? 'default-user.png'; ?>" 
                                 class="rounded-circle me-3" width="45" height="45" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <strong><?php echo htmlspecialchars($cal['cliente']); ?></strong>
                                <small class="text-muted">calificó</small>
                                <strong><?php echo htmlspecialchars($cal['platillo']); ?></strong>
                                <div class="text-warning">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $cal['puntuacion'] ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <small class="text-muted ms-1">hace <?php 
                                        $dias = floor((time() - strtotime($cal['fecha_calificacion'])) / 86400);
                                        echo $dias == 0 ? 'hoy' : $dias . ' día(s)';
                                    ?></small>
                                </div>
                                <?php if($cal['comentario']): ?>
                                    <small class="text-muted fst-italic">"<?php echo htmlspecialchars($cal['comentario']); ?>"</small>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-warning text-dark fs-6"><?php echo $cal['puntuacion']; ?>/5</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <style>
    .hover-effect:hover {
        background-color: rgba(220, 53, 69, 0.2) !important;
        color: white !important;
    }
    .nav-link {
        transition: all 0.2s ease;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>