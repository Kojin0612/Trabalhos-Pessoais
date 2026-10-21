CREATE DATABASE IF NOT EXISTS biblioteca
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE biblioteca;

CREATE TABLE IF NOT EXISTS livros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100) NOT NULL,
    year_publication YEAR NOT NULL,
    isbn VARCHAR(20) NULL,
    genero VARCHAR(60) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0
);


CREATE TABLE IF NOT EXISTS movimentos_estoque (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    livro_id INT UNSIGNED NULL,
    tipo ENUM('cadastro', 'entrada', 'saida', 'ajuste') NOT NULL,
    quantidade INT UNSIGNED NOT NULL DEFAULT 0,
    quantidade_anterior INT UNSIGNED NOT NULL DEFAULT 0,
    quantidade_atual INT UNSIGNED NOT NULL DEFAULT 0,
    data_movimentacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimentos_livros
        FOREIGN KEY (livro_id) REFERENCES livros(id)
        ON DELETE SET NULL,
    INDEX idx_movimentos_data (data_movimentacao)
);
