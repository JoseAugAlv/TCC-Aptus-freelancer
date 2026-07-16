CREATE DATABASE ReciclyWays;

USE ReciclyWays;



-- 1. TABELA: USUARIO

-- Armazena alunos, professores e coordenadores

CREATE TABLE curso(
	id_curso INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(30)
    );

CREATE TABLE usuario (

    id_usuario INT PRIMARY KEY AUTO_INCREMENT,

    email VARCHAR(100) UNIQUE NOT NULL,

    senha VARCHAR(255) NOT NULL,

    nome VARCHAR(150) NOT NULL,

    tipo ENUM('Aluno', 'Professor', 'Coordenador') NOT NULL,

    ano_escolar ENUM('1º Ano','2º Ano','3º Ano', '1º Módulo', '2º Módulo', '3º Módulo'),

    id_curso INT,

    periodo ENUM('Manhã', 'Tarde', 'Noite') NOT NULL,

    rm VARCHAR(6),

    data_nascimento DATE,

    telefone VARCHAR(15),

    status ENUM('ativo', 'inativo', 'bloqueado') DEFAULT 'ativo',

    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_curso) REFERENCES curso(id_curso)

);

INSERT INTO curso(nome) VALUES ("Administração");

    

-- 2. TABELA: PROJETO

-- Armazena informações de cada viagem/projeto anual

CREATE TABLE projeto (

    id_projeto INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    destino VARCHAR(150),
    meta_total_reais DECIMAL(10, 2),
    percentual_reciclavel INT DEFAULT 51,
    meta_por_usuario DECIMAL(10, 2),
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    situacao ENUM('planejamento', 'ativo', 'finalizado') DEFAULT 'planejamento',
    publicado BOOLEAN DEFAULT true,
    imagem_capa VARCHAR(255),
    descricao_detalhada TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

-- 3. TABELA: TIPO_MATERIAL
-- Armazena tipos de materiais recicláveis e seus valores
CREATE TABLE tipo_material (
    id_tipo_material INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    categoria ENUM('reciclavel', 'oleo') DEFAULT 'reciclavel',
    unidade ENUM('kg', 'litro') NOT NULL,
    valor_unitario DECIMAL(5, 2) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT true
);

-- 4. TABELA: PAPEL_USUARIO
-- Armazena o papel de cada usuário em cada projeto

CREATE TABLE papel_usuario (

    id_papel_usuario INT PRIMARY KEY AUTO_INCREMENT,

    papel ENUM('colaborador', 'auxiliar', 'admin') NOT NULL,

    data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    id_usuario INT NOT NULL,

    id_projeto INT NOT NULL,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)

);

 

-- 5. TABELA: ENTREGA

-- Armazena cada entrega de material reciclável ou óleo

CREATE TABLE entrega (

    id_entrega INT PRIMARY KEY AUTO_INCREMENT,

    quantidade DECIMAL(10, 2) NOT NULL,

    valor_calculado DECIMAL(10, 2) NOT NULL,

    eh_reciclavel BOOLEAN NOT NULL,

    comprovante_arquivo VARCHAR(255),

    descricao TEXT,

    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',

    observacao_validacao TEXT,

    data_entrega DATETIME DEFAULT CURRENT_TIMESTAMP,

    data_validacao DATETIME,

    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    id_usuario INT NOT NULL,

    id_projeto INT NOT NULL,

    id_tipo_material INT NOT NULL,

    id_usuario_validador INT,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),

    FOREIGN KEY (id_tipo_material) REFERENCES tipo_material(id_tipo_material),

    FOREIGN KEY (id_usuario_validador) REFERENCES usuario(id_usuario)

);

 

-- 6. TABELA: PARTICIPACAO

-- Armazena o progresso de cada usuário em cada projeto

CREATE TABLE participacao (

    id_participacao INT PRIMARY KEY AUTO_INCREMENT,

    total_reciclavel DECIMAL(10, 2) DEFAULT 0,

    total_oleo DECIMAL(10, 2) DEFAULT 0,

    total_arrecadado DECIMAL(10, 2) DEFAULT 0,

    percentual_reciclavel DECIMAL(5, 2) DEFAULT 0,

    valor_pago_pix DECIMAL(10, 2) DEFAULT 0,

    percentual_pago_pix DECIMAL(5, 2) DEFAULT 0,

    atingiu_meta BOOLEAN DEFAULT false,

    data_atingimento_meta DATETIME,

    meta_valor DECIMAL(10, 2),

    id_usuario INT NOT NULL,

    id_projeto INT NOT NULL,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)

);

 

-- 7. TABELA: FOTO_VIDEO_PROJETO

-- Armazena fotos e vídeos associados a cada projeto

CREATE TABLE foto_video_projeto (

    id_foto_video INT PRIMARY KEY AUTO_INCREMENT,

    tipo ENUM('foto', 'video') NOT NULL,

    titulo VARCHAR(150),

    descricao TEXT,

    arquivo VARCHAR(255),

    url_video VARCHAR(255),

    ordem INT DEFAULT 0,

    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    id_projeto INT NOT NULL,

    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)

);

 

-- 8. TABELA: LOG_SISTEMA

-- Armazena logs de todas as operações importantes

CREATE TABLE log_sistema (

    id_log INT PRIMARY KEY AUTO_INCREMENT,

    acao VARCHAR(100) NOT NULL,

    tabela_afetada VARCHAR(50),

    registro_id INT,

    detalhes TEXT,

    ip_address VARCHAR(45),

    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    id_usuario INT,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)

);

CREATE TABLE notificacao (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_projeto INT,
    tipo VARCHAR(50) NOT NULL,  -- 'entrega_oleo_aprovada', 'pagamento_rejeitado', 'transferencia_recebida', etc
    titulo VARCHAR(150) NOT NULL,
    mensagem TEXT,
    lida BOOLEAN DEFAULT FALSE,
    tabela_origem VARCHAR(50),  -- qual tabela gerou a notificação
    registro_id INT,  -- id do registro que gerou (id_entrega_oleo, id_pagamento, etc)
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_leitura TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)
);




ALTER TABLE usuario

ADD COLUMN remember_token VARCHAR(255) NULL;



ALTER TABLE projeto

ADD COLUMN situacao VARCHAR(255) NULL;



-- CRIA UM PROJETO DE TESTE

INSERT INTO projeto (

    nome,

    descricao,

    destino,

    data_inicio,

    data_fim

)

VALUES (

    'Projeto Principal',

    'Projeto de teste do sistema',

    'Etec',

    CURDATE(),

    DATE_ADD(CURDATE(), INTERVAL 1 YEAR)

);



-- CRIA USUÁRIOS DE TESTE

-- SENHA PARA TODOS: 123



INSERT INTO usuario (

    email,

    senha,

    nome,

    tipo,

    ano_escolar,

    rm,

    data_nascimento,

    telefone

)

VALUES



(

    'admin@email.com',

    '$2y$10$ddaL0ciqbyFXScLl0nltwOkXGyTLpbD.e1Avree8tZ6qbkCI98CFm',

    'Administrador do Sistema',

    'Coordenador',

    NULL,

    NULL,

    '1990-01-01',

    '11999999999'

),

(

    'auxiliar@email.com',

    '$$2y$10$ddaL0ciqbyFXScLl0nltwOkXGyTLpbD.e1Avree8tZ6qbkCI98CFm',

    'Usuário Auxiliar',

    'Professor',

    NULL,

    NULL,

    '1995-01-01',

    '11988888888'

),



(

    'colaborador@email.com',

    '$2y$10$ddaL0ciqbyFXScLl0nltwOkXGyTLpbD.e1Avree8tZ6qbkCI98CFm',

    'Usuário Colaborador',

    'Aluno',

    '3º Ano',

    '123456',

    '2007-01-01',

    '11977777777'

);



-- VINCULA OS PAPÉIS AO PROJETO

-- PRESSUPÕE QUE:

-- PROJETO = ID 1

-- ADMIN = ID 1

-- AUXILIAR = ID 2

-- COLABORADOR = ID 3



INSERT INTO papel_usuario (

    papel,

    id_usuario,

    id_projeto

)

VALUES

('admin', 1, 1),

('auxiliar', 2, 1),

('colaborador', 3, 1);



SELECT * FROM projeto;
