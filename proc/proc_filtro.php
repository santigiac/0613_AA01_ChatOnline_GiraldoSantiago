<?php

function obtenerContactosAceptados(PDO $conn, int $idUsuario, string $busqueda): array
{
    $stmt = $conn->prepare(
        "SELECT DISTINCT u.id, u.nombre, u.username FROM usuarios u
        INNER JOIN amistad a 
            ON ((a.fk_amistad_emisor = :emisor AND a.fk_amistad_receptor = u.id)
            OR (a.fk_amistad_receptor = :receptor AND a.fk_amistad_emisor = u.id)
        )
        WHERE a.estado = 'aceptado'
            AND (:busqueda = '' OR u.nombre LIKE :nombre_filtro OR u.username LIKE :username_filtro)
        ORDER BY u.nombre, u.username"
    );
    $stmt->execute([
        ':emisor' => $idUsuario,
        ':receptor' => $idUsuario,
        ':busqueda' => $busqueda,
        ':nombre_filtro' => '%' . $busqueda . '%',
        ':username_filtro' => '%' . $busqueda . '%',
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
