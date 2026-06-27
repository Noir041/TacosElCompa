<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Marcar como leída
if(isset($_GET['leer']) && is_numeric($_GET['leer'])) {
    marcarLeida($pdo, intval($_GET['leer']), $usuario_id);
    header('Location: notificaciones.php');
    exit;
}

// Marcar todas como leídas
if(isset($_GET['leer_todas'])) {
    marcarTodasLeidas($pdo, $usuario_id);
    header('Location: notificaciones.php');
    exit;
}

// Obtener todas las notificaciones
$stmt = $pdo->prepare("
    SELECT n.*, p.estado as estado_pedido 
    FROM notificaciones n 
    LEFT JOIN pedidos p ON n.pedido_id = p.id 
    WHERE n.usuario_id = ? 
    ORDER BY n.fecha_creacion DESC 
    LIMIT 50
");
$stmt->execute([$usuario_id]);
$notificaciones = $stmt->fetchAll();

$noLeidas = contarNotificaciones($pdo, $usuario_id);

$titulo = 'Mis Notificaciones';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bell text-danger"></i> Notificaciones</h2>
        <?php if($noLeidas > 0): ?>
            <a href="?leer_todas=1" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-check-all"></i> Marcar todas como leídas
            </a>
        <?php endif; ?>
    </div>

    <?php if(count($notificaciones) == 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
            <h3 class="text-muted mt-3">No tienes notificaciones</h3>
            <p class="text-muted">Aquí aparecerán las actualizaciones de tus pedidos</p>
            <a href="index.php" class="btn btn-danger mt-3">Ver menú</a>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php foreach($notificaciones as $noti): 
                    $bgColor = $noti['leida'] ? '' : 'bg-light border-start border-danger border-3';
                    $icono = match($noti['tipo']) {
                        'estado' => 'bi-truck',
                        default => 'bi-info-circle'
                    };
                ?>
                <div class="card shadow-sm border-0 mb-2 <?php echo $bgColor; ?>">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div>
                            <p class="mb-1 <?php echo $noti['leida'] ? '' : 'fw-bold'; ?>">
                                <i class="bi <?php echo $icono; ?> text-danger me-2"></i>
                                <?php echo htmlspecialchars($noti['mensaje']); ?>
                            </p>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($noti['fecha_creacion'])); ?>
                            </small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <?php if(!$noti['leida']): ?>
                                <a href="?leer=<?php echo $noti['id']; ?>" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check"></i> Leída
                                </a>
                                <span class="badge bg-danger rounded-pill">Nueva</span>
                            <?php else: ?>
                                <span class="text-muted small">Leída</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>