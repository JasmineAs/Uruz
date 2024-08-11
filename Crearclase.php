<?php
include 'conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario de previsualización
    $fecha = $_SESSION['form_data']['fecha'];
    $coach_horarios = $_POST['coach_horarios'];
    $movimiento_articular = isset($_SESSION['form_data']['movimiento_articular']) ? 1 : 0;
    $skill = isset($_SESSION['form_data']['skill']) ? 1 : 0;
    $wod = isset($_SESSION['form_data']['wod']) ? 1 : 0;

    // Horarios de clases
    $horarios = array_keys($coach_horarios);

    foreach ($horarios as $hora) {
        $id_coach = $coach_horarios[$hora];

        // Insertar clase
        $sql = "INSERT INTO Clases (fecha, hora, id_coach, movimiento_articular, Skill, WOD)
                VALUES ('$fecha', '$hora', '$id_coach', '$movimiento_articular', '$skill', '$wod')";

        if ($conn->query($sql) === TRUE) {
            $id_clase = $conn->insert_id;
            echo "Clase creada exitosamente. ID de clase: " . $id_clase . "<br>";

            // Insertar Movimiento Articular si está marcado
            if ($movimiento_articular && !empty($_SESSION['form_data']['id_ejercicio_movimiento']) && !empty($_SESSION['form_data']['minutos_id_ejercicio_movimiento']) && !empty($_SESSION['form_data']['segundos_id_ejercicio_movimiento'])) {
                foreach ($_SESSION['form_data']['id_ejercicio_movimiento'] as $index => $id_ejercicio_mov) {
                    $minutos = $_SESSION['form_data']['minutos_id_ejercicio_movimiento'][$index];
                    $segundos = $_SESSION['form_data']['segundos_id_ejercicio_movimiento'][$index];
                    $duracion_mov = sprintf("%02d:%02d:00", $minutos, $segundos);
                    $sql_mov = "INSERT INTO Clase_Movimiento_Articular (id_clase, id_ejercicio, duracion_mov)
                                VALUES ('$id_clase', '$id_ejercicio_mov', '$duracion_mov')";
                    if ($conn->query($sql_mov) === TRUE) {
                        echo "Movimiento Articular agregado exitosamente.<br>";
                    } else {
                        echo "Error al agregar Movimiento Articular: " . $conn->error . "<br>";
                    }
                }
            }

            // Insertar Skill si está marcado
            if ($skill && !empty($_SESSION['form_data']['id_ejercicio_skill']) && !empty($_SESSION['form_data']['minutos_id_ejercicio_skill']) && !empty($_SESSION['form_data']['segundos_id_ejercicio_skill'])) {
                foreach ($_SESSION['form_data']['id_ejercicio_skill'] as $index => $id_ejercicio_skill) {
                    $minutos = $_SESSION['form_data']['minutos_id_ejercicio_skill'][$index];
                    $segundos = $_SESSION['form_data']['segundos_id_ejercicio_skill'][$index];
                    $duracion_skill = sprintf("%02d:%02d:00", $minutos, $segundos);
                    $sql_skill = "INSERT INTO Clase_Skill (id_clase, id_ejercicio, duracion_skill)
                                  VALUES ('$id_clase', '$id_ejercicio_skill', '$duracion_skill')";
                    if ($conn->query($sql_skill) === TRUE) {
                        echo "Skill agregado exitosamente.<br>";
                    } else {
                        echo "Error al agregar Skill: " . $conn->error . "<br>";
                    }
                }
            }

            // Insertar WOD si está marcado
            if ($wod && !empty($_SESSION['form_data']['tipo_wod']) && !empty($_SESSION['form_data']['minutos_tipo_wod']) && !empty($_SESSION['form_data']['segundos_tipo_wod'])) {
                foreach ($_SESSION['form_data']['tipo_wod'] as $index => $tipo_wod) {
                    $minutos = $_SESSION['form_data']['minutos_tipo_wod'][$index];
                    $segundos = $_SESSION['form_data']['segundos_tipo_wod'][$index];
                    $duracion_wod = sprintf("%02d:%02d:00", $minutos, $segundos);
                    $sql_wod = "INSERT INTO Clase_WOD (id_clase, tipo_wod, duracion_wod)
                                VALUES ('$id_clase', '$tipo_wod', '$duracion_wod')";
                    if ($conn->query($sql_wod) === TRUE) {
                        $id_clase_wod = $conn->insert_id;
                        echo "WOD agregado exitosamente.<br>";

                        // Insertar ejercicios del WOD
                        if (!empty($_SESSION['form_data']['id_ejercicio_wod'][$index]) && !empty($_SESSION['form_data']['repeticiones_wod'][$index])) {
                            foreach ($_SESSION['form_data']['id_ejercicio_wod'][$index] as $wod_index => $id_ejercicio_wod) {
                                $repeticiones_wod = $_SESSION['form_data']['repeticiones_wod'][$index][$wod_index];
                                $sql_wod_ejercicio = "INSERT INTO wod_ejercicio (id_Clasewod, id_ejercicio, repeticiones)
                                                      VALUES ('$id_clase_wod', '$id_ejercicio_wod', '$repeticiones_wod')";
                                if ($conn->query($sql_wod_ejercicio) === TRUE) {
                                    echo "Ejercicio del WOD agregado exitosamente.<br>";
                                } else {
                                    echo "Error al agregar ejercicio del WOD: " . $conn->error . "<br>";
                                }
                            }
                        }
                    } else {
                        echo "Error al agregar WOD: " . $conn->error . "<br>";
                    }
                }
            }
        } else {
            echo "Error al crear la clase: " . $conn->error . "<br>";
        }
    }

    // Limpiar datos de la sesión
    unset($_SESSION['form_data']);
}   
header("Location: crearclases.php");
exit(); // Asegurarse de detener la ejecución del script después de redirigir
?>
