
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

// Comprobar que el ID sea un número entero válido
if (filter_var($idSolicitud, FILTER_VALIDATE_INT) === false || $idSolicitud <= 0) {
    die("La solicitud no es válida.");
}

try {

    // Rechazar la solicitud de amistad
    $sql = "UPDATE amistad SET estado = 'rechazado'
            WHERE id = :id AND fk_amistad_receptor = :receptor AND estado = 'pendiente'";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':id' => $idSolicitud,
        ':receptor' => $idReceptor
    ]);

    // Comprobar si se ha rechazado
    if ($stmt->rowCount() == 1) {
        header('Location: ../views/contactos.php');
        exit();
    } else {
        echo "No se puede rechazar esta solicitud.";
    }

} catch (PDOException $e) {
    echo "Error al rechazar la solicitud.";
}

?>
