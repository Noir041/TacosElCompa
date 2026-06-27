<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// Configuración de paginación
$por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Contar total de clientes
$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn();
$total_paginas = ceil($total_clientes / $por_pagina);

// Obtener clientes con límite
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(p.id) as total_pedidos,
           COALESCE(SUM(p.total), 0) as total_gastado
    FROM usuarios u 
    LEFT JOIN pedidos p ON u.id = p.usuario_id 
    WHERE u.rol = 'cliente' 
    GROUP BY u.id 
    ORDER BY u.fecha_registro DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$por_pagina, $offset]);
$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Admin</title>
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
                        <li class="nav-item"><a href="usuarios.php" class="nav-link rounded-3 active bg-danger text-white"><i class="bi bi-people me-2"></i> Clientes</a></li>
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
                        <h2 class="fw-bold"><i class="bi bi-people text-danger"></i> Clientes Registrados</h2>
                        <p class="text-muted">Total: <strong><?php echo $total_clientes; ?></strong> clientes</p>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="ps-4">Foto</th>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Dirección</th>
                                        <th class="text-center">Pedidos</th>
                                        <th class="text-end">Total gastado</th>
                                        <th>Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($clientes as $c): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <a href="../subir/<?php echo htmlspecialchars($c['foto_perfil'] ?? 'default-user.png'); ?>" target="_blank">
                                                <img src="../subir/<?php echo htmlspecialchars($c['foto_perfil'] ?? 'default-user.png'); ?>" 
                                                     class="rounded-circle border border-2 border-secondary" 
                                                     width="45" height="45" style="object-fit: cover;">
                                            </a>
                                        </td>
                                        <td class="fw-bold">#<?php echo $c['id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td><?php echo htmlspecialchars($c['telefono'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['direccion'] ?? '-'); ?></td>
                                        <td class="text-center"><span class="badge bg-info"><?php echo $c['total_pedidos']; ?></span></td>
                                        <td class="text-end text-success fw-bold">$<?php echo number_format($c['total_gastado'] ?? 0, 2); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($c['fecha_registro'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>

                                    <?php if(count($clientes) == 0): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No hay clientes registrados</td>
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
                        (Mostrando <?php echo count($clientes); ?> de <?php echo $total_clientes; ?> clientes)
                    </p>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

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