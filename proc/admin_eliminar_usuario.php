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

$csrfToken = $_POST['csrf_token'] ?? '';
$targetUserId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);

if (
    !is_string($csrfToken)
    || !isset($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $csrfToken)
    || $targetUserId === false
    || $targetUserId < 1
    || $targetUserId === (int) $_SESSION['user_id']
) {
    header('Location: ../views/admin.php?resultado=invalido');
    exit();
}

try {
    $conn->beginTransaction(); 

    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE id = :user_id FOR UPDATE');
    $stmt->execute([':user_id' => $targetUserId]);

    if (!$stmt->fetchColumn()) {
        $conn->rollBack();
        header('Location: ../views/admin.php?resultado=invalido');
        exit();
    }

    $stmt = $conn->prepare(
        'DELETE mensajes
         FROM mensajes
         INNER JOIN amistad ON amistad.id = mensajes.fk_mensajes_amistad
         WHERE amistad.fk_amistad_emisor = :emisor
            OR amistad.fk_amistad_receptor = :receptor'
    );
    $stmt->execute([
        ':emisor' => $targetUserId,
        ':receptor' => $targetUserId,
    ]);

    $stmt = $conn->prepare(
        'DELETE FROM amistad
         WHERE fk_amistad_emisor = :emisor
            OR fk_amistad_receptor = :receptor'
    );
    $stmt->execute([
        ':emisor' => $targetUserId,
        ':receptor' => $targetUserId,
    ]);

    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = :user_id');
    $stmt->execute([':user_id' => $targetUserId]);

    $conn->commit();
} catch (PDOException $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error al eliminar usuario desde administración: ' . $exception->getMessage());
    header('Location: ../views/admin.php?resultado=error');
    exit();
}

header('Location: ../views/admin.php?resultado=eliminado');
exit();
