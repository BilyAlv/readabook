DROP DATABASE read_a_book;

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS read_a_book;

-- Usar la base de datos
USE read_a_book;

-- Crear tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla libros
CREATE TABLE IF NOT EXISTS libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(100),
    categoria VARCHAR(50),
    precio DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE libros 
ADD COLUMN descripcion TEXT AFTER precio,
ADD COLUMN imagen VARCHAR(255) AFTER descripcion;

-- Crear tabla pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    libro_id INT,
    cantidad INT DEFAULT 1,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (libro_id) REFERENCES libros(id)
);

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS autores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    biografia TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuarios
INSERT INTO usuarios (nombre, email, password) VALUES
('Juan Pérez', 'juan@example.com', 'password123'),
('María Gómez', 'maria@example.com', 'password123');

-- Insertar libros
INSERT INTO libros (titulo, autor, categoria, precio, stock) VALUES
('Cien años de soledad', 'Gabriel García Márquez', 'Ficción', 20.99, 10),
('El código Da Vinci', 'Dan Brown', 'Misterio', 15.50, 5);

-- Insertar pedidos
INSERT INTO pedidos (usuario_id, libro_id, cantidad) VALUES
(1, 1, 2),  -- Juan compró 2 libros de "Cien años de soledad"
(2, 2, 1);  -- María compró 1 libro de "El código Da Vinci"

ALTER TABLE usuarios ADD COLUMN estado VARCHAR(50);
ALTER TABLE libros ADD COLUMN estado VARCHAR(50);

CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    mensaje VARCHAR(255),
    estado VARCHAR(50) DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO notificaciones (usuario_id, mensaje, estado) VALUES
(1, 'Tienes un nuevo mensaje', 'pendiente'),
(2, 'Tu pedido ha sido procesado', 'pendiente');

ALTER TABLE usuarios ADD COLUMN rol VARCHAR(50) DEFAULT 'usuario';

-- Eliminar notificaciones
DELETE FROM notificaciones;

-- Eliminar pedidos
DELETE FROM pedidos;

-- Eliminar libros
DELETE FROM libros;

-- Eliminar usuarios
DELETE FROM usuarios;

-- Eliminar categorías (si hay datos)
DELETE FROM categorias;

-- Eliminar autores (si hay datos)
DELETE FROM autores;

-- Reiniciar contadores de auto-incremento
ALTER TABLE notificaciones AUTO_INCREMENT = 1;
ALTER TABLE pedidos AUTO_INCREMENT = 1;
ALTER TABLE libros AUTO_INCREMENT = 1;
ALTER TABLE usuarios AUTO_INCREMENT = 1;
ALTER TABLE categorias AUTO_INCREMENT = 1;
ALTER TABLE autores AUTO_INCREMENT = 1;

-- Insertar libros de ficción
INSERT INTO libros (titulo, autor, categoria, precio, descripcion, stock, imagen) VALUES
('Cien años de soledad', 'Gabriel García Márquez', 'Ficción', 22.99, 'Una obra maestra de la literatura hispanoamericana que narra la historia de la familia Buendía en el pueblo de Macondo.', 15, 'cien-anos-soledad.jpg'),
('1984', 'George Orwell', 'Ficción', 18.50, 'Una distopía clásica que explora temas de vigilancia gubernamental y manipulación de la verdad.', 12, '1984.jpg'),
('El Hobbit', 'J.R.R. Tolkien', 'Fantasía', 19.99, 'La aventura de Bilbo Bolsón que lo lleva a un viaje inesperado con un grupo de enanos.', 20, 'hobbit.jpg');

-- Insertar libros de misterio y thriller
INSERT INTO libros (titulo, autor, categoria, precio, descripcion, stock, imagen) VALUES
('El código Da Vinci', 'Dan Brown', 'Misterio', 16.75, 'Un thriller que mezcla arte, religión y secretos históricos en una trama trepidante.', 8, 'codigo-da-vinci.jpg'),
('La chica del tren', 'Paula Hawkins', 'Thriller', 14.99, 'Una historia psicológica sobre una mujer que se ve envuelta en una investigación de desaparición.', 10, 'chica-tren.jpg'),
('Gone Girl', 'Gillian Flynn', 'Thriller', 17.25, 'Un thriller psicológico sobre un matrimonio que no es lo que parece y una desaparición misteriosa.', 7, 'gone-girl.jpg');

-- Insertar libros de no ficción
INSERT INTO libros (titulo, autor, categoria, precio, descripcion, stock, imagen) VALUES
('Sapiens: De animales a dioses', 'Yuval Noah Harari', 'Historia', 24.50, 'Un recorrido por la historia de la humanidad desde la evolución hasta la actualidad.', 18, 'sapiens.jpg'),
('El poder del ahora', 'Eckhart Tolle', 'Autoayuda', 15.99, 'Una guía para la iluminación espiritual y vivir en el momento presente.', 14, 'poder-ahora.jpg'),
('Atomic Habits', 'James Clear', 'Autoayuda', 20.00, 'Cómo construir buenos hábitos y romper los malos mediante pequeños cambios.', 22, 'atomic-habits.jpg');

-- Insertar libros de ciencia ficción
INSERT INTO libros (titulo, autor, categoria, precio, descripcion, stock, imagen) VALUES
('Dune', 'Frank Herbert', 'Ciencia Ficción', 21.50, 'Un clásico de la ciencia ficción que explora política, religión y ecología en un futuro lejano.', 9, 'dune.jpg'),
('Fundación', 'Isaac Asimov', 'Ciencia Ficción', 19.75, 'La historia del psicohistoriador Hari Seldon y su plan para salvar el conocimiento humano.', 11, 'fundacion.jpg'),
('Ready Player One', 'Ernest Cline', 'Ciencia Ficción', 16.99, 'Una aventura futurista en un mundo virtual lleno de referencias a la cultura pop de los 80.', 13, 'ready-player-one.jpg');

-- Insertar libros clásicos
INSERT INTO libros (titulo, autor, categoria, precio, descripcion, stock, imagen) VALUES
('Orgullo y prejuicio', 'Jane Austen', 'Clásico', 12.99, 'La historia de Elizabeth Bennet y el señor Darcy en la Inglaterra del siglo XIX.', 16, 'orgullo-prejuicio.jpg'),
('Moby Dick', 'Herman Melville', 'Clásico', 14.25, 'La obsesiva búsqueda del capitán Ahab por la ballena blanca que le arrancó la pierna.', 8, 'moby-dick.jpg'),
('Crimen y castigo', 'Fiódor Dostoyevski', 'Clásico', 13.50, 'Un profundo estudio psicológico sobre la culpa y la redención.', 10, 'crimen-castigo.jpg');