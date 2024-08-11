<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Guardar los datos del formulario en la sesión
    $_SESSION['form_data'] = $_POST;

    // Consultar coaches
    $coach_query = "SELECT id, nombre FROM Coach";
    $result_coach = $conn->query($coach_query);
    $coaches = array();
    if ($result_coach->num_rows > 0) {
        while($row = $result_coach->fetch_assoc()) {
            $coaches[] = $row;
        }
    }

    $conn->close();
}

// Definir horarios
$horarios = [
    '08:30:00', '18:00:00', '19:00:00', '20:00:00', '21:00:00', '22:00:00'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsualizar Clase</title>
    <link rel="stylesheet" href="Css/CC.css">
</head>
<body>
    <div class="container">
        <h2>Previsualización de la Clase</h2>
        <form action="crearclase.php" method="post">
            <div class="input-group">
                <div>
                    <label>Fecha:</label>
                    <span><?= htmlspecialchars($_SESSION['form_data']['fecha'] ?? 'No especificado') ?></span><br>
                </div>

                <?php foreach($horarios as $horario): ?>
                <div>
                    <label>Horario: <?= htmlspecialchars($horario) ?></label>
                    <select name="coach_horarios[<?= htmlspecialchars($horario) ?>]" required>
                        <?php foreach($coaches as $coach): ?>
                            <option value="<?= htmlspecialchars($coach['id']) ?>"><?= htmlspecialchars($coach['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (isset($_SESSION['form_data']['movimiento_articular'])): ?>
            <fieldset>
                <h2>Movimiento Articular</h2>
                <?php if (isset($_SESSION['form_data']['id_ejercicio_movimiento'])): ?>
                    <?php foreach($_SESSION['form_data']['id_ejercicio_movimiento'] as $index => $ejercicio): ?>
                        <div>
                            <label>Ejercicio:</label>
                            <span><?= htmlspecialchars($ejercicio) ?></span><br>
                            <label>Duración:</label>
                            <span><?= htmlspecialchars($_SESSION['form_data']['minutos_id_ejercicio_movimiento'][$index] . ' minutos ' . $_SESSION['form_data']['segundos_id_ejercicio_movimiento'][$index] . ' segundos') ?></span><br>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>
            <?php endif; ?>

            <?php if (isset($_SESSION['form_data']['skill'])): ?>
            <fieldset>
                <h2>Skill</h2>
                <?php if (isset($_SESSION['form_data']['id_ejercicio_skill'])): ?>
                    <?php foreach($_SESSION['form_data']['id_ejercicio_skill'] as $index => $ejercicio): ?>
                        <div>
                            <label>Ejercicio:</label>
                            <span><?= htmlspecialchars($ejercicio) ?></span><br>
                            <label>Duración:</label>
                            <span><?= htmlspecialchars($_SESSION['form_data']['minutos_id_ejercicio_skill'][$index] . ' minutos ' . $_SESSION['form_data']['segundos_id_ejercicio_skill'][$index] . ' segundos') ?></span><br>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>
            <?php endif; ?>

            <?php if (isset($_SESSION['form_data']['wod'])): ?>
            <fieldset>
                <h2>WOD</h2>
                <?php if (isset($_SESSION['form_data']['tipo_wod'])): ?>
                    <?php foreach($_SESSION['form_data']['tipo_wod'] as $index => $tipo_wod): ?>
                        <div>
                            <label>Tipo WOD:</label>
                            <span><?= htmlspecialchars($tipo_wod) ?></span><br>
                            <label>Duración:</label>
                            <span><?= htmlspecialchars($_SESSION['form_data']['minutos_tipo_wod'][$index] . ' minutos ' . $_SESSION['form_data']['segundos_tipo_wod'][$index] . ' segundos') ?></span><br>
                            <h3>Ejercicios</h3>
                            <?php if (isset($_SESSION['form_data']['id_ejercicio_wod'][$index])): ?>
                                <?php foreach($_SESSION['form_data']['id_ejercicio_wod'][$index] as $ejercicio_index => $ejercicio): ?>
                                    <div>
                                        <label>Ejercicio:</label>
                                        <span><?= htmlspecialchars($ejercicio) ?></span><br>
                                        <label>Repeticiones:</label>
                                        <span><?= htmlspecialchars($_SESSION['form_data']['repeticiones_wod'][$index][$ejercicio_index]) ?></span><br>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>
            <?php endif; ?>

            <button type="submit">Crear Clase</button>
        </form>
    </div>
</body>
</html>
