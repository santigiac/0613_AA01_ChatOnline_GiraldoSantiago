
<?php

    session_start();

    include_once __DIR__ . '/../conexion/conexion.php';

    // Comprobar si el usuario ha iniciado sesión
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../views/login.php');
        exit();
    }

    // Obtener los datos
    $idReceptor = (int) $_SESSION['user_id'];
    $idSolicitud = $_GET['idSolicitud'] ?? '';

    // Comprobar que el ID sea un número
    if (!is_numeric($idSolicitud)) {
        die("La solicitud no es válida.");
    }

    try {

        // Aceptar la solicitud de amistad
        $sql = "UPDATE amistad SET estado = 'aceptado'
                WHERE id = :id AND fk_amistad_receptor = :receptor AND estado = 'pendiente'";
        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':id' => $idSolicitud,
            ':receptor' => $idReceptor
        ]);

        // Comprobar si se ha actualizado una solicitud
        if ($stmt->rowCount() == 1) {
            header('Location: ../views/contactos.php');
            exit();
        } else {
            echo "No se puede aceptar esta solicitud.";
        }

    } catch (PDOException $e) {
        echo "Error al aceptar la solicitud.";
    }

?>
