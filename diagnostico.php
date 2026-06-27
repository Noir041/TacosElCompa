<?php
// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Diagnóstico de tu sitio</h1>";

// 1. Verificar versión de PHP
echo "<h3>1. PHP Version</h3>";
echo "<p>Versión: " . phpversion() . "</p>";

// 2. Verificar si PDO está activo
echo "<h3>2. Extensiones necesarias</h3>";
echo "<p>PDO: " . (extension_loaded('pdo') ? '✅ Activado' : '❌ Faltante') . "</p>";
echo "<p>PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Activado' : '❌ Faltante') . "</p>";
echo "<p>MySQLi: " . (extension_loaded('mysqli') ? '✅ Activado' : '❌ Faltante') . "</p>";

// 3. Probar conexión a base de datos
echo "<h3>3. Conexión a base de datos</h3>";

// AJUSTA ESTOS DATOS CON LOS DE INFINITYFREE
$host = 'sql210.infinityfree.com';  // <- Cambia esto
$db   = 'if0_42249305_elcompa';   // <- Cambia esto
$user = 'if0_42249305';        // <- Cambia esto
$pass = 'JaneDoe29';     // <- Cambia esto

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ Conexión exitosa a la base de datos</p>";
    
    // Mostrar tablas
    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tablas encontradas: " . count($tablas) . "</p>";
    echo "<ul>";
    foreach($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// 4. Verificar archivos
echo "<h3>4. Archivos del proyecto</h3>";
$archivos = ['index.php', 'login.php', 'includes/db.php', 'includes/header.php', 'admin/login.php'];
foreach($archivos as $archivo) {
    echo "<p>$archivo: " . (file_exists($archivo) ? '✅ Existe' : '❌ No encontrado') . "</p>";
}

// 5. Verificar carpeta de subida
echo "<h3>5. Carpeta de imágenes</h3>";
echo "<p>subir/: " . (is_dir('subir') ? '✅ Existe' : '❌ No existe') . "</p>";
if(is_dir('subir')) {
    echo "<p>Permisos: " . substr(sprintf('%o', fileperms('subir')), -4) . "</p>";
}
?>