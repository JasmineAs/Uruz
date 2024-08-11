<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Conexión a la base de datos
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inicializa la respuesta
    $response = ['success' => false, 'message' => ''];

    try {
        // Captura los datos del formulario
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $telefono = $_POST['telefono'] ?? null;

        // Manejo de la foto de perfil
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto']['tmp_name'];
            $fileName = $_FILES['foto']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExts)) {
                $uploadFileDir = './uploads/';
                $dest_file = $uploadFileDir . $fileName;

                // Elimina la foto anterior si existe
                $oldPhoto = $_SESSION['user']['foto'];
                if ($oldPhoto && $oldPhoto !== 'uploads/default.png' && file_exists($oldPhoto)) {
                    unlink($oldPhoto);
                }

                if (move_uploaded_file($fileTmpPath, $dest_file)) {
                    // Actualiza la foto en la base de datos
                    $stmt = $conn->prepare("UPDATE miembros SET foto = ? WHERE correo_electronico = ?");
                    $stmt->bind_param("ss", $dest_file, $_SESSION['user']['correo_electronico']);
                    if ($stmt->execute()) {
                        $_SESSION['user']['foto'] = $dest_file;
                    } else {
                        throw new Exception('Error al actualizar la foto: ' . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    throw new Exception('Error al subir el archivo.');
                }
            } else {
                throw new Exception('Extensión de archivo no permitida.');
            }
        }

        // Actualiza los datos del perfil en la base de datos
        $stmt = $conn->prepare("UPDATE miembros SET nombre = ?, correo_electronico = ?, telefono = ? WHERE correo_electronico = ?");
        $stmt->bind_param("ssss", $nombre, $correo, $telefono, $_SESSION['user']['correo_electronico']);
        if ($stmt->execute()) {
            // Actualiza los datos en la sesión
            $_SESSION['user']['nombre'] = $nombre;
            $_SESSION['user']['correo_electronico'] = $correo;
            $_SESSION['user']['telefono'] = $telefono;

            $response['success'] = true;
            $response['message'] = 'Datos actualizados correctamente.';
        } else {
            throw new Exception('Error al actualizar los datos: ' . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    // Envía la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
