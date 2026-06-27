<?php
// Redirigir si no ha iniciado sesión como cliente
function requiereCliente() {
    if(!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Redirigir si no ha iniciado sesión como admin
function requiereAdmin() {
    if(!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Subir imagen y devolver nombre único
function subirImagen($archivo, $carpeta = 'subir/') {
    $extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $nombre = $archivo['name'];
    $tmp = $archivo['tmp_name'];
    
    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    
    if(!in_array($ext, $extensiones)) {
        return false;
    }
    
    $nuevoNombre = uniqid('platillo_') . '.' . $ext;
    $destino = $carpeta . $nuevoNombre;
    
    if(move_uploaded_file($tmp, $destino)) {
        return $nuevoNombre;
    }
    
    return false;
}

// Obtener nombre del estado del pedido en español
function estadoPedido($estado) {
    $estados = [
        'pendiente' => '⏳ Pendiente',
        'en_cocina' => '👨‍🍳 En cocina',
        'listo' => '✅ Listo',
		'en_camino' => '🛵 En camino',
        'entregado' => '📦 Entregado',
        'cancelado' => '❌ Cancelado'
    ];
    return $estados[$estado] ?? $estado;
}

// Verificar si un platillo es favorito del usuario
function esFavorito($pdo, $usuario_id, $platillo_id) {
    $stmt = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND platillo_id = ?");
    $stmt->execute([$usuario_id, $platillo_id]);
    return $stmt->fetch() ? true : false;
}

// Crear notificación para un usuario
function crearNotificacion($pdo, $usuario_id, $pedido_id, $mensaje, $tipo = 'estado') {
    $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, pedido_id, mensaje, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $pedido_id, $mensaje, $tipo]);
}

// Obtener notificaciones no leídas de un usuario
function getNotificacionesNoLeidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? AND leida = FALSE ORDER BY fecha_creacion DESC");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

// Contar notificaciones no leídas
function contarNotificaciones($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = FALSE");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchColumn();
}

// Marcar notificación como leída
function marcarLeida($pdo, $notificacion_id, $usuario_id) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$notificacion_id, $usuario_id]);
}

// Marcar todas como leídas
function marcarTodasLeidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
}
?>