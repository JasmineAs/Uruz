<?php
require_once 'twilio-php/src/twilio/autoload.php'; // Asegúrate de que esta ruta sea la correcta para la autoload de Twilio
use Twilio\Rest\Client;

// Iniciar sesión y conexión a la base de datos
session_start();
require_once 'conexion.php';

// Obtener la lista de miembros
$result = $conn->query("SELECT id, nombre, telefono FROM miembros");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $miembro_id = $_POST['miembro_id'];
    $mensaje = $_POST['mensaje'];

    // Obtener el número de teléfono del miembro seleccionado
    $stmt = $conn->prepare("SELECT telefono FROM miembros WHERE id = ?");
    $stmt->bind_param("i", $miembro_id);
    $stmt->execute();
    $stmt->bind_result($telefono);
    $stmt->fetch();
    $stmt->close();

    // Validar los datos antes de enviar el mensaje
    if (!empty($telefono) && !empty($mensaje)) {

        // Asegúrate de que el número de teléfono empiece con '+' y esté en formato internacional
        if (!preg_match('/^\+\d{10,15}$/', $telefono)) {
            die("Error: Número de teléfono no tiene un formato internacional válido.");
        }

        // Enviar mensaje a través de Twilio
        $sid = 'ACfafe909e5137667b47cb9c34e829a209';
        $token = 'd626e18a1b782a5fe852e6b82a987c5c';
        $client = new Client($sid, $token);

        try {
            $message = $client->messages->create(
                "whatsapp:$telefono", // Número de teléfono del destinatario
                [
                    'from' => 'whatsapp:+14155238886',
                    'body' => $mensaje
                ]
            );
            echo "Mensaje enviado a " . $telefono;
        } catch (Exception $e) {
            die("Error al enviar el mensaje: " . $e->getMessage());
        }

    } else {
        die("Error: Número de teléfono o mensaje vacío.");
    }
}
?>
