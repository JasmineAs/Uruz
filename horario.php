<?php
session_start();
require_once 'conexion.php';

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Obtener el rol del usuario
$user_role = $_SESSION['user_role'];

// Obtener reservas del miembro
$reservas_query = "";
if ($user_role === 'member') {
    $id_miembro = $_SESSION['user']['id']; // Obtener el id del miembro desde la sesión
    $reservas_query = "SELECT r.id, c.fecha, c.hora, co.nombre AS coach, c.movimiento_articular, c.skill, c.wod
                       FROM Reserva r
                       JOIN Clases c ON r.id_clase = c.id
                       JOIN Coach co ON c.id_coach = co.id
                       WHERE r.id_miembro = '$id_miembro'";
    $reservas_result = $conn->query($reservas_query);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Horario</title>
    <link rel="stylesheet" href="Css/horario.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Menú</h2>
        <ul>
            <li><a href="perfilavance.php">Perfil</a></li>
            <?php if ($user_role === 'coach'): ?>
                <li><a href="buscar_clase.php">Clases</a></li>
                <li><a href="Crearclases.php">Crear Clase</a></li>
            <?php endif; ?>
            <?php if ($user_role === 'member'): ?>
                <li><a href="buscar_clase.php">Buscar Clase</a></li>
            <?php endif; ?>
            <li><a href="perfil.php">Editar perfil</a></li>
            <li><a href="login.php">Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Mi Horario</h1>
        <div class="reservas-container">
            <?php
            if (isset($reservas_result) && $reservas_result->num_rows > 0) {
                while ($row = $reservas_result->fetch_assoc()) {
                    echo '<div class="reserva">';
                    echo "<h2>Clase</h2>";
                    echo "Fecha: " . $row['fecha'] . "<br>";
                    echo "Hora: " . $row['hora'] . "<br>";
                    echo "Coach: " . $row['coach'] . "<br>";

                    // Mostrar ejercicios de Movimiento Articular
                    if ($row['movimiento_articular']) {
                        $mov_query = "SELECT e.nombre, cma.duracion_mov 
                                      FROM Clase_Movimiento_Articular cma
                                      JOIN Ejercicio e ON cma.id_ejercicio = e.id
                                      WHERE cma.id_clase = " . $row['id'];
                        $mov_result = $conn->query($mov_query);

                        if ($mov_result->num_rows > 0) {
                            echo "<h3>Movimiento Articular</h3>";
                            while ($mov_row = $mov_result->fetch_assoc()) {
                                echo "Ejercicio: " . $mov_row['nombre'] . ", Duración: " . $mov_row['duracion_mov'] . "<br>";
                            }
                        }
                    }

                    // Mostrar ejercicios de Skill
                    if ($row['skill']) {
                        $skill_query = "SELECT e.nombre, cs.duracion_skill 
                                        FROM Clase_Skill cs
                                        JOIN Ejercicio e ON cs.id_ejercicio = e.id
                                        WHERE cs.id_clase = " . $row['id'];
                        $skill_result = $conn->query($skill_query);

                        if ($skill_result->num_rows > 0) {
                            echo "<h3>Skill</h3>";
                            while ($skill_row = $skill_result->fetch_assoc()) {
                                echo "Ejercicio: " . $skill_row['nombre'] . ", Duración: " . $skill_row['duracion_skill'] . "<br>";
                            }
                        }
                    }

                    // Mostrar WODs y sus ejercicios
                    if ($row['wod']) {
                        $wod_query = "SELECT cw.id, w.tipo, cw.duracion_wod 
                                      FROM Clase_WOD cw
                                      JOIN wods w ON cw.tipo_wod = w.id
                                      WHERE cw.id_clase = " . $row['id'];
                        $wod_result = $conn->query($wod_query);

                        if ($wod_result->num_rows > 0) {
                            echo "<h3>WOD</h3>";
                            while ($wod_row = $wod_result->fetch_assoc()) {
                                echo "<h4> Tipo WOD: " . $wod_row['tipo'] . ", Duración: " . $wod_row['duracion_wod'] . "<h4>";

                                // Mostrar ejercicios del WOD
                                $wod_ex_query = "SELECT e.nombre, we.repeticiones 
                                                 FROM wod_ejercicio we
                                                 JOIN Ejercicio e ON we.id_ejercicio = e.id
                                                 WHERE we.id_Clasewod = " . $wod_row['id'];
                                $wod_ex_result = $conn->query($wod_ex_query);

                                if ($wod_ex_result->num_rows > 0) {
                                    echo "<h4>Ejercicios del WOD</h4>";
                                    while ($wod_ex_row = $wod_ex_result->fetch_assoc()) {
                                        echo "Ejercicio: " . $wod_ex_row['nombre'] . ", Repeticiones: " . $wod_ex_row['repeticiones'] . "<br>";
                                    }
                                }
                            }
                        }
                    }
                    echo '</div>';
                }
            } else {
                echo "<p>No tienes clases agendadas.</p>";
            }

            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>

