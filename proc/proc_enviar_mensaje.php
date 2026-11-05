<?php

// Inicia la sesión para identificar al usuario que envía el mensaje.
session_start();

// Carga la conexión y la función que comprueba la amistad.
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/proc_chat.php';

// Impide enviar mensajes si no hay una sesión válida.
if (!isset($_SESSION['user_id']) || !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)) {
    header('Location: ../views/login.php');
    exit();
}

// Obtiene el usuario destinatario y los datos enviados desde el formulario.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/chat.php');
    exit();
}

$idUsuario = (int) $_SESSION['user_id'];
$idAmigo = filter_input(INPUT_POST, 'usuario', FILTER_VALIDATE_INT);
$texto = isset($_POST['mensaje']) && is_string($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$busqueda = isset($_POST['buscar']) && is_string($_POST['buscar']) ? trim($_POST['buscar']) : '';
$archivo = $_FILES['archivo'] ?? null;
$tieneArchivo = is_array($archivo) && isset($archivo['error']) && $archivo['error'] !== UPLOAD_ERR_NO_FILE;

// Comprueba que exista un destinatario y que haya texto o un archivo.
if (!$idAmigo || strlen($texto) > 255 || strlen($busqueda) > 100 || ($texto === '' && !$tieneArchivo)) {
    header('Location: ../views/chat.php');
    exit();
}

// Comprueba que ambos usuarios tienen una amistad aceptada.
$idAmistad = obtenerIdAmistad($conn, $idUsuario, $idAmigo);
if ($idAmistad === null) {
    header('Location: ../views/chat.php');
    exit();
}

// Valida y guarda el archivo adjunto, si se ha enviado.
$nombreArchivo = null;
if ($tieneArchivo) {
    if (!isset($archivo['error'], $archivo['size'], $archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 5 * 1024 * 1024 || !is_uploaded_file($archivo['tmp_name'])) {
        header('Location: ../views/chat.php?usuario=' . $idAmigo);
        exit();
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $tipoArchivo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($tiposPermitidos[$tipoArchivo]) || @getimagesize($archivo['tmp_name']) === false) {
        header('Location: ../views/chat.php?usuario=' . $idAmigo);
        exit();
    }

    $directorioSubidas = __DIR__ . '/../media/uploads';
    if (!is_dir($directorioSubidas) && !mkdir($directorioSubidas, 0755, true)) {
        header('Location: ../views/chat.php?usuario=' . $idAmigo);
        exit();
    }

    $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipoArchivo];
    if (!move_uploaded_file($archivo['tmp_name'], $directorioSubidas . '/' . $nombreArchivo)) {
        header('Location: ../views/chat.php?usuario=' . $idAmigo);
        exit();
    }
}

// Guarda el mensaje asociado a la amistad y al usuario que lo envía.
$stmt = $conn->prepare(
    'INSERT INTO mensajes (fk_mensajes_amistad, fk_mensajes_emisor, mensaje, imagen)
    VALUES (:amistad, :emisor, :mensaje, :imagen)'
);
$stmt->execute([
    ':amistad' => $idAmistad,
    ':emisor' => $idUsuario,
    ':mensaje' => $texto,
    ':imagen' => $nombreArchivo,
]);

// guardar el contacto seleccionado y el filtro al volver al chat.
$parametros = ['usuario' => $idAmigo];
if ($busqueda !== '') {
    $parametros['buscar'] = $busqueda;
}

// Vuelve a la conversación para mostrar el mensaje guardado.
header('Location: ../views/chat.php?' . http_build_query($parametros));
exit();
