<?php

function buscarPersonas(PDO $conn, int $idUsuario, string $busqueda): array
{
    // Limitar la longitud de la búsqueda a 100 caracteres
    $busqueda = trim(mb_substr($busqueda, 0, 100));
    // Consulta SQL para buscar personas que no sean el usuario actual y que no tengan una amistad aceptada con el
    $sql = "SELECT u.id, u.nombre, u.username,
                CASE WHEN EXISTS (

                    SELECT 1 FROM amistad pendiente WHERE pendiente.estado = 'pendiente'
                        AND ((pendiente.fk_amistad_emisor = :pendiente_emisor AND pendiente.fk_amistad_receptor = u.id) 
                        OR (pendiente.fk_amistad_receptor = :pendiente_receptor AND pendiente.fk_amistad_emisor = u.id))

                ) THEN 'pendiente' ELSE NULL END AS estado_amistad

            FROM usuarios u WHERE u.id <> :id
                AND NOT EXISTS ( 

                    SELECT 1 FROM amistad a WHERE a.estado = 'aceptado'
                    AND ((a.fk_amistad_emisor = :usuario_emisor AND a.fk_amistad_receptor = u.id)
                    OR (a.fk_amistad_receptor = :usuario_receptor AND a.fk_amistad_emisor = u.id))

            )";

    $parametros = [

        ':id' => $idUsuario,
        ':usuario_emisor' => $idUsuario,
        ':usuario_receptor' => $idUsuario,
        ':pendiente_emisor' => $idUsuario,
        ':pendiente_receptor' => $idUsuario,

    ];
    // Agregar condición de búsqueda si se proporciona un término de búsqueda
    if ($busqueda !== '') {

        $sql .= ' AND u.nombre LIKE :nombre';
        $parametros[':nombre'] = '%' . $busqueda . '%';
        
    }
    // Ordenar los resultados por nombre
    $sql .= ' ORDER BY u.nombre';
    $stmt = $conn->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
