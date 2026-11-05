<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
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
    header('Location: ../views/chat.php');
    exit();
}

$targetUserId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
$csrfToken = $_POST['csrf_token'] ?? '';

if (
    $targetUserId === false
    || $targetUserId < 1
    || !is_string($csrfToken)
    || !isset($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    header('Location: ../views/admin.php?resultado=invalido');
    exit();
}

$nombre = isset($_POST['nombre']) && is_string($_POST['nombre']) ? trim($_POST['nombre']) : '';
$username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
$roleId = filter_var($_POST['role_id'] ?? null, FILTER_VALIDATE_INT);
$errores = [];

if ($nombre === '') {
    $errores['NombreVacio'] = true;
} elseif (mb_strlen($nombre) > 50 || !preg_match('/^[\p{L}]+(?:[ -][\p{L}]+)*$/u', $nombre)) {
    $errores['NombreError'] = true;
}

if ($username === '') {
    $errores['UsernameVacio'] = true;
} elseif (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
    $errores['UsernameError'] = true;
}

$stmt = $conn->prepare('SELECT 1 FROM usuarios WHERE username = :username AND id <> :user_id LIMIT 1');
$stmt->execute([
    ':username' => $username,
    ':user_id' => $targetUserId,
]);

if ($username !== '' && !isset($errores['UsernameError']) && $stmt->fetchColumn()) {
    $errores['UsernameExistente'] = true;
}

$stmt = $conn->prepare('SELECT 1 FROM roles WHERE id = :role_id LIMIT 1');
$stmt->execute([':role_id' => $roleId === false ? 0 : $roleId]);

if ($roleId === false || !$stmt->fetchColumn()) {
    $errores['RolError'] = true;
}

if ($errores) {
    $_SESSION['admin_edit_errors'] = $errores;
    $_SESSION['admin_edit_data'] = [
        'user_id' => $targetUserId,
        'nombre' => $nombre,
        'username' => $username,
        'role_id' => $roleId === false ? 0 : $roleId,
    ];
    header('Location: ../views/admin_editar_usuario.php?id=' . $targetUserId);
    exit();
}

try {
    $stmt = $conn->prepare(
        'UPDATE usuarios
         SET nombre = :nombre, username = :username, role_id = :role_id
         WHERE id = :user_id'
    );
    $stmt->execute([
        ':nombre' => $nombre,
        ':username' => $username,
        ':role_id' => $roleId,
        ':user_id' => $targetUserId,
    ]);
} catch (PDOException $exception) {
    error_log('Error al editar usuario desde administración: ' . $exception->getMessage());
    $_SESSION['admin_edit_errors'] = ['ErrorGuardado' => true];
    $_SESSION['admin_edit_data'] = [
        'user_id' => $targetUserId,
        'nombre' => $nombre,
        'username' => $username,
        'role_id' => $roleId,
    ];
    header('Location: ../views/admin_editar_usuario.php?id=' . $targetUserId);
    exit();
}

header('Location: ../views/admin.php?resultado=actualizado');
exit();
