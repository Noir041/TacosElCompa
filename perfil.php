<?php
// PRIMERA LÍNEA, sin espacios ni nada antes
session_start();

// Activar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/funciones.php';

// Verificar sesión manualmente
if(!isset($_SESSION['usuario_id'])) {
    die("Error: No hay sesión activa. <a href='login.php'>Inicia sesión</a>");
}

requiereCliente();

$titulo = 'Mi Perfil';
$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

if(!$usuario) {
    die("Error: Usuario no encontrado en la base de datos.");
}

// ACTUALIZAR FOTO DE PERFIL
if(isset($_POST['actualizar_foto']) && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tamano_max = 3 * 1024 * 1024;
    
    if(!in_array($_FILES['foto_perfil']['type'], $permitidos)) {
        $mensaje = '❌ Formato no válido. Usa JPG, PNG, GIF o WebP';
    } elseif($_FILES['foto_perfil']['size'] > $tamano_max) {
        $mensaje = '❌ La imagen es muy grande. Máximo 3MB';
    } else {
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nueva_foto = 'user_' . $usuario_id . '_' . time() . '.' . $ext;
        
        $foto_anterior = $usuario['foto_perfil'] ?? 'default-user.png';
        if($foto_anterior != 'default-user.png' && file_exists('subir/' . $foto_anterior)) {
            unlink('subir/' . $foto_anterior);
        }
        
        if(move_uploaded_file($_FILES['foto_perfil']['tmp_name'], 'subir/' . $nueva_foto)) {
            $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $stmt->execute([$nueva_foto, $usuario_id]);
            $_SESSION['usuario_foto'] = $nueva_foto;
            $mensaje = '✅ Foto de perfil actualizada correctamente';
            
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch();
        } else {
            $mensaje = '❌ Error al subir la imagen. Intenta de nuevo';
        }
    }
}

// ACTUALIZAR DATOS PERSONALES
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?");
    $stmt->execute([$nombre, $telefono, $direccion, $usuario_id]);
    
    $mensaje = '✅ Perfil actualizado correctamente';
    $_SESSION['usuario_nombre'] = $nombre;
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
}

// CAMBIAR CONTRASEÑA
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_password'])) {
    $actual = $_POST['password_actual'];
    $nueva = $_POST['password_nueva'];
    $confirmar = $_POST['password_confirmar'];
    
    if(!password_verify($actual, $usuario['password'])) {
        $error_pass = 'La contraseña actual es incorrecta';
    } elseif($nueva !== $confirmar) {
        $error_pass = 'Las contraseñas no coinciden';
    } elseif(strlen($nueva) < 6) {
        $error_pass = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $usuario_id]);
        $mensaje = '✅ Contraseña actualizada correctamente';
    }
}

// PAGINACIÓN DEL HISTORIAL
$por_pagina = 5;
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ?");
$stmtTotal->execute([$usuario_id]);
$total_pedidos = $stmtTotal->fetchColumn();
$total_paginas = ceil($total_pedidos / $por_pagina);
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Historial de pedidos con límite
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha_pedido DESC LIMIT ? OFFSET ?");
$stmt->execute([$usuario_id, $por_pagina, $offset]);
$pedidos = $stmt->fetchAll();

// Últimas notificaciones no leídas
$ultimasNotis = getNotificacionesNoLeidas($pdo, $usuario_id);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <?php if($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h2 class="mb-4"><i class="bi bi-person-circle text-danger"></i> Mi Perfil</h2>

    <!-- 🔔 Últimas notificaciones -->
    <?php if(count($ultimasNotis) > 0): ?>
    <div class="alert alert-info alert-dismissible fade show mb-4 border-start border-info border-4">
        <div class="d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-bell-fill"></i> Novedades de tus pedidos:</strong>
            <a href="notificaciones.php" class="badge bg-info text-dark text-decoration-none">
                Ver todas <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <ul class="mb-0 mt-2">
            <?php foreach(array_slice($ultimasNotis, 0, 3) as $noti): ?>
                <li><?php echo htmlspecialchars($noti['mensaje']); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- FOTO DE PERFIL -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-camera"></i> Foto de Perfil</h5>
                </div>
                <div class="card-body text-center py-4">
                    <img src="subir/<?php echo htmlspecialchars($usuario['foto_perfil'] ?? 'default-user.png'); ?>" 
                         class="rounded-circle border border-4 border-danger shadow" 
                         width="150" height="150" 
                         style="object-fit: cover;"
                         id="fotoPerfilPreview"
                         alt="Foto de perfil">
                    
                    <form method="POST" enctype="multipart/form-data" class="mt-3">
                        <input type="file" name="foto_perfil" id="inputFotoPerfil" 
                               accept="image/*" style="display: none;" 
                               onchange="previewImage(event); document.getElementById('btnGuardarFoto').style.display='inline-block';">
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="document.getElementById('inputFotoPerfil').click()">
                            <i class="bi bi-camera"></i> Cambiar foto
                        </button>
                        <button type="submit" name="actualizar_foto" id="btnGuardarFoto" 
                                class="btn btn-danger" style="display: none;">
                            <i class="bi bi-check-lg"></i> Guardar foto
                        </button>
                    </form>
                    <p class="text-muted mt-2 small">Formatos: JPG, PNG, GIF, WebP. Máx. 3MB</p>
                </div>
            </div>
        </div>

        <!-- Datos personales -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Datos Personales</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                            <small class="text-muted">El correo no se puede cambiar</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección de entrega</label>
                            <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="actualizar" class="btn btn-danger">
                            <i class="bi bi-check-lg"></i> Guardar cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cambiar contraseña -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-lock"></i> Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($error_pass)): ?>
                        <div class="alert alert-danger"><?php echo $error_pass; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="password_nueva" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="password_confirmar" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" name="cambiar_password" class="btn btn-warning">
                            <i class="bi bi-key"></i> Cambiar contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de pedidos -->
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Pedidos</h5>
        </div>
        <div class="card-body">
            <?php if(count($pedidos) == 0): ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aún no has hecho ningún pedido</p>
                    <a href="index.php" class="btn btn-danger">Ver menú y pedir</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th># Pedido</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Calificación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pedidos as $ped): ?>
                            <tr>
                                <td><strong>#<?php echo $ped['id']; ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></td>
                                <td class="fw-bold text-success">$<?php echo number_format($ped['total'], 2); ?></td>
                                <td>
                                    <?php 
                                    switch($ped['estado']) {
                                        case 'pendiente': $badgeColor = 'warning'; break;
                                        case 'en_cocina': $badgeColor = 'info'; break;
                                        case 'listo': $badgeColor = 'success'; break;
                                        case 'en_camino': $badgeColor = 'primary'; break;
                                        case 'entregado': $badgeColor = 'secondary'; break;
                                        case 'cancelado': $badgeColor = 'danger'; break;
                                        default: $badgeColor = 'light';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badgeColor; ?>">
                                        <?php echo estadoPedido($ped['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($ped['estado'] == 'entregado'): ?>
                                        <?php
                                        $stmtCal = $pdo->prepare("SELECT puntuacion FROM calificaciones WHERE usuario_id = ? AND pedido_id = ?");
                                        $stmtCal->execute([$usuario_id, $ped['id']]);
                                        $calificacion = $stmtCal->fetch();
                                        ?>
                                        <?php if($calificacion): ?>
                                            <span class="text-warning">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i <= $calificacion['puntuacion'] ? '-fill' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                        <?php else: ?>
                                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#modalCalificar<?php echo $ped['id']; ?>">
                                                <i class="bi bi-star"></i> Calificar
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">Disponible al entregar</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                        (Mostrando <?php echo count($pedidos); ?> de <?php echo $total_pedidos; ?> pedidos)
                    </p>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modales de calificación -->
<?php foreach($pedidos as $ped): ?>
    <?php if($ped['estado'] == 'entregado'): ?>
        <?php
        $stmtCal = $pdo->prepare("SELECT puntuacion FROM calificaciones WHERE usuario_id = ? AND pedido_id = ?");
        $stmtCal->execute([$usuario_id, $ped['id']]);
        $yaCalifico = $stmtCal->fetch();
        ?>
        <?php if(!$yaCalifico): ?>
            <?php
            $stmtDet = $pdo->prepare("
                SELECT dp.*, pl.nombre, pl.id as platillo_id 
                FROM detalle_pedidos dp 
                JOIN platillos pl ON dp.platillo_id = pl.id 
                WHERE dp.pedido_id = ?
            ");
            $stmtDet->execute([$ped['id']]);
            $detallesPedido = $stmtDet->fetchAll();
            ?>
            <?php if(count($detallesPedido) > 0): ?>
            <div class="modal fade" id="modalCalificar<?php echo $ped['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="calificar.php">
                            <input type="hidden" name="pedido_id" value="<?php echo $ped['id']; ?>">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title"><i class="bi bi-star-fill"></i> Calificar Pedido #<?php echo $ped['id']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php foreach($detallesPedido as $det): ?>
                                <div class="mb-4 p-3 bg-light rounded">
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($det['nombre']); ?></h6>
                                    <input type="hidden" name="platillo_id[]" value="<?php echo $det['platillo_id']; ?>">
                                    <div class="estrellas-calificacion">
                                        <p class="mb-2 small text-muted">Tu puntuación:</p>
                                        <?php for($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="puntuacion_<?php echo $det['platillo_id']; ?>" 
                                                   value="<?php echo $i; ?>" 
                                                   id="est<?php echo $ped['id']; ?>_<?php echo $det['platillo_id']; ?>_<?php echo $i; ?>" required>
                                            <label for="est<?php echo $ped['id']; ?>_<?php echo $det['platillo_id']; ?>_<?php echo $i; ?>">
                                                <i class="bi bi-star-fill"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="mb-3">
                                    <label class="form-label">Comentario general (opcional)</label>
                                    <textarea name="comentario" class="form-control" rows="2" 
                                              placeholder="Cuéntanos tu experiencia..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-warning fw-bold">
                                    <i class="bi bi-star-fill"></i> Enviar calificación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Estilos para las estrellas -->
<style>
.estrellas-calificacion {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}
.estrellas-calificacion input {
    display: none;
}
.estrellas-calificacion label {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    transition: color 0.2s;
}
.estrellas-calificacion label:hover,
.estrellas-calificacion label:hover ~ label,
.estrellas-calificacion input:checked ~ label {
    color: #ffc107;
}
.page-link { color: #dc3545; }
.page-item.active .page-link { background-color: #dc3545; border-color: #dc3545; }
.page-link:hover { color: #dc3545; }
</style>

<!-- Script para preview de foto -->
<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('fotoPerfilPreview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include 'includes/footer.php'; ?>