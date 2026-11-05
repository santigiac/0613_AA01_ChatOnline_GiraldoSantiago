<?php

require_once __DIR__ . '/../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['registrarse'])) {
    header('Location: ../views/registro.php');
    exit();
}

$nombre = isset($_POST['nombre']) && is_string($_POST['nombre']) ? trim($_POST['nombre']) : '';
$username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
$passwordConfirm = isset($_POST['password_confirm']) && is_string($_POST['password_confirm'])
    ? $_POST['password_confirm']
    : '';

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

if ($password === '') {
    $errores['PasswordVacio'] = true;
} elseif (strlen($password) < 6) {
    $errores['PasswordCorta'] = true;
}

if ($passwordConfirm === '') {
    $errores['PasswordConfirmVacio'] = true;
} elseif ($password !== $passwordConfirm) {
    $errores['PasswordError'] = true;
}

if ($errores) {
    $_POST = array_merge($_POST, $errores);
    require __DIR__ . '/../views/registro.php';
    exit();
}
// Verificar si el nombre de usuario ya existe en la base de datos
$stmt = $conn->prepare('SELECT 1 FROM usuarios WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);

// Si el nombre de usuario ya existe, mostrar un mensaje de error
if ($stmt->fetchColumn()) {
    $_POST['UsernameExistente'] = true;
    require __DIR__ . '/../views/registro.php';
    exit();
}
$roleStmt = $conn->prepare('SELECT id FROM roles WHERE nombre = :nombre');
$roleStmt->execute([':nombre' => 'usuario']);
$roleId = $roleStmt->fetchColumn();

if ($roleId === false) {
    throw new RuntimeException('No existe el rol usuario en la base de datos.');
}

// Insertar el nuevo usuario en la base de datos con el rol normal
$stmt = $conn->prepare(
    'INSERT INTO usuarios (nombre, username, password, role_id)
     VALUES (:nombre, :username, :password, :role_id)'
);
$stmt->execute([
    ':nombre' => $nombre,
    ':username' => $username,
    ':password' => password_hash($password, PASSWORD_DEFAULT),
    ':role_id' => $roleId,
]);

header('Location: ../views/login.php?registro=ok');
exit();
