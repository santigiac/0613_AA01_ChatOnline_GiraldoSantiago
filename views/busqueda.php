<?php

session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit();
}

// Obtener el mensaje de la sesión, si existe
$mensaje = $_SESSION['mensaje_solicitud'] ?? null;
unset($_SESSION['mensaje_solicitud']);

// Incluir la conexión a la base de datos y el proceso de búsqueda
include_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../proc/proc_busqueda_personas.php';

// Obtener los datos de búsqueda
$idUsuario = (int) $_SESSION['user_id'];
$busqueda = isset($_GET['nombre']) && is_string($_GET['nombre']) && strlen($_GET['nombre']) <= 10 ? trim($_GET['nombre']) : '';
$usuarios = buscarPersonas($conn, $idUsuario, $busqueda);

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personas - Chat Online</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body class="pagina pagina-chat">
    <header class="cabecera cabecera-personas">
        <a class="marca" href="./chat.php"> <img class="marca-logo" src="../media/logo/LogoChat.png" alt="Logo de Chat Online"> Chat Online</a>
        <nav class="navegacion-personas" aria-label="Navegación principal">
            <a class="enlace-navegacion" href="./chat.php">Chats</a>
            <a class="enlace-navegacion activo" href="./busqueda.php">Buscar personas</a>
            <a class="enlace-navegacion" href="./contactos.php">Solicitudes y amigos</a>
        </nav>
        <span class="usuario-actual"><?= escapar($_SESSION['username'] ?? '') ?></span>
    </header>

    <main class="contenido-personas">
        <section class="panel-personas" aria-labelledby="titulo-personas">
            <p class="etiqueta">Conecta con otras personas</p>
            <h1 id="titulo-personas">Buscar personas</h1>
            <p class="texto-ayuda">Escribe un nombre para encontrar personas y enviarles una solicitud.</p>

            <!-- Mensaje de aviso -->
            <?php if ($mensaje): ?>
                <p class="aviso-solicitud" role="alert">
                    <?= escapar($mensaje) ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de búsqueda -->
            <form class="formulario-busqueda-personas" action="./busqueda.php" method="get">
                <label for="nombre">Nombre de la persona</label>
                <div class="controles-busqueda-personas">
                    <input type="search" id="nombre" name="nombre" value="<?= escapar($busqueda) ?>" placeholder="Por ejemplo, Ana">
                    <button class="boton boton-principal" type="submit">Buscar</button>
                </div>
            </form>

            <h2 class="titulo-resultados">
                <?= $busqueda === '' ? 'Personas' : 'Resultados de búsqueda' ?>
            </h2>

            <!-- Lista de resultados -->
            <?php if ($usuarios): ?>
                <ul class="lista-personas">
                    <?php foreach ($usuarios as $usuario): ?>
                        <li class="persona">
                            <div class="datos-persona">
                                <strong><?= escapar($usuario['nombre']) ?></strong>
                                <span>@<?= escapar($usuario['username']) ?></span>
                            </div>
                            <?php if ($usuario['estado_amistad'] === 'pendiente'): ?>
                                <span class="boton boton-solicitud-enviada">
                                    Solicitud enviada
                                </span>
                            <?php else: ?>
                                <a class="boton boton-secundario" href="../proc/proc_enviar_solicitud.php?idUsuario=<?= (int) $usuario['id'] ?>">Enviar solicitud</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="sin-resultados">
                    <?= $busqueda === '' ? 'Todavía no hay otras personas registradas.' : 'No se encontraron personas con ese nombre.' ?>
                </p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
