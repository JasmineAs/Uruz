<?php
// Habilita la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Conexion.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    // Encripta la contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Procesa el archivo de la foto
    $uploadDir = 'uploads/';
    $uploadFile = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadFile = $uploadDir . basename($_FILES['photo']['name']);

        // Crea el directorio de subida si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Mueve el archivo a la carpeta de destino
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
            echo "<p>Error al subir la foto.</p>";
            $uploadFile = null;
        }
    } else {
        echo "<p>Error en la carga del archivo.</p>";
    }

    // Inserta los datos en la tabla usuario
    $stmtUsuario = $conn->prepare("INSERT INTO usuarios (correo_electronico, password) VALUES (?, ?)");

    if ($stmtUsuario === false) {
        die("Error en la preparación de la consulta para usuario: " . $conn->error);
    }

    $stmtUsuario->bind_param("ss", $email, $hashedPassword);

    if ($stmtUsuario->execute()) {
        echo "<p>Datos de usuario guardados correctamente.</p>";
    } else {
        echo "<p>Error al registrar el usuario: " . $stmtUsuario->error . "</p>";
    }

    $stmtUsuario->close();

    // Inserta los datos del miembro en la tabla miembros
    $stmtMiembro = $conn->prepare("INSERT INTO miembros (nombre, correo_electronico, telefono, foto) VALUES (?, ?, ?, ?)");

    if ($stmtMiembro === false) {
        die("Error en la preparación de la consulta para miembro: " . $conn->error);
    }

    $stmtMiembro->bind_param("ssss", $name,$email, $phone, $uploadFile);

    if ($stmtMiembro->execute()) {
        echo "<p>Registro exitoso. ¡Bienvenido, $name!</p>";
    } else {
        echo "<p>Error al registrar el miembro: " . $stmtMiembro->error . "</p>";
    }

    $stmtMiembro->close();
    $conn->close();
} else {
    echo "<p>Formulario no enviado correctamente.</p>";
}
?>
