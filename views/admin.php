<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit();
}

require_once __DIR__ . '/../conexion/conexion.php';

$stmt = $conn->prepare(
    'SELECT roles.nombre
     FROM usuarios
     INNER JOIN roles ON roles.id = usuarios.role_id
     WHERE usuarios.id = :user_id
     LIMIT 1'
);
$stmt->execute([':user_id' => (int) $_SESSION['user_id']]);
$role = $stmt->fetchColumn();

if ($role !== 'admin') {
    header('Location: ' . ($role === false ? './login.php' : './chat.php'));
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $conn->query(
    'SELECT usuarios.id, usuarios.nombre, usuarios.username, roles.nombre AS role_name
     FROM usuarios
     INNER JOIN roles ON roles.id = usuarios.role_id
     ORDER BY usuarios.id'
);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
$mensaje = $_GET['resultado'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Chat Online</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body class="pagina">
    <header class="cabecera admin-cabecera">
        <a class="marca" href="../index.php">
            <img class="marca-logo" src="../media/logo/LogoChat.png" alt="Logo de Chat Online">
            <span>Chat Online</span>
        </a>
        <a class="boton boton-secundario" href="../proc/proc_logout.php">Cerrar sesión</a>
    </header>

    <main class="contenido-principal admin-contenido">
        <section class="tarjeta admin-tarjeta">
            <p class="etiqueta">Administración</p>
            <h1>Usuarios</h1>
            <p class="texto-ayuda">Consulta las cuentas y elimina usuarios junto con sus amistades y conversaciones.</p>

            <?php if ($mensaje === 'eliminado'): ?>
                <p class="admin-aviso" role="status">El usuario y todos sus chats se han eliminado.</p>
            <?php elseif ($mensaje === 'actualizado'): ?>
                <p class="admin-aviso" role="status">Los datos del usuario se han actualizado.</p>
            <?php elseif ($mensaje === 'error'): ?>
                <p class="mensaje-error" role="alert">No se pudo eliminar el usuario. Inténtalo de nuevo.</p>
            <?php elseif ($mensaje === 'invalido'): ?>
                <p class="mensaje-error" role="alert">La solicitud no es válida.</p>
            <?php endif; ?>

            <div class="admin-tabla-contenedor">
                <table class="admin-tabla">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= (int) $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['username']) ?></td>
                                <td><?= htmlspecialchars($usuario['role_name']) ?></td>
                                <td>
                                    <a href="admin_editar_usuario.php?id=<?= (int) $usuario['id'] ?>">Editar</a>
                                    <?php if ((int) $usuario['id'] !== (int) $_SESSION['user_id']): ?>
                                        <form class="admin-accion" action="../proc/admin_eliminar_usuario.php" method="POST"
                                              onsubmit="return confirm('¿Eliminar este usuario y todos sus chats?');">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="user_id" value="<?= (int) $usuario['id'] ?>">
                                            <button type="submit">Eliminar</button>
                                        </form>
                                    <?php else: ?>
                                        <span>Tu cuenta</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
