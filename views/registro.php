<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Chat Online</title>
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

            <p class="etiqueta">Únete a la conversación</p>
            
            <h1>Registro de usuario</h1>
            
            <p class="texto-ayuda">Crea tu cuenta para empezar a hablar con tus amigos.</p>


            <form class="formulario formulario-registro" action="../proc/validation_register.php" method="POST">
            
                <div class="campo">
                
                    <label for="nombre">Nombre:</label>

                    <input type="text" id="nombre" name="nombre" autocomplete="name"
                           value="<?php echo htmlspecialchars((string) ($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <?php if (isset($_POST['NombreVacio'])) {echo "Debes escribir tu nombre."; } ?>
                    <?php if (isset($_POST['NombreError'])) {echo "El nombre no cumple con el formato requerido."; } ?>

                </div>


                <div class="campo">

                    <label for="usuario">Nombre de usuario:</label>

                    <input type="text" id="usuario" name="username" autocomplete="username"
                           value="<?php echo htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                    <?php if (isset($_POST['UsernameVacio'])) {echo "Debes escribir un nombre de usuario."; } ?>
                    <?php if (isset($_POST['UsernameError'])) {echo "El nombre de usuario debe contener solo letras y números."; } ?>

                </div>


                <div class="campo">

                    <label for="password">Contraseña:</label>

                    <input type="password" id="password" name="password" minlength="6" autocomplete="new-password">

                    <?php if (isset($_POST['PasswordVacio'])) {echo "Debes escribir una contraseña."; } ?>
                    <?php if (isset($_POST['PasswordCorta'])) {echo "La contraseña debe tener al menos 6 caracteres."; } ?>

                </div>


                <div class="campo">

                    <label for="confirmar-password">Confirmar contraseña:</label>

                    <input type="password" id="confirmar-password" name="password_confirm" minlength="6" autocomplete="new-password">

                    <?php if (isset($_POST['PasswordConfirmVacio'])) {echo "Debes confirmar tu contraseña."; } ?>
                    <?php if (isset($_POST['PasswordError'])) {echo "Las contraseñas no coinciden."; } ?>

                </div>

                <?php if (isset($_POST['UsernameExistente'])) {echo "El nombre de usuario ya está en uso. Por favor, elige otro."; } ?>

                <button class="boton boton-principal boton-ancho" type="submit" name="registrarse">Registrarse</button>
                <p class="enlace-ayuda"><a href="../index.php">Cancelar registro</a></p>
            </form>
        </section>
    </main>
    
</body>
</html>