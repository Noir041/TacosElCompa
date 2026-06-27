<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';
require_once '../includes/funciones.php';

$mensaje = '';

// Cambiar estado
if(isset($_POST['cambiar_estado'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $nuevo_estado = $_POST['estado'];
    
    // Obtener datos del pedido antes de actualizar
    $stmtPed = $pdo->prepare("SELECT usuario_id, estado FROM pedidos WHERE id = ?");
    $stmtPed->execute([$pedido_id]);
    $pedidoInfo = $stmtPed->fetch();
    
    // Actualizar estado
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $pedido_id]);
    
    // Crear notificación según el nuevo estado
    $mensajesNoti = [
        'pendiente' => '⏳ Tu pedido #' . $pedido_id . ' está pendiente de revisión',
        'en_cocina' => '👨‍🍳 ¡Tu pedido #' . $pedido_id . ' ya está en cocina!',
        'listo' => '✅ ¡Tu pedido #' . $pedido_id . ' está listo! Espera al repartidor',
        'en_camino' => '🛵 ¡Tu pedido #' . $pedido_id . ' va en camino!',
        'entregado' => '📦 ¡Tu pedido #' . $pedido_id . ' ha sido entregado! ¡Buen provecho!',
        'cancelado' => '❌ Tu pedido #' . $pedido_id . ' ha sido cancelado'
    ];
    
    if(isset($mensajesNoti[$nuevo_estado])) {
        crearNotificacion($pdo, $pedidoInfo['usuario_id'], $pedido_id, $mensajesNoti[$nuevo_estado]);
    }
    
    $mensaje = '✅ Pedido #' . $pedido_id . ' actualizado a: ' . estadoPedido($nuevo_estado);
}

// Obtener TODOS los pedidos (para contadores y filtros)
$pedidos_todos = $pdo->query("
    SELECT p.*, u.nombre as cliente, u.telefono, u.direccion, u.foto_perfil
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY 
        CASE p.estado 
            WHEN 'pendiente' THEN 1 
            WHEN 'en_cocina' THEN 2 
            WHEN 'listo' THEN 3 
            WHEN 'en_camino' THEN 4 
            WHEN 'entregado' THEN 5 
            WHEN 'cancelado' THEN 6 
        END,
        p.fecha_pedido DESC
")->fetchAll();

// Contar por estado (de todos los pedidos)
$pendientes = 0; $enCocina = 0; $listos = 0; $enCamino = 0; $entregados = 0; $cancelados = 0;
foreach($pedidos_todos as $ped) {
    switch($ped['estado']) {
        case 'pendiente': $pendientes++; break;
        case 'en_cocina': $enCocina++; break;
        case 'listo': $listos++; break;
        case 'en_camino': $enCamino++; break;
        case 'entregado': $entregados++; break;
        case 'cancelado': $cancelados++; break;
    }
}

// PAGINACIÓN
$por_pagina = 3;
$total_pedidos = count($pedidos_todos);
$total_paginas = ceil($total_pedidos / $por_pagina);
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Obtener solo los pedidos de la página actual
$pedidos = array_slice($pedidos_todos, $offset, $por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Admin</title>
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
                        <li class="nav-item"><a href="pedidos.php" class="nav-link rounded-3 active bg-danger text-white"><i class="bi bi-box-seam me-2"></i> Pedidos</a></li>
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
                        <h2 class="fw-bold"><i class="bi bi-box-seam text-danger"></i> Gestión de Pedidos</h2>
                        <p class="text-muted">Total: <strong><?php echo $total_pedidos; ?></strong> pedidos</p>
                    </div>
                </div>

                <?php if($mensaje): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?php echo $mensaje; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <!-- Cards resumen -->
                <div class="row g-3 mb-4">
                    <div class="col">
                        <div class="card border-0 shadow-sm bg-warning bg-gradient text-white text-center">
                            <div class="card-body py-3">
                                <h3 class="fw-bold mb-0"><?php echo $pendientes; ?></h3>
                                <small class="text-white-50">⏳ Pendientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm bg-info bg-gradient text-white text-center">
                            <div class="card-body py-3">
                                <h3 class="fw-bold mb-0"><?php echo $enCocina; ?></h3>
                                <small class="text-white-50">👨‍🍳 En cocina</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm bg-success bg-gradient text-white text-center">
                            <div class="card-body py-3">
                                <h3 class="fw-bold mb-0"><?php echo $listos; ?></h3>
                                <small class="text-white-50">✅ Listos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm bg-primary bg-gradient text-white text-center">
                            <div class="card-body py-3">
                                <h3 class="fw-bold mb-0"><?php echo $enCamino; ?></h3>
                                <small class="text-white-50">🛵 En camino</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm bg-secondary bg-gradient text-white text-center">
                            <div class="card-body py-3">
                                <h3 class="fw-bold mb-0"><?php echo $entregados; ?></h3>
                                <small class="text-white-50">📦 Entregados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm bg-danger bg-gradient text-white text-center">
                            <div class="card-body py-3">
                                <h3 class="fw-bold mb-0"><?php echo $cancelados; ?></h3>
                                <small class="text-white-50">❌ Cancelados</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-secondary btn-sm filtrar-pedido active" data-estado="todos">Todos</button>
                    <button class="btn btn-outline-warning btn-sm filtrar-pedido" data-estado="pendiente">⏳ Pendientes</button>
                    <button class="btn btn-outline-info btn-sm filtrar-pedido" data-estado="en_cocina">👨‍🍳 En cocina</button>
                    <button class="btn btn-outline-success btn-sm filtrar-pedido" data-estado="listo">✅ Listos</button>
                    <button class="btn btn-outline-primary btn-sm filtrar-pedido" data-estado="en_camino">🛵 En camino</button>
                    <button class="btn btn-outline-secondary btn-sm filtrar-pedido" data-estado="entregado">📦 Entregados</button>
                    <button class="btn btn-outline-danger btn-sm filtrar-pedido" data-estado="cancelado">❌ Cancelados</button>
                </div>

                <!-- Lista de pedidos -->
                <?php foreach($pedidos as $ped): 
                    $stmt = $pdo->prepare("SELECT dp.*, pl.nombre as platillo FROM detalle_pedidos dp JOIN platillos pl ON dp.platillo_id = pl.id WHERE dp.pedido_id = ?");
                    $stmt->execute([$ped['id']]);
                    $detalles = $stmt->fetchAll();
                    
                    $badgeColor = match($ped['estado']) {
                        'pendiente' => 'warning',
                        'en_cocina' => 'info',
                        'listo' => 'success',
                        'en_camino' => 'primary',
                        'entregado' => 'secondary',
                        'cancelado' => 'danger',
                        default => 'light'
                    };
                ?>
                <div class="card shadow-sm border-0 mb-3 pedido-card" data-estado="<?php echo $ped['estado']; ?>">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <img src="../subir/<?php echo $ped['foto_perfil'] ?? 'default-user.png'; ?>" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                            <div>
                                <strong>Pedido #<?php echo $ped['id']; ?></strong> - 
                                <span class="text-muted"><?php echo htmlspecialchars($ped['cliente']); ?></span>
                                <span class="badge bg-<?php echo $badgeColor; ?> ms-2"><?php echo estadoPedido($ped['estado']); ?></span>
                            </div>
                        </div>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong><i class="bi bi-telephone text-danger"></i></strong> <?php echo htmlspecialchars($ped['telefono']); ?></p>
                                <p class="mb-1"><strong><i class="bi bi-geo-alt text-danger"></i></strong> <?php echo htmlspecialchars($ped['direccion_entrega'] ?? $ped['direccion']); ?></p>
                                <?php if($ped['notas']): ?>
                                    <p class="mb-0"><strong><i class="bi bi-chat-dots text-danger"></i></strong> <?php echo htmlspecialchars($ped['notas']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-5">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light"><tr><th>Platillo</th><th>Cant.</th><th>Subtotal</th></tr></thead>
                                    <tbody>
                                        <?php foreach($detalles as $d): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($d['platillo']); ?></td>
                                            <td><?php echo $d['cantidad']; ?></td>
                                            <td>$<?php echo number_format($d['subtotal'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr><td colspan="2" class="text-end fw-bold">Total:</td><td class="fw-bold text-success fs-5">$<?php echo number_format($ped['total'], 2); ?></td></tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <form method="POST" class="d-flex gap-2 align-items-end h-100">
                                    <input type="hidden" name="pedido_id" value="<?php echo $ped['id']; ?>">
                                    <select name="estado" class="form-select form-select-sm">
                                        <option value="pendiente" <?php echo $ped['estado']=='pendiente'?'selected':''; ?>>⏳ Pendiente</option>
                                        <option value="en_cocina" <?php echo $ped['estado']=='en_cocina'?'selected':''; ?>>👨‍🍳 En cocina</option>
                                        <option value="listo" <?php echo $ped['estado']=='listo'?'selected':''; ?>>✅ Listo</option>
                                        <option value="en_camino" <?php echo $ped['estado']=='en_camino'?'selected':''; ?>>🛵 En camino</option>
                                        <option value="entregado" <?php echo $ped['estado']=='entregado'?'selected':''; ?>>📦 Entregado</option>
                                        <option value="cancelado" <?php echo $ped['estado']=='cancelado'?'selected':''; ?>>❌ Cancelado</option>
                                    </select>
                                    <button type="submit" name="cambiar_estado" class="btn btn-danger btn-sm">Actualizar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if($total_pedidos == 0): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">No hay pedidos</h4>
                        <p class="text-muted">Los pedidos aparecerán aquí cuando los clientes compren</p>
                    </div>
                <?php endif; ?>

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
                        (Mostrando <?php echo count($pedidos); ?> de <?php echo $total_pedidos; ?> pedidos)
                    </p>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <style>
    .hover-effect:hover { background-color: rgba(220, 53, 69, 0.2) !important; color: white !important; }
    .nav-link { transition: all 0.2s ease; }
    .filtrar-pedido.active { font-weight: bold; }
    .page-link { color: #dc3545; }
    .page-item.active .page-link { background-color: #dc3545; border-color: #dc3545; }
    .page-link:hover { color: #dc3545; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('.filtrar-pedido').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filtrar-pedido').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const estado = this.dataset.estado;
            document.querySelectorAll('.pedido-card').forEach(card => {
                card.style.display = (estado === 'todos' || card.dataset.estado === estado) ? '' : 'none';
            });
        });
    });
    </script>
</body>
</html>