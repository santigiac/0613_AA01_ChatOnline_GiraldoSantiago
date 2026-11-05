Create database db_chat;

use db_chat;

create table roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE="INNODB";

INSERT INTO roles (nombre) VALUES
('usuario'),
('admin');

create table usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL
) ENGINE="INNODB";

create table amistad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fk_amistad_emisor INT NOT NULL,
    fk_amistad_receptor INT NOT NULL,
    estado ENUM('pendiente', 'aceptado', 'rechazado', 'bloqueado') NOT NULL DEFAULT 'pendiente',
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unica_amistad (fk_amistad_emisor, fk_amistad_receptor)
) ENGINE="INNODB";

create table mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fk_mensajes_amistad INT NOT NULL,
    fk_mensajes_emisor INT NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    imagen VARCHAR(255),
    enviado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE="INNODB";


ALTER TABLE amistad
ADD CONSTRAINT fk_amistad_emisor FOREIGN KEY (fk_amistad_emisor) REFERENCES usuarios(id),
ADD CONSTRAINT fk_amistad_receptor FOREIGN KEY (fk_amistad_receptor) REFERENCES usuarios(id);

ALTER TABLE mensajes
ADD CONSTRAINT fk_mensajes_amistad FOREIGN KEY (fk_mensajes_amistad) REFERENCES amistad(id),
ADD CONSTRAINT fk_mensajes_emisor FOREIGN KEY (fk_mensajes_emisor) REFERENCES usuarios(id);

ALTER TABLE usuarios
ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (role_id) REFERENCES roles(id);




INSERT INTO usuarios (nombre, username, password, role_id) VALUES
('Junhao', 'Hao', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.', (SELECT id FROM roles WHERE nombre = 'usuario')),
('David hola', 'David', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.', (SELECT id FROM roles WHERE nombre = 'usuario')),
('Santi seven', 'santi', '$2y$10$gFACBX6xVH5E.hBza0GZkOMX5msI6ic1c67bLcZ16YcCdcNQFuBOa', (SELECT id FROM roles WHERE nombre = 'usuario')),

('Lucas Fernandez', 'Lucas', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.', (SELECT id FROM roles WHERE nombre = 'usuario')),
('Ana Romero', 'Ana', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.', (SELECT id FROM roles WHERE nombre = 'usuario')),
('Pablo Sanchez', 'Pablo', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.', (SELECT id FROM roles WHERE nombre = 'usuario')),
('Elena Torres', 'Elena', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.', (SELECT id FROM roles WHERE nombre = 'usuario')),
('Administrador', 'admin', '$2y$10$0JTcdACt1cfArBUsA1gHY.MLskrslBntO9WWPNfLlXQyVXHwzW75e', (SELECT id FROM roles WHERE nombre = 'admin'));

INSERT INTO amistad (fk_amistad_emisor, fk_amistad_receptor, estado)
VALUES (1, 2, 'aceptado');

INSERT INTO amistad (fk_amistad_emisor, fk_amistad_receptor, estado)
SELECT emisor.id, receptor.id, 'aceptado'
FROM usuarios AS emisor
JOIN usuarios AS receptor ON emisor.id < receptor.id
LEFT JOIN amistad AS existente
    ON existente.fk_amistad_emisor = emisor.id
    AND existente.fk_amistad_receptor = receptor.id
WHERE existente.id IS NULL;

INSERT INTO mensajes (fk_mensajes_amistad, fk_mensajes_emisor, mensaje) VALUES
(1, 1, 'Hola Luis, que tal?'),
(1, 2, 'Hola Ana, todo bien.');

