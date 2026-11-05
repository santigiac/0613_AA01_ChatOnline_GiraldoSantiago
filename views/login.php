<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chat Online</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body class="pagina">
    <header class="cabecera">
        <a class="marca" href="../index.php">
            <img class="marca-logo" src="../media/logo/LogoChat.png" alt="Logo de Chat Online">
            <span>Chat Online</span>
        </a>
    </header>

    <main class="contenido-principal">
        <section class="tarjeta formulario-tarjeta">

            <p class="etiqueta">Qué alegría verte</p>

            <h1>Iniciar sesión</h1>

            <p class="texto-ayuda">Accede a tus conversaciones y sigue en contacto.</p>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
                <p class="texto-ayuda">Registro completado. Ya puedes iniciar sesión.</p>
            <?php endif; ?>

            <form class="formulario" action="../proc/validation_login.php" method="POST">

                <div class="campo">

                    <label for="usuario">Nombre de usuario:</label>

                    <input type="text" id="usuario" name="username" autocomplete="username"
                           value="<?php echo htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                    <?php if (isset($_POST['UsernameVacio'])): ?>
                        <span class="mensaje-error">Debes escribir un nombre de usuario.</span>
                    <?php endif; ?>

                </div>

                <div class="campo">

                    <label for="password">Contraseña:</label>

                    <input type="password" id="password" name="password">

                    <?php if (isset($_POST['PasswordVacio'])): ?>
                        <span class="mensaje-error">Debes escribir una contraseña.</span>
                    <?php endif; ?>

                </div>

                <?php if (isset($_POST['LoginError'])): ?>
                    <p class="mensaje-error">El usuario o la contraseña no son correctos.</p>
                <?php endif; ?>

                <button class="boton boton-principal boton-ancho" type="submit">Iniciar sesión</button>
            </form>

            <p class="enlace-ayuda">¿Aún no tienes una cuenta? <a href="../views/registro.php">Regístrate aquí</a></p>
            
        </section>
    </main>

</body>
</html>