<?php
session_start();
require_once 'conexion.php';

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'coach') {
    header('Location: login.php');
    exit();
}

header('Content-Type: application/json');
$response = array('alert_type' => 'error', 'alert_message' => 'Ha ocurrido un error desconocido');

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_miembro = $_POST['id_miembro'];
        $id_clase = $_POST['id_clase'];
        $id_wod = $_POST['id_wod'] ?? null;
        $id_ejercicio = $_POST['id_ejercicio'];
        $id_wod_ejercicio = $_POST['id_wod_ejercicio'] ?? null; // Recupera el id_wod_ejercicio
        $id_claseskill = $_POST['id_clase_skill'] ?? null; // Recupera el id_claseskill
        $tipo = $_POST['tipo'];
        $tiempo = $_POST['tiempo'];
        $rondas = $_POST['rondas'];
        $repeticiones = $_POST['repeticiones'];
        $peso_usado = $_POST['peso_usado'];

        if ($tipo === 'wod') {
              // Verificar si los datos ya existen en Progreso_WOD
                $query_check_progreso = $conn->prepare("SELECT COUNT(*) FROM Progreso_WOD WHERE id_miembro = ? AND id_clase = ? AND id_wod = ? AND id_wodejercicio = ?");
                $query_check_progreso->bind_param("iiii", $id_miembro, $id_clase, $id_wod, $id_wod_ejercicio);
                $query_check_progreso->execute();
                $query_check_progreso->bind_result($count);
                $query_check_progreso->fetch();
                $query_check_progreso->free_result();


                if ($count > 0) {
                    throw new Exception('Los datos ya han sido insertados anteriormente');
                }


                $query_progreso = $conn->prepare("INSERT INTO Progreso_WOD (id_miembro, id_clase, id_wod, id_wodejercicio, id_ejercicio, tiempo, rondas, repeticiones, peso_usado) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $query_progreso->bind_param("iiiiisiii", $id_miembro, $id_clase, $id_wod, $id_wod_ejercicio, $id_ejercicio, $tiempo, $rondas, $repeticiones, $peso_usado);
            

        } else {
            $query_check_progreso = $conn->prepare("SELECT COUNT(*) FROM Progreso_Skill WHERE id_miembro = ? AND id_clase = ? AND id_ejercicio = ? AND id_claseskill = ?");
            $query_check_progreso->bind_param("iiii", $id_miembro, $id_clase, $id_ejercicio, $id_claseskill);
            $query_check_progreso->execute();
            $query_check_progreso->bind_result($count);
            $query_check_progreso->fetch();
            $query_check_progreso->free_result();

            if ($count > 0) {
                throw new Exception('Los datos ya han sido insertados anteriormente');
            }
            $query_progreso = $conn->prepare("INSERT INTO Progreso_Skill (id_miembro, id_clase, id_ejercicio, tiempo, rondas, repeticiones, peso_usado, id_claseskill) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $query_progreso->bind_param("iiisiiii", $id_miembro, $id_clase, $id_ejercicio, $tiempo, $rondas, $repeticiones, $peso_usado, $id_claseskill);
        }

        if ($query_progreso->execute()) {
            $response['alert_message'] = 'Progreso registrado con éxito';
            $response['alert_type'] = 'success';
        } else {
            throw new Exception('Error: ' . $query_progreso->error);
        }
    }
} catch (Exception $e) {
    $response['alert_message'] = $e->getMessage();
}

echo json_encode($response);
?>
