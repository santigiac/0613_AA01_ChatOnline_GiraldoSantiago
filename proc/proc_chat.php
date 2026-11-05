<?php

// Busca el identificador de una amistad aceptada entre dos usuarios.
function obtenerIdAmistad(PDO $conn, int $idUsuario, int $idAmigo): ?int
{
    // Comprueba las dos posibles direcciones de la amistad.
    $stmt = $conn->prepare(
        "SELECT id FROM amistad WHERE estado = 'aceptado'
            AND ((fk_amistad_emisor = :emisor_uno AND fk_amistad_receptor = :receptor_uno)
            OR (fk_amistad_emisor = :receptor_dos AND fk_amistad_receptor = :emisor_dos))
        LIMIT 1");

    $stmt->execute([
        
        ':emisor_uno' => $idUsuario,
        ':receptor_uno' => $idAmigo,
        ':receptor_dos' => $idAmigo,
        ':emisor_dos' => $idUsuario,

    ]);

    // Obtiene la amistad encontrada, si existe.
    $amistad = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devuelve el ID de la amistad o null si no son amigos.
    return $amistad ? (int) $amistad['id'] : null;
}

// Recupera todos los mensajes asociados a una amistad.
function obtenerMensajes(PDO $conn, int $idAmistad): array
{
    // Ordena los mensajes del más antiguo al más reciente.
    $stmt = $conn->prepare(
        'SELECT m.mensaje, m.imagen, m.fk_mensajes_emisor, m.enviado, u.nombre AS emisor_nombre, u.username AS emisor_username
        FROM mensajes m INNER JOIN usuarios u ON u.id = m.fk_mensajes_emisor WHERE m.fk_mensajes_amistad = :amistad
        ORDER BY m.enviado ASC, m.id ASC'
    );
    
    $stmt->execute([':amistad' => $idAmistad]);

    // Devuelve los mensajes en un array para mostrarlos en la vista.
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
