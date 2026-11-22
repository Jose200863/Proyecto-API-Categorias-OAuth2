CREATE DATABASE IF NOT EXISTS ventas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ventas;

CREATE TABLE IF NOT EXISTS categoria (
    codigo INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

INSERT IGNORE INTO categoria (codigo, nombre) VALUES
    (1, 'Computadoras'),
    (2, 'Telefonos'),
    (3, 'Impresoras'),
    (4, 'Accesorios');

CREATE TABLE IF NOT EXISTS Programadores (
    Cedula VARCHAR(20) PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Contrasena VARCHAR(20) NOT NULL
);

INSERT INTO Programadores (Cedula, Nombre, Contrasena) VALUES
    ('1', 'Luis', '01'),
    ('2', 'Ana', '02'),
    ('3', 'Maria', '03');

CREATE TABLE IF NOT EXISTS clientes_oauth (
    id              INT           NOT NULL AUTO_INCREMENT,
    identificador   VARCHAR(64)   NOT NULL,                      
    secreto_hash    VARCHAR(255)  NOT NULL,                     
    nombre          VARCHAR(100)  NOT NULL,
    permisos        VARCHAR(255)  NOT NULL,                         
    activo          TINYINT(1)    NOT NULL DEFAULT 1,
    fecha_creacion  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_identificador (identificador)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS tokens_oauth (
    id                 BIGINT        NOT NULL AUTO_INCREMENT,
    token_hash         CHAR(64)      NOT NULL,
    cliente_id         INT           NOT NULL,                     
    permisos           VARCHAR(255)  NOT NULL,                       
    fecha_vencimiento  DATETIME      NOT NULL,                       
    fecha_creacion     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_token_hash (token_hash),
    KEY idx_vencimiento (fecha_vencimiento),

    CONSTRAINT fk_token_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes_oauth (id)
        ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS solicitudes_api (
    id               BIGINT    NOT NULL AUTO_INCREMENT,
    cliente_id       INT       NOT NULL,                              
    fecha_solicitud  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_cliente_fecha (cliente_id, fecha_solicitud),

    CONSTRAINT fk_solicitud_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes_oauth (id)
        ON DELETE CASCADE
) ENGINE = InnoDB;

INSERT INTO clientes_oauth (identificador, secreto_hash, nombre, permisos) VALUES
    (
        'cliente_lector',
        '$2y$10$GAqfm0oUYCB0HyhGULleSOmhITbMElbGBR24CLY0TRRvrBCoW7AFq',
        'Cliente de solo lectura',
        'categorias:leer'
    ),
    (
        'cliente_admin',
        '$2y$10$BKPKY9MFEkmqpx1lqWACIuGabxgBwxYoGhRCcJTw7jDOUgHwWIE72',
        'Cliente con permisos completos',
        'categorias:leer categorias:escribir categorias:eliminar'
    )
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
