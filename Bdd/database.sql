Create database db_chat;

use db_chat;

create table usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
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




INSERT INTO usuarios (nombre, username, password) VALUES
('Junhao', 'Hao', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('David hola', 'David', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Santi seven', 'Santi', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Alicia Garcia', 'Alicia', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Carlos Martinez', 'Carlos', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Maria Lopez', 'Maria', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Lucas Fernandez', 'Lucas', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Ana Romero', 'Ana', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Pablo Sanchez', 'Pablo', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.'),
('Elena Torres', 'Elena', '$2y$12$VUf3M2Cru4hjes2GVEtaSO14Q8MZQho8YrIsK0ukIvxTSl9RctCh.');

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