<?php
session_start();
require_once '../includes/db.php';
$error = '';

if(isset($_SESSION['admin_id'])) {
    header('Location: panel.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND rol IN ('admin', 'cocinero', 'repartidor')");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_rol'] = $admin['rol'];
        header('Location: panel.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas o no tienes acceso';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-5 col-lg-4">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock-fill text-danger" style="font-size: 3.5rem;"></i>
                            <h3 class="mt-3">Panel Admin</h3>
                            <p class="text-muted small">Acceso restringido</p>
                        </div>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Correo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar al panel
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        <p class="text-center small">
                            <a href="../index.php" class="text-secondary">← Volver a la tienda</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>