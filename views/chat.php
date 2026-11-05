<?php
	session_start();

	include_once __DIR__ . '/../conexion/conexion.php';
	// proc_filtro.php se carga para buscar los contactos con amistad aceptada.
	require_once __DIR__ . '/../proc/proc_filtro.php';
	// proc_chat.php se carga para comprobar amistades y recuperar mensajes.
	require_once __DIR__ . '/../proc/proc_chat.php';

	// Comprueba que solo los usuarios autenticados puedan acceder al chat.
	if (!isset($_SESSION['user_id']) || !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)) {
		header('Location: login.php');
		exit();
	}

	$idUsuario = (int) $_SESSION['user_id'];
	$busqueda = isset($_GET['buscar']) && is_string($_GET['buscar']) && strlen($_GET['buscar']) <= 100 ? trim($_GET['buscar']) : '';

	// Carga los datos del usuario que ha iniciado sesión.
	$stmt = $conn->prepare('SELECT id, nombre, username FROM usuarios WHERE id = :id');
	$stmt->execute([':id' => $idUsuario]);
	$usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$usuarioActual) {
		$_SESSION = [];
		session_destroy();
		header('Location: login.php');
		exit();
	}

	$_SESSION['username'] = $usuarioActual['username'];

	// Se ejecuta proc_filtro.php al abrir la página o enviar una búsqueda.
	$usuarios = obtenerContactosAceptados($conn, $idUsuario, $busqueda);

	// Lee el contacto seleccionado o utiliza el primero de la lista.
	$idAmigo = filter_input(INPUT_GET, 'usuario', FILTER_VALIDATE_INT);
	if (!$idAmigo && $usuarios) {
		$idAmigo = (int) $usuarios[0]['id'];
	}

	$amigo = null;
	foreach ($usuarios as $usuario) {
		if ((int) $usuario['id'] === (int) $idAmigo) {
			// Guarda los datos del contacto cuya conversación se va a mostrar.
			$amigo = $usuario;
			break;
		}
	}

	$mensajes = [];
	$idAmistad = null;

	if ($amigo) {
		// Se ejecuta proc_chat.php al seleccionar un contacto.
		// Primero comprueba la amistad y después recupera sus mensajes.
		$idAmistad = obtenerIdAmistad($conn, $idUsuario, (int) $amigo['id']);
		if ($idAmistad !== null) {
			$mensajes = obtenerMensajes($conn, $idAmistad);
		}
	}

	function escapar(string $valor): string
	{
		// Evita que los datos de la base de datos se interpreten como HTML.
		return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
	}

	function iniciales(string $nombre): string
	{
		// Genera las dos primeras letras para mostrar el avatar.
		return escapar(strtoupper(substr($nombre, 0, 2)));
	}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Online</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body class="pagina pagina-chat">
    <main class="chat-app">
        <aside class="barra-lateral" aria-label="Secciones del chat">
            <a class="marca marca-chat" href="./chat.php" aria-label="Ir al inicio">
                <img src="../media/logo/LogoChat.png" alt="Ir al inicio">
            </a>
            <nav class="navegacion-lateral">
                <a class="icono-lateral" href="contactos.php" aria-label="Contactos">👥</a>
            </nav>
            <div class="navegacion-inferior">
                <a class="avatar avatar-pequeno" aria-label="Perfil">
                    <?php echo iniciales($usuarioActual['username']); ?>
                </a>
				<!-- El enlace ejecuta proc_logout.php para cerrar la sesión. -->
				<a class="icono-lateral" href="../proc/proc_logout.php" aria-label="Cerrar sesión">⏻</a>
            </div>
        </aside>

        <section class="panel-chats" id="chats" aria-label="Lista de chats">
            <header class="cabecera-chats">
                <div>
                    <p class="etiqueta">Tu espacio</p>
                    <h1>Chats</h1>
                </div>
            </header>

            <!-- Ejecuta proc_filtro.php mediante GET al buscar un contacto. -->
            <form class="busqueda" method="get" action="chat.php">
                <span aria-hidden="true">⌕</span>
                <input type="search" id="buscar-chat" name="buscar" value="<?php echo escapar($busqueda); ?>" placeholder="Buscar o iniciar un chat" autocomplete="off">
                <?php if ($idAmigo): ?>
                    <input type="hidden" name="usuario" value="<?php echo (int) $idAmigo; ?>">
                <?php endif; ?>
            </form>

            <!-- Muestra la lista devuelta por proc_filtro.php. -->
            <div class="lista-conversaciones" id="lista-conversaciones">
                <?php foreach ($usuarios as $usuario): ?>
                    <a class="conversacion<?php echo ((int) $usuario['id'] === (int) $idAmigo) ? ' seleccionada' : ''; ?>"
                       href="chat.php?usuario=<?php echo (int) $usuario['id']; ?>">
                        <span class="avatar avatar-coral"><?php echo iniciales($usuario['username']); ?></span>
                        <span class="datos-conversacion">
                            <strong><?php echo escapar($usuario['nombre']); ?></strong>
                            <span>@<?php echo escapar($usuario['username']); ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
                <?php if (!$usuarios): ?>
                    <p>No tienes amigos para iniciar un chat.</p>
                <?php endif; ?>
                <?php if (!$usuarios && $busqueda !== ''): ?>
                    <p class="sin-resultados">No hay amigos que coincidan con la búsqueda.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Muestra la conversación seleccionada y el formulario para enviar mensajes. -->
        <section class="conversacion-activa" aria-label="Conversación">
            <?php if ($amigo): ?>
                <header class="cabecera-conversacion">
                    <span class="avatar avatar-coral"><?php echo iniciales($amigo['username']); ?></span>
                    <div class="contacto">
                        <h2><?php echo escapar($amigo['nombre']); ?></h2>
                        <p>@<?php echo escapar($amigo['username']); ?></p>
                    </div>
                </header>

                <div class="zona-mensajes">
                    <p class="fecha-mensajes">MENSAJES</p>
                    <?php foreach ($mensajes as $mensaje): ?>
                        <?php $enviado = (int) $mensaje['fk_mensajes_emisor'] === $idUsuario; ?>
                        <div class="mensaje <?php echo $enviado ? 'enviado' : 'recibido'; ?>">
                            <!-- mostrar nombre de emisor -->
                            <strong class="emisor-mensaje">
                                <?php echo escapar($mensaje['emisor_nombre']); ?>
                            </strong>
                            <?php if ($mensaje['mensaje'] !== ''): ?>
                                <p><?php echo escapar($mensaje['mensaje']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($mensaje['imagen'])): ?>
                                <a class="archivo-mensaje"
                                   href="../media/uploads/<?php echo escapar($mensaje['imagen']); ?>"
                                   target="_blank" rel="noopener">
                                    <img src="../media/uploads/<?php echo escapar($mensaje['imagen']); ?>"
                                         alt="Imagen adjunta">
                                </a>
                            <?php endif; ?>
                            <time><?php echo date('H:i', strtotime($mensaje['enviado'])); ?></time>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$mensajes): ?>
                        <p class="fecha-mensajes">Todavía no hay mensajes.</p>
                    <?php endif; ?>
                </div>

                <!-- Ejecuta proc_enviar_mensaje.php solo al enviar este formulario. -->
                <form class="compositor" method="post" action="../proc/proc_enviar_mensaje.php"
                      enctype="multipart/form-data">
                    <input type="hidden" name="usuario" value="<?php echo (int) $amigo['id']; ?>">
                    <?php if ($busqueda !== ''): ?>
                        <input type="hidden" name="buscar" value="<?php echo escapar($busqueda); ?>">
                    <?php endif; ?>
                    <input class="campo-mensaje" type="text" name="mensaje"
                           placeholder="Escribe un mensaje">
                    <label class="boton boton-adjunto" for="archivo">Adjuntar</label>
                    <input class="entrada-archivo" type="file" id="archivo" name="archivo"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <button class="boton boton-principal" type="submit">Enviar</button>
                </form>
            <?php else: ?>
                <div class="zona-mensajes">
                    <p class="fecha-mensajes">Selecciona un amigo para comenzar a chatear.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
