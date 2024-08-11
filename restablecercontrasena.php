<?php
require_once 'conexion.php';

$step = $_GET['step'] ?? 'email'; // Determina el paso actual

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 'email') {
        $email = $_POST['email'] ?? '';

        if ($email) {
            // Verifica si el correo electrónico existe en la base de datos
            $sql = "SELECT id FROM usuarios WHERE correo_electronico = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Enviar al usuario a la página para cambiar la contraseña
                header("Location: restablecercontrasena.php?step=password&email=" . urlencode($email));
                exit();
            } else {
                $error = "No se encontró el correo electrónico en nuestra base de datos.";
            }
            $stmt->close();
        } else {
            $error = "Por favor, ingresa tu correo electrónico.";
        }
    } elseif ($step == 'password') {
        $email = $_GET['email'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if ($email && $new_password) {
            // Encriptar la nueva contraseña
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $sql = "UPDATE usuarios SET password = ? WHERE correo_electronico = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashedPassword, $email);

            if ($stmt->execute()) {
                $message = "Cambio de contraseña exitoso. Serás redirigido al inicio de sesión.";
                $success = true;
                header("refresh:3;url=login.php"); // Redirige después de 3 segundos
            } else {
                $error = "Error al cambiar la contraseña: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Por favor, ingresa una nueva contraseña.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="Css/restablecercontrasena.css">
</head>
<body>
    <div class="form-container">
        <?php if ($step == 'email'): ?>
            <h1>Restablecer Contraseña</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <button type="submit">Enviar enlace de restablecimiento</button>
                </div>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        <?php elseif ($step == 'password'): ?>
            <h1>Establecer Nueva Contraseña</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <button type="submit">Cambiar Contraseña</button>
                </div>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if (isset($message)): ?>
                    <p class="success"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
