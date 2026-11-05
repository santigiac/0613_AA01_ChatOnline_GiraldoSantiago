<?php 
 
 $servername = "localhost:3306"; // Nombre del servidor 
 $dbusername = "root"; // Nombre de usuario 
 $dbpassword = ""; // Contraseña 
 $dbname = "db_chat"; // Nombre de la base de datos 
 
try { 
    // Se crea una instancia de la clase PDO para establecer la conexión 
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword); 
  
    // Se establece el modo de errores de PDO para lanzar excepciones en lugar de advertencias 
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
      
} catch (PDOException $e) { 

    // Se captura la excepción y se muestra el mensaje de error 
    echo "Error en la conexión a la base de datos: " . $e->getMessage(); 
    die(); 

} 