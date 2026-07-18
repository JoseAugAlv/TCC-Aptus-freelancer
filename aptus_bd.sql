CREATE DATABASE Aptus;
USE Aptus;

-- ============================================================================
-- SEM TRIGGERS (por decisão do projeto). Toda transição de status em
-- cascata (aprovar anúncio, bater confirmação de pagamento, recalcular nota
-- média, banir usuário, resolver disputa etc.) fica no Controller, dentro
-- de uma transação PDO — mesmo padrão do aprovar() no RecycleWays original.
-- ============================================================================

-- ============================================================================
-- TABELAS LOOKUP / REFERÊNCIA
-- ============================================================================

CREATE TABLE perfil (
    id_perfil INT PRIMARY KEY AUTO_INCREMENT,
    perfil VARCHAR(20) UNIQUE NOT NULL
);
INSERT INTO perfil(perfil) VALUES ('Admin'), ('Moderador'), ('Usuario'), ('Master');
-- perfil = nível de acesso administrativo.
-- "Freelancer" e "Contratante" NÃO são perfis fixos (RF01: cadastro único):
-- qualquer usuário 'Usuario' pode anunciar um serviço (freelancer daquele
-- anúncio) e demonstrar interesse em outros (contratante daquela negociação).

CREATE TABLE situacao (
    id_situacao INT PRIMARY KEY AUTO_INCREMENT,
    situacao VARCHAR(20) NOT NULL UNIQUE
);
INSERT INTO situacao(situacao) VALUES ('Pendente'), ('Aprovado'), ('Rejeitado'), ('Cancelado');

CREATE TABLE categoria (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    descricao VARCHAR(255),
    icone VARCHAR(60),
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE habilidade (
    id_habilidade INT PRIMARY KEY AUTO_INCREMENT,
    id_categoria INT,
    nome VARCHAR(80) NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

-- ============================================================================
-- TABELA CORE: USUÁRIO
-- ============================================================================

CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    id_perfil INT NOT NULL DEFAULT 3,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(15),
    whatsapp VARCHAR(15) NULL COMMENT 'usado para gerar o link https://wa.me/... (RF08)',
    cpf_cnpj VARCHAR(20) UNIQUE,
    data_nascimento DATE,
    foto_perfil VARCHAR(255),
    bio TEXT,
    cidade VARCHAR(100),
    estado CHAR(2),
    nota_media DECIMAL(3,2) DEFAULT 0,
    total_avaliacoes INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    banido BOOLEAN DEFAULT FALSE,
    motivo_banimento VARCHAR(255) NULL,
    data_banimento DATETIME NULL,
    id_moderador_banimento INT NULL,
    token_verificacao VARCHAR(64) NULL,
    email_verificado BOOLEAN DEFAULT FALSE,
    data_verificacao DATETIME NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil),
    FOREIGN KEY (id_moderador_banimento) REFERENCES usuario(id_usuario)
);

CREATE TABLE usuario_habilidade (
    id_usuario_habilidade INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_habilidade INT NOT NULL,
    nivel ENUM('basico', 'intermediario', 'avancado') DEFAULT 'intermediario',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_habilidade) REFERENCES habilidade(id_habilidade),
    UNIQUE KEY uk_usuario_habilidade (id_usuario, id_habilidade)
);

-- Portfólio do PERFIL (RF07) — galeria de trabalhos já realizados,
-- independente dos anúncios ativos no momento.
CREATE TABLE portfolio (
    id_portfolio INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- ============================================================================
-- ANÚNCIO DE SERVIÇO (RF05/RF06) — o freelancer anuncia o que faz e o preço
-- ============================================================================

CREATE TABLE anuncio_servico (
    id_anuncio INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL COMMENT 'dono do anuncio (freelancer)',
    id_categoria INT,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    slug VARCHAR(180) UNIQUE,
    preco DECIMAL(10,2) NOT NULL COMMENT 'preco sugerido (RF05)',
    foto_capa VARCHAR(255),
    situacao ENUM('ativo', 'pausado', 'excluido') DEFAULT 'ativo' COMMENT 'controlado pelo proprio freelancer (RF06)',
    id_situacao_moderacao INT NOT NULL DEFAULT 1 COMMENT 'Pendente/Aprovado/Rejeitado - controlado pelo moderador (RF17)',
    motivo_remocao VARCHAR(255) NULL,
    visualizacoes INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
    FOREIGN KEY (id_situacao_moderacao) REFERENCES situacao(id_situacao)
);

-- Fotos adicionais do anúncio (além da foto de capa)
CREATE TABLE anuncio_foto (
    id_anuncio_foto INT PRIMARY KEY AUTO_INCREMENT,
    id_anuncio INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio_servico(id_anuncio)
);

-- ============================================================================
-- FAVORITOS (RF12)
-- ============================================================================

CREATE TABLE favorito (
    id_favorito INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL COMMENT 'contratante que favoritou',
    id_anuncio INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_anuncio) REFERENCES anuncio_servico(id_anuncio),
    UNIQUE KEY uk_usuario_anuncio (id_usuario, id_anuncio)
);

-- ============================================================================
-- INTERESSE (RF13) — clique de "Tenho interesse"/"Contratar".
-- É o elo leve entre contratante e freelancer para um anúncio específico;
-- substitui o antigo "contrato" da v1 (sem valor/prazo formais, porque o
-- preço já está fixo no anúncio e o resto é combinado por fora).
-- ============================================================================

CREATE TABLE interesse (
    id_interesse INT PRIMARY KEY AUTO_INCREMENT,
    id_anuncio INT NOT NULL,
    id_contratante INT NOT NULL,
    id_freelancer INT NOT NULL COMMENT 'redundante com anuncio_servico.id_usuario, denormalizado para facilitar consultas',
    mensagem_inicial TEXT NULL,
    situacao ENUM('ativo', 'concluido', 'cancelado') DEFAULT 'ativo',
    data_interesse DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_conclusao DATETIME NULL,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio_servico(id_anuncio),
    FOREIGN KEY (id_contratante) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_freelancer) REFERENCES usuario(id_usuario)
);

-- ============================================================================
-- ⭐ CONFIRMAÇÃO DE PAGAMENTO EM VIA DE MÃO DUPLA (diferencial mantido da v1)
-- O dinheiro NUNCA passa pela plataforma. Contratante e freelancer
-- confirmam, cada um do seu lado, que o pagamento combinado foi feito.
-- ============================================================================

CREATE TABLE confirmacao_pagamento (
    id_confirmacao INT PRIMARY KEY AUTO_INCREMENT,
    id_interesse INT NOT NULL UNIQUE,

    -- lado do contratante (quem paga)
    confirmado_contratante BOOLEAN DEFAULT FALSE,
    valor_informado_contratante DECIMAL(10,2),
    forma_pagamento_contratante ENUM('pix', 'transferencia', 'dinheiro', 'cartao', 'outro'),
    data_pagamento_contratante DATE,
    data_confirmacao_contratante DATETIME,
    observacao_contratante TEXT,

    -- lado do freelancer (quem recebe)
    confirmado_freelancer BOOLEAN DEFAULT FALSE,
    valor_informado_freelancer DECIMAL(10,2),
    data_recebimento_freelancer DATE,
    data_confirmacao_freelancer DATETIME,
    observacao_freelancer TEXT,

    situacao_final ENUM('pendente', 'confirmado', 'divergente') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_interesse) REFERENCES interesse(id_interesse)
);

-- ============================================================================
-- DISPUTAS (quando a confirmação de pagamento diverge)
-- ============================================================================

CREATE TABLE disputa (
    id_disputa INT PRIMARY KEY AUTO_INCREMENT,
    id_interesse INT NOT NULL,
    id_aberto_por INT NOT NULL,
    motivo VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    id_situacao INT NOT NULL DEFAULT 1,
    id_responsavel INT NULL COMMENT 'moderador ou admin que analisou',
    resposta TEXT,
    data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_resolucao DATETIME,
    FOREIGN KEY (id_interesse) REFERENCES interesse(id_interesse),
    FOREIGN KEY (id_aberto_por) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_responsavel) REFERENCES usuario(id_usuario)
);

CREATE TABLE disputa_anexo (
    id_anexo INT PRIMARY KEY AUTO_INCREMENT,
    id_disputa INT NOT NULL,
    id_usuario INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_disputa) REFERENCES disputa(id_disputa),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- ============================================================================
-- AVALIAÇÕES (RF14/RF15) — nota + comentário do contratante, e resposta
-- pública do freelancer (pedido explícito do documento de requisitos)
-- ============================================================================

CREATE TABLE avaliacao (
    id_avaliacao INT PRIMARY KEY AUTO_INCREMENT,
    id_interesse INT NOT NULL,
    id_avaliador INT NOT NULL COMMENT 'normalmente o contratante',
    id_avaliado INT NOT NULL COMMENT 'normalmente o freelancer',
    nota TINYINT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    resposta_avaliado TEXT NULL COMMENT 'freelancer responde publicamente ao comentario',
    data_resposta DATETIME NULL,
    data_avaliacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_interesse) REFERENCES interesse(id_interesse),
    FOREIGN KEY (id_avaliador) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_avaliado) REFERENCES usuario(id_usuario),
    UNIQUE KEY uk_interesse_avaliador (id_interesse, id_avaliador)
);

-- ============================================================================
-- MENSAGENS (RF08) — chat simples vinculado ao interesse
-- ============================================================================

CREATE TABLE mensagem (
    id_mensagem INT PRIMARY KEY AUTO_INCREMENT,
    id_interesse INT NOT NULL,
    id_remetente INT NOT NULL,
    id_destinatario INT NOT NULL,
    mensagem TEXT NOT NULL,
    arquivo_anexo VARCHAR(255),
    lida BOOLEAN DEFAULT FALSE,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_interesse) REFERENCES interesse(id_interesse),
    FOREIGN KEY (id_remetente) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_destinatario) REFERENCES usuario(id_usuario)
);

-- ============================================================================
-- DENÚNCIAS (RF16) — anúncio ou perfil
-- ============================================================================

CREATE TABLE denuncia (
    id_denuncia INT PRIMARY KEY AUTO_INCREMENT,
    id_denunciante INT NOT NULL,
    id_denunciado INT NOT NULL COMMENT 'dono do anuncio ou usuario reportado',
    id_anuncio INT NULL,
    motivo VARCHAR(100) NOT NULL,
    descricao TEXT,
    id_situacao INT NOT NULL DEFAULT 1,
    id_moderador_analise INT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_analise DATETIME,
    FOREIGN KEY (id_denunciante) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_denunciado) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_anuncio) REFERENCES anuncio_servico(id_anuncio),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_moderador_analise) REFERENCES usuario(id_usuario)
);

-- ============================================================================
-- BUSCA (RF19 — relatório de categorias mais buscadas)
-- ============================================================================

CREATE TABLE busca_log (
    id_busca INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NULL COMMENT 'null se busca de visitante nao logado',
    termo_buscado VARCHAR(150),
    id_categoria INT NULL,
    data_busca DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

-- ============================================================================
-- LOGS E NOTIFICAÇÕES
-- ============================================================================

CREATE TABLE log_sistema (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    acao VARCHAR(100) NOT NULL,
    tabela_afetada VARCHAR(50),
    registro_id INT,
    detalhes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT COMMENT 'quem executou a acao (ex.: moderador que baniu)',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE notificacao (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_interesse INT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensagem TEXT,
    lida BOOLEAN DEFAULT FALSE,
    tabela_origem VARCHAR(50),
    registro_id INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_leitura TIMESTAMP NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_interesse) REFERENCES interesse(id_interesse)
);

CREATE TABLE reset_senha (
    id_reset INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiracao DATETIME NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);
-- ============================================================================
-- USUÁRIOS PRÉ-CADASTRADOS PARA TESTE
-- Senha de TODOS: 123456
-- ============================================================================
INSERT INTO usuario (id_perfil, nome, email, senha, email_verificado, ativo, banido) VALUES
(3, 'Usuário Teste', 'usuario@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 1, 1, 0),
(2, 'Moderador Teste', 'moderador@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 1, 1, 0),
(1, 'Administrador Teste', 'admin@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 1, 1, 0),
(4, 'Master Teste', 'master@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 1, 1, 0);

SELECT id_usuario, nome, email, id_perfil, senha, email_verificado, ativo, banido 
FROM usuario 
WHERE email = 'usuario@aptus.com';

-- ============================================================================
-- SEM TRIGGERS — a lógica que seria automática fica nos Controllers
-- ============================================================================
-- Toda a lógica que envolve efeito colateral em outra tabela (ex.: ao bater
-- confirmado_contratante e confirmado_freelancer com valores iguais, marcar
-- interesse.situacao = 'concluido'; ao divergir, sugerir disputa; ao
-- aprovar anúncio na moderação, liberar id_situacao_moderacao = Aprovado;
-- ao banir usuário, também pausar todos os anúncios dele; ao inserir
-- avaliação, recalcular nota_media/total_avaliacoes do usuario avaliado)
-- deve ficar nos Controllers, dentro de transação PDO, seguindo o mesmo
-- padrão que o RecycleWays original usa em
-- TransferenciaController@aprovar / PagamentoController@aprovar.