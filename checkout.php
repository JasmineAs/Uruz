<?php

require 'vendor/autoload.php';

use Transbank\Webpay\WebpayPlus;

session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $_SESSION['name'] = $_POST['name'] ?? '';
    $_SESSION['email'] = $_POST['email'] ?? '';
    $_SESSION['phone'] = $_POST['phone'] ?? '';
    $_SESSION['password'] = $_POST['password'] ?? '';

    // Procesa el archivo de la foto
    $uploadDir = 'uploads/';
    $uploadFile = 'default.png';  // Imagen por defecto

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadFile = $uploadDir . basename($_FILES['photo']['name']);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
            echo "<p>Error al subir la foto.</p>";
            exit();
        }
    } else {
        // Si no hay foto cargada, se usa la imagen por defecto
        $uploadFile = 'uploads/default.png';  // Asegúrate de que este archivo exista en el directorio 'uploads'
    }

    $_SESSION['photo'] = $uploadFile;

    // Guardar el plan_id en la sesión
    $_SESSION['plan_id'] = $_POST['plan'] ?? null; // Aquí $_POST['plan'] contiene el ID del plan

    // Obtener el valor del plan seleccionado
    $planId = $_SESSION['plan_id'];
    $stmt = $conn->prepare("SELECT valor FROM tipoplan WHERE id = ?");
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $stmt->bind_result($amount);
    $stmt->fetch();
    $stmt->close();

    if ($amount === null) {
        echo "<p>Error: No se pudo encontrar el valor para el plan seleccionado.</p>";
        exit();
    }

    try {
        // Crear una nueva transacción
        $transaction = new WebpayPlus\Transaction();
        $buyOrder = uniqid();  // Orden de compra única para cada transacción
        $sessionId = session_id();
        $returnUrl = 'http://localhost/Uruz/return_url.php'; // Actualiza esta URL si es necesario

        $response = $transaction->create($buyOrder, $sessionId, $amount, $returnUrl);

        // Guardar los datos de la transacción en la sesión
        $_SESSION['transaction'] = [
            'buyOrder' => $buyOrder,
            'amount' => $amount,
            'status' => 'Pending'
        ];

        // Redirigir al usuario a la URL de Transbank para completar el pago
        header('Location: ' . $response->getUrl() . '?token_ws=' . $response->getToken());
        exit();
    } catch (Exception $e) {
        echo "<p>Error al crear la transacción: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Formulario no enviado correctamente.</p>";
}
?>
