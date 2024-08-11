<?php
require 'vendor/autoload.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uruz";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión ha fallado: " . $conn->connect_error);
} 

// Configuración de Transbank para pruebas
use Transbank\Webpay\WebpayPlus;

WebpayPlus::configureForIntegration(
    '597055555532',  // Código de comercio para pruebas
    '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C'  // Llave privada para pruebas
);
?>
