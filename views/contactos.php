<?php

session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit();
}

include_once __DIR__ . '/../conexion/conexion.php';

$idUsuario = (int) $_SESSION['user_id'];

// Obtiene las solicitudes pendientes que ha recibido el usuario.
$stmt = $conn->prepare(
    "SELECT a.id, u.nombre, u.username, a.creado FROM amistad a INNER JOIN usuarios u ON u.id = a.fk_amistad_emisor 
    WHERE a.fk_amistad_receptor = :id AND a.estado = 'pendiente' ORDER BY a.creado DESC"
);
$stmt->execute([':id' => $idUsuario]);
$solicitudesRecibidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtiene las solicitudes pendientes que ha enviado el usuario.
$stmt = $conn->prepare(
    "SELECT a.id, u.nombre, u.username, a.creado FROM amistad a INNER JOIN usuarios u ON u.id = a.fk_amistad_receptor
    WHERE a.fk_amistad_emisor = :id AND a.estado = 'pendiente' ORDER BY a.creado DESC"
);
$stmt->execute([':id' => $idUsuario]);
$solicitudesEnviadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtiene los amigos del usuario.
$stmt = $conn->prepare(
    "SELECT u.nombre, u.username, a.creado FROM amistad a INNER JOIN usuarios u ON u.id = IF(a.fk_amistad_emisor = :id_amigo_emisor, a.fk_amistad_receptor, a.fk_amistad_emisor)
    WHERE (a.fk_amistad_emisor = :id_filtro_emisor OR a.fk_amistad_receptor = :id_filtro_receptor) AND a.estado = 'aceptado' ORDER BY u.nombre"
);

$stmt->execute([
    ':id_amigo_emisor' => $idUsuario,
    ':id_filtro_emisor' => $idUsuario,
    ':id_filtro_receptor' => $idUsuario,
]);
$amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// Muestra la lista de personas según el tipo (recibidas, enviadas o amigos).
function mostrarPersonas(array $personas, string $tipo): void
{
    // Si no hay personas que mostrar, se muestra un mensaje según el tipo.
    if (!$personas) {
        $mensajes = [
            'recibidas' => 'No tienes solicitudes recibidas.',
            'enviadas' => 'No tienes solicitudes enviadas.',
            'amigos' => 'Todavía no tienes amigos.',
        ];
        echo '<p class="sin-resultados">' . escapar($mensajes[$tipo]) . '</p>';
        return;
    }

    // Muestra la lista de personas con sus acciones según el tipo.
    echo '<ul class="lista-personas">';
    foreach ($personas as $persona) {
        echo '<li class="persona">';
        echo '<div class="datos-persona">';
        echo '<strong>' . escapar($persona['nombre']) . '</strong>';
        echo '<span>@' . escapar($persona['username']) . '</span>';
        echo '</div>';

        // Muestra los botones de acción según el tipo de persona (recibida, enviada o amigo).
        if ($tipo === 'recibidas') {
            echo '<div class="acciones-solicitud">';
            echo '<a class="boton boton-principal" href="../proc/proc_aceptar_solicitud.php?idSolicitud='
                . (int) $persona['id'] . '">Aceptar</a>';
            echo '<a class="boton boton-secundario" href="../proc/proc_rechazar_solicitud.php?idSolicitud='
                . (int) $persona['id'] . '">Rechazar</a>';
            echo '</div>';
        } elseif ($tipo === 'enviadas') {
            echo '<span class="etiqueta-estado">Pendiente</span>';
        } else {
            echo '<span class="etiqueta-estado estado-amigo">Amigo</span>';
        }

        echo '</li>';
    }
    echo '</ul>';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes y amigos - Chat Online</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body class="pagina pagina-chat">
    <header class="cabecera cabecera-personas">
        <a class="marca" href="./chat.php"> <img class="marca-logo" src="../media/logo/LogoChat.png" alt="Logo de Chat Online"> Chat Online</a>
        <nav class="navegacion-personas" aria-label="Navegación principal">
            <a class="enlace-navegacion" href="./chat.php">Chats</a>
            <a class="enlace-navegacion" href="./busqueda.php">Buscar personas</a>
            <a class="enlace-navegacion activo" href="./contactos.php">Solicitudes y amigos</a>
        </nav>
        <span class="usuario-actual"><?= escapar($_SESSION['username'] ?? '') ?></span>
    </header>

    <main class="contenido-personas">
        <section class="panel-personas panel-contactos" aria-labelledby="titulo-contactos">
            <p class="etiqueta">Tu red</p>
            <h1 id="titulo-contactos">Solicitudes y amigos</h1>
            <p class="texto-ayuda">Consulta tus solicitudes.</p>

            <!-- Sección de solicitudes recibidas -->
            <section class="seccion-contactos" aria-labelledby="titulo-recibidas">
                <h2 class="titulo-resultados" id="titulo-recibidas">Solicitudes recibidas</h2>
                <?php mostrarPersonas($solicitudesRecibidas, 'recibidas'); ?>
            </section>

            <!-- Sección de solicitudes enviadas -->
            <section class="seccion-contactos" aria-labelledby="titulo-enviadas">
                <h2 class="titulo-resultados" id="titulo-enviadas">Solicitudes enviadas</h2>
                <?php mostrarPersonas($solicitudesEnviadas, 'enviadas'); ?>
            </section>

            <!-- Sección de amigos -->
            <section class="seccion-contactos" aria-labelledby="titulo-amigos">
                <h2 class="titulo-resultados" id="titulo-amigos">Mis amigos</h2>
                <?php mostrarPersonas($amigos, 'amigos'); ?>
            </section>
        </section>
    </main>
</body>
</html>
