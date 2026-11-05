<?php

require_once __DIR__ . '/../conexion/conexion.php';

// Validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit();
}

$username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

// Validar que el nombre de usuario y la contraseña no estén vacíos y cumplan con los requisitos
if ($username === '' || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)
    || $password === '' || strlen($password) > 255) {
    $_POST['UsernameVacio'] = $username === '';
    $_POST['PasswordVacio'] = $password === '';
    require __DIR__ . '/../views/login.php';
    exit();
}

// Preparar y ejecutar la consulta para obtener el usuario
$stmt = $conn->prepare('SELECT id, username, password FROM usuarios WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el usuario existe y si la contraseña es correcta
if ($user && password_verify($password, $user['password'])) {
    session_start();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    header('Location: ../views/chat.php');
    exit();
}
// Si el usuario no existe o la contraseña es incorrecta, mostrar un mensaje de error
$_POST['LoginError'] = true;
require __DIR__ . '/../views/login.php';
exit();