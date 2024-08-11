<?php
require_once 'conexion.php';

session_start();

// Verificar si el usuario no está logueado o si no es coach
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirigir a login si no está logueado
    exit();
}

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user']['id'] ?? null;

if ($user_id === null) {
    die("Error: El identificador del usuario no está disponible.");
}

// Verificar si es el primer inicio de sesión
$sql = "SELECT primer_inicio FROM usuarios WHERE id = ? AND id_tipoUsuario = 2";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';

    if ($nueva_contrasena) {
        // Encriptar la nueva contraseña
        $hashedPassword = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $sqlUpdate = "UPDATE usuarios SET password = ?, primer_inicio = 0 WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        if ($stmtUpdate === false) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmtUpdate->bind_param("si", $hashedPassword, $user_id);
        $stmtUpdate->execute();

        if ($stmtUpdate->affected_rows > 0) {
            header("Location: login.php");
            
            exit();
        } else {
            echo "<p>Error: No se pudo actualizar la contraseña.</p>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="Css/cambiarcontra.css">
</head>
<body>

<div class="form-container">
    <h1>Cambiar Contraseña</h1>
    <form method="POST">
        <div class="form-group">
            <label for="nueva_contrasena">Nueva Contraseña</label>
            <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
        </div>
        <button type="submit" class="button">Actualizar Contraseña</button>
    </form>
</div>

</body>
</html>

