<?php

session_start();

include_once __DIR__ . '/../conexion/conexion.php';

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

// Obtener los usuarios
$idEmisor = (int) $_SESSION['user_id'];
$idReceptor = $_GET['idUsuario'] ?? '';

function volverConMensaje(string $mensaje)
{
    $_SESSION['mensaje_solicitud'] = $mensaje;
    header('Location: ../views/busqueda.php');
    exit();
}

// Comprobar que el ID sea válido
if (filter_var($idReceptor, FILTER_VALIDATE_INT) === false || $idReceptor <= 0) {
    volverConMensaje('El usuario no es válido.');
}

// No permitir enviarse una solicitud a uno mismo
if ($idEmisor == $idReceptor) {
    volverConMensaje('No puedes enviarte una solicitud a ti mismo.');
}

try {

    // Comprobar si existe el usuario receptor
    $sql = "SELECT id FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $idReceptor]);

    if (!$stmt->fetch()) {
        volverConMensaje('El usuario no existe.');
    }

    // Comprobar si ya existe una amistad o solicitud
    $sql = "SELECT id, fk_amistad_emisor, fk_amistad_receptor, estado FROM amistad 
            WHERE (fk_amistad_emisor = :emisor AND fk_amistad_receptor = :receptor) OR (fk_amistad_emisor = :receptor AND fk_amistad_receptor = :emisor)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':emisor' => $idEmisor,
        ':receptor' => $idReceptor
    ]);

    $amistad = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($amistad) {
        if ($amistad['estado'] == 'aceptado') {
            volverConMensaje('Estos usuarios ya son amigos.');
        }

        if ($amistad['estado'] == 'pendiente') {
            volverConMensaje('Ya existe una solicitud pendiente.');
        }
        
        // Si la solicitud fue rechazada, se puede volver a enviar
        if ($amistad['estado'] == 'rechazado') {
            $sql = "UPDATE amistad SET fk_amistad_emisor = :emisor, fk_amistad_receptor = :receptor, estado = 'pendiente', creado = CURRENT_TIMESTAMP
                    WHERE id = :id AND estado = 'rechazado'";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':emisor' => $idEmisor,
                ':receptor' => $idReceptor,
                ':id' => $amistad['id']
            ]);

            volverConMensaje('Solicitud enviada correctamente.');
        }
    }

    // Crear la solicitud de amistad
    $sql = "INSERT INTO amistad (fk_amistad_emisor, fk_amistad_receptor, estado)
            VALUES (:emisor, :receptor, 'pendiente')";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':emisor' => $idEmisor,
        ':receptor' => $idReceptor
    ]);

    volverConMensaje('Solicitud enviada correctamente.');

} catch (PDOException $e) {
    volverConMensaje('Error al enviar la solicitud.');
}

?>
