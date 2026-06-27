<?php
// Activar errores para depurar
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
$titulo = 'Iniciar Sesión';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Buscar el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND rol = 'cliente'");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if($usuario && password_verify($password, $usuario['password'])) {
        // Iniciar sesión si no está iniciada
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Guardar datos en sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['usuario_foto'] = $usuario['foto_perfil'] ?? 'default-user.png';
        
        // Redirigir
        header('Location: index.php');
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos';
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card shadow border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-danger" style="font-size: 3rem;"></i>
                        <h2 class="mt-2">Iniciar Sesión</h2>
                    </div>
                    
                    <?php if(isset($_GET['registro'])): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> ¡Cuenta creada con éxito! Ahora inicia sesión.
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
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
                            <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </button>
                    </form>
                    
                    <p class="text-center mt-3">
                        ¿No tienes cuenta? <a href="registro.php" class="text-danger fw-bold">Regístrate gratis</a>
                    </p>
                    
                    <!-- 🔑 ACCESO ADMIN -->
                    <hr>
                    <p class="text-center text-muted small mb-0">
                        <i class="bi bi-shield-lock"></i> ¿Eres administrador? 
                        <a href="admin/login.php" class="text-secondary fw-bold">Accede aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>