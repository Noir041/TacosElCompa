<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$mensaje = '';
$admin_id = $_SESSION['admin_id'];

// Obtener datos
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Actualizar datos
if(isset($_POST['actualizar_datos'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
    $stmt->execute([$nombre, $email, $admin_id]);
    
    $_SESSION['admin_nombre'] = $nombre;
    $mensaje = '✅ Datos actualizados correctamente';
    
    // Recargar
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
}

// Cambiar contraseña
if(isset($_POST['cambiar_password'])) {
    $actual = $_POST['password_actual'];
    $nueva = $_POST['password_nueva'];
    $confirmar = $_POST['password_confirmar'];
    
    if(!password_verify($actual, $admin['password'])) {
        $error = 'Contraseña actual incorrecta';
    } elseif($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden';
    } elseif(strlen($nueva) < 6) {
        $error = 'Mínimo 6 caracteres';
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $admin_id]);
        $mensaje = '✅ Contraseña actualizada correctamente';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-danger shadow sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-speedometer2"></i> Panel Admin</a>
            <div>
                <span class="text-white me-3"><?php echo $_SESSION['admin_nombre']; ?></span>
                <a href="cerrar-sesion.php" class="btn btn-outline-light btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 bg-dark min-vh-100 py-4">
                <div class="px-3">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a href="index.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a></li>
                        <li class="nav-item"><a href="platillos.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-egg-fried me-2"></i> Platillos</a></li>
                        <li class="nav-item"><a href="categorias.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-tags me-2"></i> Categorías</a></li>
                        <li class="nav-item"><a href="pedidos.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-box-seam me-2"></i> Pedidos</a></li>
                        <li class="nav-item"><a href="usuarios.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-people me-2"></i> Clientes</a></li>
                        <li class="nav-item"><a href="promociones.php" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-megaphone me-2"></i> Promociones</a></li>
                    </ul>
                    <hr class="text-secondary my-3">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item"><a href="perfil.php" class="nav-link rounded-3 active bg-danger text-white"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        <li class="nav-item"><a href="../index.php" target="_blank" class="nav-link rounded-3 text-white-50 hover-effect"><i class="bi bi-eye me-2"></i> Ver tienda</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 p-4">
                <h2 class="fw-bold mb-4"><i class="bi bi-person-gear text-danger"></i> Mi Perfil Admin</h2>

                <?php if($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-danger text-white"><h5 class="mb-0">Datos de la cuenta</h5></div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($admin['nombre']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Correo electrónico</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                    </div>
                                    <button type="submit" name="actualizar_datos" class="btn btn-danger">Guardar cambios</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-warning text-dark"><h5 class="mb-0">Cambiar contraseña</h5></div>
                            <div class="card-body">
                                <?php if(isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
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
                                        <label class="form-label">Confirmar contraseña</label>
                                        <input type="password" name="password_confirmar" class="form-control" required minlength="6">
                                    </div>
                                    <button type="submit" name="cambiar_password" class="btn btn-warning">Cambiar contraseña</button>
                                </form>
                            </div>
                        </div>
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