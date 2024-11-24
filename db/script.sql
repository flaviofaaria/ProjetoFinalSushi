INSERT INTO tb_categoria (nome) VALUES
('Entradas'),
('Pratos Principais'),
('Bebidas'),
('Sobremesas');

INSERT INTO tb_itens (idCategoria, nome, descricao, foto, preco) VALUES
(1, 'Ceviche', 'Peixe fresco marinado em limão, com cebola roxa e coentro.', 'img/ceviche.jpg', 29.90),
(1, 'Guioza', 'Pastel chinês recheado com carne suína e vegetais.', 'img/guioza.jpg', 19.90),
(2, 'Sushi de Salmão', 'Arroz de sushi com fatias de salmão fresco.', 'img/sushi_salmao.jpg', 39.90),
(2, 'Yakissoba', 'Macarrão japonês frito com legumes e carne de frango.', 'img/yakissoba.jpg', 32.90),
(3, 'Saquê', 'Bebida alcoólica japonesa feita de arroz fermentado.', 'img/sake.jpg', 15.90),
(3, 'Chá Verde', 'Chá verde tradicional japonês.', 'img/cha_verde.jpg', 8.90),
(4, 'Mochi', 'Doce tradicional japonês feito de arroz glutinoso e recheado com pasta de feijão doce.', 'img/mochi.jpg', 12.90),
(4, 'Tempura de Sorvete', 'Sorvete frito com crosta crocante de massa tempura.', 'img/tempura_sorvete.jpg', 18.90);


CREATE TABLE tb_usuario (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    data_nascimento DATE,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    cep VARCHAR(10),
    rua VARCHAR(255),
    numero VARCHAR(10),
    bairro VARCHAR(100),
    complemento VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2)
);

CREATE TABLE tb_categoria (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);

CREATE TABLE tb_itens (
    id SERIAL PRIMARY KEY,
    idCategoria INT,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    foto BYTEA,
    preco DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (idCategoria) REFERENCES tb_categoria(id)
);

CREATE TABLE tb_itens_pedido (
    id SERIAL PRIMARY KEY,
    idUsuario INT,
    idItem INT,
    quantidade INT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    finalizado BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (idUsuario) REFERENCES tb_usuario(id),
    FOREIGN KEY (idItem) REFERENCES tb_itens(id)
);
