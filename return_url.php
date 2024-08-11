<?php

require 'vendor/autoload.php';

use Transbank\Webpay\WebpayPlus;

session_start();
require_once 'conexion.php';

if (isset($_GET['token_ws'])) {
    $token = $_GET['token_ws'];
    $transaction = new WebpayPlus\Transaction();

    try {
        // Confirmar la transacción con el token
        $response = $transaction->commit($token);

        if ($response->getStatus() === 'AUTHORIZED') {
            // La transacción fue exitosa
            echo "<p>Pago exitoso.</p>";

            // Obtener datos del formulario desde la sesión
            $name = $_SESSION['name'] ?? null;
            $email = $_SESSION['email'] ?? null;
            $phone = $_SESSION['phone'] ?? null;
            $password = $_SESSION['password'] ?? null;
            $uploadFile = $_SESSION['photo'] ?? null;

            // Si no se ha subido una foto, asignar la foto por defecto
            if (empty($uploadFile)) {
                $uploadFile = 'uploads/default.png'; // Ruta a la imagen por defecto
            }

            // Recupera el ID del plan seleccionado
            $planId = $_SESSION['plan_id'] ?? null;

            if ($name && $email && $phone && $password && $uploadFile && $planId) {
                // Encripta la contraseña
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Recuperar la duración del plan para calcular la fecha de término
                $stmtPlan = $conn->prepare("SELECT duracion FROM tipoplan WHERE id = ?");
                if ($stmtPlan === false) {
                    die("Error en la preparación de la consulta para el plan: " . $conn->error);
                }
                $stmtPlan->bind_param("i", $planId);
                $stmtPlan->execute();
                $stmtPlan->bind_result($duracion);
                $stmtPlan->fetch();
                $stmtPlan->close();

                if ($duracion === null) {
                    echo "<p>Error: No se pudo encontrar la duración del plan seleccionado.</p>";
                    exit();
                }

                // Calcular la fecha de inicio y la fecha de término
                $fechaInicio = date('Y-m-d'); // Fecha actual en formato YYYY-MM-DD
                $fechaTermino = date('Y-m-d', strtotime($fechaInicio . ' + ' . $duracion . ' days'));

                // Inserta datos del usuario
                $stmtUsuario = $conn->prepare("INSERT INTO usuarios (correo_electronico, password, id_tipoUsuario) VALUES (?, ?, ?)");
                if ($stmtUsuario === false) {
                    die("Error en la preparación de la consulta para usuario: " . $conn->error);
                }
                // Asumiendo que el id_tipoUsuario para miembro es 3
                $idTipoUsuario = 3;
                $stmtUsuario->bind_param("ssi", $email, $hashedPassword, $idTipoUsuario);
                if ($stmtUsuario->execute()) {
                    // Recupera el ID del usuario recién insertado
                    $userId = $conn->insert_id;
                    echo "<p>Datos de usuario guardados correctamente.</p>";

                    // Inserta datos del miembro
                    $stmtMiembro = $conn->prepare("INSERT INTO miembros (id_usuario, nombre, correo_electronico, telefono, foto, id_tipoplan, fecha_inicioplan, fecha_terminoplan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmtMiembro === false) {
                        die("Error en la preparación de la consulta para miembro: " . $conn->error);
                    }
                    $stmtMiembro->bind_param("issssiss", $userId, $name, $email, $phone, $uploadFile, $planId, $fechaInicio, $fechaTermino);
                    if ($stmtMiembro->execute()) {
                        echo "<p>Registro exitoso. ¡Bienvenido, $name!</p>";
                        $userM = $conn->insert_id;
                        // Insertar la transacción
                        $amount = $_SESSION['transaction']['amount']; // Obtener monto de la sesión
                        $stmtTransaccion = $conn->prepare("INSERT INTO transaccion (id_miembro, monto, fecha, id_tipoplan) VALUES (?, ?, ?, ?)");
                        if ($stmtTransaccion === false) {
                            die("Error en la preparación de la consulta para transacción: " . $conn->error);
                        }
                        $fecha = date('Y-m-d'); // Fecha actual
                        $stmtTransaccion->bind_param("iisi", $userM, $amount, $fecha, $planId);
                        if (!$stmtTransaccion->execute()) {
                            echo "<p>Error al registrar la transacción: " . $stmtTransaccion->error . "</p>";
                        }
                        $stmtTransaccion->close();
                    } else {
                        echo "<p>Error al registrar el miembro: " . $stmtMiembro->error . "</p>";
                    }
                    $stmtMiembro->close();
                } else {
                    echo "<p>Error al guardar el usuario: " . $stmtUsuario->error . "</p>";
                }
                $stmtUsuario->close();
            } else {
                echo "<p>Datos incompletos.</p>";
            }
        } else {
            echo "<p>Pago fallido.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error al procesar el pago: " . $e->getMessage() . "</p>";
    }

    // Limpiar sesión
    session_unset();
    session_destroy();
} else {
    echo "<p>Token de pago no proporcionado.</p>";
}
?>
