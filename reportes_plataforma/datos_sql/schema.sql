CREATE DATABASE reportes_db CHARACTER SET utf8mb4;
USE reportes_db;

CREATE TABLE supervisores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tienda VARCHAR(100),
  supervisor VARCHAR(100)
);

CREATE TABLE circuitos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  aplicativo VARCHAR(100),
  circuito VARCHAR(100)
);

CREATE TABLE reportes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tienda VARCHAR(100),
  tipo VARCHAR(50),
  fecha DATE,
  hora TIME,
  estado VARCHAR(50),
  severidad VARCHAR(50),
  aplicativo VARCHAR(100),
  circuito VARCHAR(100),
  supervisor VARCHAR(100),
  detalle TEXT
);
