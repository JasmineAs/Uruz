<?php
require_once 'conexion.php'; // Asegúrate de que esta ruta sea la correcta para la conexión a la base de datos

// Iniciar sesión y conexión a la base de datos
session_start();

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

        // Enviar mensaje a través de WhatsApp Business API
        $accessToken = 'EAAV2wjcX2KgBOwPGyJD3cZB7A6DPQnEQGbCzCj90EsjfuFAquQOLNUwuFswVuM5YYsw0WBSWbuElNHRJWsxLehXtcnPLRsZBIeYn3YK885QXMRpwTDO1kunPH0RZCMLJSbQfmlazG03TtAWaRU9lfsZAwpeW82prkC3HOi0V0ZAtLRNbC0MAJnvTkpuITKvLCvgkH01VGIinjNt4z7OgZD'; // Reemplaza con tu Access Token
        $phoneNumberId = '418480558010087'; // Reemplaza con tu ID de número de teléfono
        $url = "https://graph.facebook.com/v20.0/$phoneNumberId/messages";

        $data = array(
            'messaging_product' => 'whatsapp',
            'to' => $telefono,
            'text' => array(
                'body' => $mensaje
            ),
        );

        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n" .
                             "Authorization: Bearer $accessToken\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ),
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            die('Error al enviar el mensaje');
        }

        echo "Mensaje enviado a " . $telefono;
    } else {
        die("Error: Número de teléfono o mensaje vacío.");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensaje a Miembro</title>
</head>
<body>
    <h1>Enviar Mensaje a Miembro</h1>

    <form method="post" action="">
        <label for="miembro_id">Seleccionar Miembro:</label>
        <select name="miembro_id" id="miembro_id" required>
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>">
                    <?php echo $row['nombre']; ?> - <?php echo $row['telefono']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label for="mensaje">Mensaje:</label>
        <textarea name="mensaje" id="mensaje" rows="4" cols="50" required></textarea>

        <br><br>

        <button type="submit">Enviar Mensaje</button>
    </form>
</body>
</html>