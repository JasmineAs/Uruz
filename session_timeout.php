<?php

// Define el tiempo máximo de inactividad
$inactive = 10; // 15 minutos en segundos

// Verifica si hay una sesión activa
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header("Location: login.php"); // Redirige al usuario a la página de inicio de sesión
        exit();
    }
}

// Actualiza el tiempo de última actividad
$_SESSION['last_activity'] = time();
?>
