<?php
session_start();
session_unset(); // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirigir al usuario a la página de inicio de sesión u otra página
header("Location: login.php");
exit();
?>