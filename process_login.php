<?php
session_start();
require_once 'conexion.php';

// Habilita la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Conexión a la base de datos y verificación de usuario
    try {
        // Verificar si el usuario es un miembro
        $stmt = $conn->prepare(
            "SELECT m.id, m.nombre, m.correo_electronico, m.telefono, m.foto, m.fecha_inicioplan, m.fecha_terminoplan, m.id_tipoplan, u.password
             FROM miembros m
             JOIN usuarios u ON m.id_usuario = u.id
             WHERE u.correo_electronico = ?"
        );
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'correo_electronico' => $user['correo_electronico'],
                    'telefono' => $user['telefono'],
                    'foto' => $user['foto'],
                    'fecha_inicioplan' => $user['fecha_inicioplan'],
                    'fecha_terminoplan' => $user['fecha_terminoplan'],
                    'id_tipoplan' => $user['id_tipoplan']
                ];
                $_SESSION['user_role'] = 'member'; // Rol de usuario es 'member'
                header("Location: perfilavance.php");
                exit();
            } else {
                echo "<p>Error: Contraseña incorrecta.</p>";
            }
        } else {
            // Verificar si el usuario es un coach
            $stmt = $conn->prepare(
                "SELECT u.id AS user_id, u.correo_electronico, u.password, u.primer_inicio, c.id AS coach_id, c.nombre, c.telefono, '' AS foto, tu.tipo 
                 FROM usuarios u 
                 JOIN coach c ON u.id = c.id_usuario 
                 JOIN tipoUsuario tu ON u.id_tipoUsuario = tu.id
                 WHERE u.correo_electronico = ? AND u.id_tipoUsuario = 2"
            );
            if ($stmt === false) {
                throw new Exception("Error en la preparación de la consulta: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // El usuario es un coach
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user'] = [
                        'id' => $user['user_id'],
                        'nombre' => $user['nombre'],
                        'correo_electronico' => $user['correo_electronico'],
                        'telefono' => $user['telefono'],
                        'foto' => $user['foto']
                    ];
                    $_SESSION['user_role'] = 'coach'; // Rol de usuario es 'coach'
                    
                    // Verificar si es el primer inicio de sesión
                    if ($user['primer_inicio'] == 1) {
                        header("Location: cambiarcontrasenacoach.php");
                    } else {
                        header("Location: perfilavance.php");
                    }
                    exit();
                } else {
                    echo "<p>Error: Contraseña incorrecta.</p>";
                }
            } else {
                echo "<p>Error: No se encontró un usuario con ese correo electrónico.</p>";
            }
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo "<p>Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Formulario no enviado correctamente.</p>";
}
?>




