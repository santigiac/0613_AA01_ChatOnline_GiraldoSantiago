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

if ($stmt->fetchColumn() !== 'admin') {
    header('Location: ./chat.php');
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$targetUserId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($targetUserId === false || $targetUserId < 1) {
    header('Location: ./admin.php?resultado=invalido');
    exit();
}

$stmt = $conn->prepare(
    'SELECT usuarios.id, usuarios.nombre, usuarios.username, usuarios.role_id, roles.nombre AS role_name
     FROM usuarios
     INNER JOIN roles ON roles.id = usuarios.role_id
     WHERE usuarios.id = :user_id
     LIMIT 1'
);
$stmt->execute([':user_id' => $targetUserId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: ./admin.php?resultado=invalido');
    exit();
}

$roles = $conn->query('SELECT id, nombre FROM roles ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
$errores = $_SESSION['admin_edit_errors'] ?? [];
$datos = $_SESSION['admin_edit_data'] ?? [];
unset($_SESSION['admin_edit_errors'], $_SESSION['admin_edit_data']);

if (isset($datos['user_id']) && (int) $datos['user_id'] === $targetUserId) {
    $usuario['nombre'] = $datos['nombre'];
    $usuario['username'] = $datos['username'];
    $usuario['role_id'] = $datos['role_id'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario - Chat Online</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body class="admin-editar">
    <header>
        <a href="../index.php">
            <img src="../media/logo/LogoChat.png" alt="Logo de Chat Online" width="32">
            <span>Chat Online</span>
        </a>
        <a href="admin.php">Volver a usuarios</a>
    </header>

    <main>
        <section>
            <p>Administración</p>
            <h1>Editar usuario</h1>

            <form action="../proc/admin_actualizar_usuario.php" method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="user_id" value="<?= (int) $usuario['id'] ?>">

                <div>
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" maxlength="50"
                           value="<?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errores['NombreVacio'])): ?>
                        <span class="mensaje-error">Debes escribir tu nombre.</span>
                    <?php elseif (isset($errores['NombreError'])): ?>
                        <span class="mensaje-error">El nombre no cumple con el formato requerido.</span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="username">Nombre de usuario:</label>
                    <input type="text" id="username" name="username" maxlength="20"
                           value="<?= htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errores['UsernameVacio'])): ?>
                        <span class="mensaje-error">Debes escribir un nombre de usuario.</span>
                    <?php elseif (isset($errores['UsernameError'])): ?>
                        <span class="mensaje-error">El nombre de usuario debe contener solo letras y números.</span>
                    <?php elseif (isset($errores['UsernameExistente'])): ?>
                        <span class="mensaje-error">El nombre de usuario ya está en uso.</span>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="role_id">Rol:</label>
                    <select id="role_id" name="role_id">
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int) $rol['id'] ?>"
                                <?= (int) $usuario['role_id'] === (int) $rol['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['RolError'])): ?>
                        <span class="mensaje-error">Selecciona un rol válido.</span>
                    <?php endif; ?>
                </div>

                <?php if (isset($errores['ErrorGuardado'])): ?>
                    <p class="mensaje-error">No se pudieron guardar los cambios. Comprueba que el nombre de usuario no esté en uso.</p>
                <?php endif; ?>

                <button type="submit">Guardar cambios</button>
            </form>
        </section>
    </main>
</body>
</html>
