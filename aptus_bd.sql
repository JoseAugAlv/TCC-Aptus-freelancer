DROP DATABASE IF EXISTS Aptus;
CREATE DATABASE Aptus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Aptus;

-- ============================================================================
-- 1. LOOKUP / REFERÊNCIA
-- ============================================================================

CREATE TABLE perfil (
    id_perfil INT PRIMARY KEY AUTO_INCREMENT,
    perfil VARCHAR(20) UNIQUE NOT NULL
);

INSERT INTO perfil(perfil) VALUES ('Admin'), ('Moderador'), ('Usuario'), ('Master');

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
-- 2. TABELA CORE: USUÁRIO
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
    remember_token VARCHAR(64) NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil),
    FOREIGN KEY (id_moderador_banimento) REFERENCES usuario(id_usuario),
    INDEX idx_remember_token (remember_token)
);

CREATE TABLE login_tentativa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    criado_em DATETIME NOT NULL,
    INDEX idx_email_criado (email, criado_em),
    INDEX idx_ip_criado (ip, criado_em)
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
-- 3. ANÚNCIOS DE SERVIÇO
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
    situacao ENUM('ativo', 'pausado', 'excluido') DEFAULT 'ativo'
        COMMENT 'controlado pelo proprio freelancer (RF06)',
    id_situacao_moderacao INT NOT NULL DEFAULT 1
        COMMENT 'Pendente/Aprovado/Rejeitado - controlado pelo moderador (RF17)',
    motivo_remocao VARCHAR(255) NULL,
    visualizacoes INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
    FOREIGN KEY (id_situacao_moderacao) REFERENCES situacao(id_situacao)
);

CREATE TABLE anuncio_foto (
    id_anuncio_foto INT PRIMARY KEY AUTO_INCREMENT,
    id_anuncio INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio_servico(id_anuncio)
);

-- ============================================================================
-- 4. FAVORITOS, INTERESSES, PAGAMENTOS
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

CREATE TABLE interesse (
    id_interesse INT PRIMARY KEY AUTO_INCREMENT,
    id_anuncio INT NOT NULL,
    id_contratante INT NOT NULL,
    id_freelancer INT NOT NULL
        COMMENT 'redundante com anuncio_servico.id_usuario, denormalizado para facilitar consultas',
    mensagem_inicial TEXT NULL,
    situacao ENUM('pendente', 'ativo', 'concluido', 'cancelado', 'recusado')
        NOT NULL DEFAULT 'pendente',
    data_interesse DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_aceite DATETIME NULL,
    data_recusa DATETIME NULL,
    data_conclusao DATETIME NULL,
    FOREIGN KEY (id_anuncio) REFERENCES anuncio_servico(id_anuncio),
    FOREIGN KEY (id_contratante) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_freelancer) REFERENCES usuario(id_usuario)
);

CREATE TABLE confirmacao_pagamento (
    id_confirmacao INT PRIMARY KEY AUTO_INCREMENT,
    id_interesse INT NOT NULL UNIQUE,

    confirmado_contratante BOOLEAN DEFAULT FALSE,
    valor_informado_contratante DECIMAL(10,2),
    forma_pagamento_contratante ENUM('pix', 'transferencia', 'dinheiro', 'cartao', 'outro'),
    data_pagamento_contratante DATE,
    data_confirmacao_contratante DATETIME,
    observacao_contratante TEXT,

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
-- 5. DISPUTAS, AVALIAÇÕES, MENSAGENS
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
-- 6. DENÚNCIAS, BUSCA, LOGS, NOTIFICAÇÕES, RESET
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

CREATE TABLE busca_log (
    id_busca INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NULL COMMENT 'null se busca de visitante nao logado',
    termo_buscado VARCHAR(150),
    id_categoria INT NULL,
    data_busca DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

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

CREATE TABLE configuracao (
    id_configuracao INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(80) NOT NULL UNIQUE,
    valor TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Valores iniciais (mesmos do Configuracao::getDefaults() no PHP)
INSERT INTO configuracao (chave, valor) VALUES
('site_nome',              'Aptus'),
('site_descricao',         'Conectando Talentos'),
('site_email',             'contato@aptus.com'),
('site_telefone',          '(11) 99999-9999'),
('site_endereco',          'Sao Paulo, SP'),

('upload_max_size',        '5'),
('upload_allow_types',     'jpg,jpeg,png,webp,gif'),

('moderacao_automatica',   '0'),

('sessao_tempo',           '3600'),
('tentativas_login',       '5'),

('email_host',             'smtp.gmail.com'),
('email_port',             '587'),
('email_user',             ''),
('email_pass',             ''),
('email_from_name',        'Aptus'),

('pagamento_pix',          '1'),
('pagamento_transferencia','1'),
('pagamento_dinheiro',     '1'),
('pagamento_cartao',       '0'),

('manutencao',             '0'),
('manutencao_mensagem',    'Sistema em manutencao. Volte em breve.');

INSERT INTO usuario (id_perfil, nome, email, senha, email_verificado, ativo, banido) VALUES
(3, 'Usuário Teste',       'usuario@aptus.com',   '$2y$10$IrRHFrbGBM1ro2gd/S8XKeqUPVcdsa6e6hhgtZW0kG32864jOw3jC', 1, 1, 0),
(2, 'Moderador Teste',     'moderador@aptus.com', '$2y$10$IrRHFrbGBM1ro2gd/S8XKeqUPVcdsa6e6hhgtZW0kG32864jOw3jC', 1, 1, 0),
(1, 'Administrador Teste', 'admin@aptus.com',     '$2y$10$IrRHFrbGBM1ro2gd/S8XKeqUPVcdsa6e6hhgtZW0kG32864jOw3jC', 1, 1, 0),
(4, 'Master Teste',        'master@aptus.com',    '$2y$10$IrRHFrbGBM1ro2gd/S8XKeqUPVcdsa6e6hhgtZW0kG32864jOw3jC', 1, 1, 0);

ALTER TABLE usuario
    ADD COLUMN token_verificacao_expira DATETIME NULL
    AFTER token_verificacao;

-- Índice para acelerar a verificação
CREATE INDEX idx_token_verificacao ON usuario (token_verificacao);

ALTER TABLE usuario ADD COLUMN cep VARCHAR(9) NULL AFTER estado;

-- Expiração do token "Lembrar-me" (usado em AuthController::login e public/index.php)
ALTER TABLE usuario ADD COLUMN remember_token_expira DATETIME NULL AFTER remember_token;
-- =====================================================================
-- Requisitos (alteracoes_desejadas.md): username, endereço completo,
-- privacidade de localização, suspensão, fotos múltiplas, anexos de
-- denúncia e bloqueio entre usuários.
-- =====================================================================
ALTER TABLE usuario
    ADD COLUMN username VARCHAR(30) NULL AFTER nome,
    ADD COLUMN logradouro VARCHAR(150) NULL AFTER cep,
    ADD COLUMN numero VARCHAR(10) NULL AFTER logradouro,
    ADD COLUMN complemento VARCHAR(60) NULL AFTER numero,
    ADD COLUMN bairro VARCHAR(100) NULL AFTER complemento,
    ADD COLUMN privacidade_localizacao ENUM('oculto','cidade','cidade_bairro') NOT NULL DEFAULT 'oculto' AFTER bairro,
    ADD COLUMN suspenso_ate DATETIME NULL AFTER id_moderador_banimento,
    ADD COLUMN motivo_suspensao VARCHAR(255) NULL AFTER suspenso_ate,
    ADD COLUMN id_admin_suspensao INT NULL AFTER motivo_suspensao;

-- usuários já existentes recebem username derivado do e-mail (ajustável no perfil)
UPDATE usuario SET username = LOWER(CONCAT(
    LEFT(REGEXP_REPLACE(SUBSTRING_INDEX(email, '@', 1), '[^A-Za-z0-9_.]', ''), 24), '_', id_usuario))
WHERE username IS NULL;

ALTER TABLE usuario
    MODIFY username VARCHAR(30) NOT NULL,
    ADD UNIQUE KEY uk_usuario_username (username);

CREATE TABLE IF NOT EXISTS denuncia_anexo (
    id_anexo     INT AUTO_INCREMENT PRIMARY KEY,
    id_denuncia  INT NOT NULL,
    arquivo      VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_denuncia_anexo FOREIGN KEY (id_denuncia) REFERENCES denuncia(id_denuncia) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS usuario_bloqueio (
    id_bloqueio   INT AUTO_INCREMENT PRIMARY KEY,
    id_bloqueador INT NOT NULL,
    id_bloqueado  INT NOT NULL,
    data_criacao  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bloqueio_par (id_bloqueador, id_bloqueado),
    KEY idx_bloqueado (id_bloqueado),
    CONSTRAINT fk_bloqueio_de FOREIGN KEY (id_bloqueador) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_bloqueio_para FOREIGN KEY (id_bloqueado) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- A capa (anuncio_servico.foto_capa) passa a ser sempre uma das fotos do anúncio
INSERT INTO anuncio_foto (id_anuncio, arquivo, ordem)
SELECT a.id_anuncio, a.foto_capa, -1
FROM anuncio_servico a
WHERE a.foto_capa IS NOT NULL AND a.foto_capa <> ''
  AND NOT EXISTS (SELECT 1 FROM anuncio_foto f WHERE f.id_anuncio = a.id_anuncio AND f.arquivo = a.foto_capa);

-- Dados de teste: CPFs válidos (dígitos verificadores) e nascimento para as contas seed
UPDATE usuario SET cpf_cnpj = '77706108007', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 1;
UPDATE usuario SET cpf_cnpj = '75764020085', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 2;
UPDATE usuario SET cpf_cnpj = '19966268251', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 3;
UPDATE usuario SET cpf_cnpj = '27715134988', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 4;
UPDATE usuario SET cpf_cnpj = '95333867895', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 5;
UPDATE usuario SET cpf_cnpj = '03161984714', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 6;
UPDATE usuario SET cpf_cnpj = '60553935062', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 7;
UPDATE usuario SET cpf_cnpj = '29515880033', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 8;
UPDATE usuario SET cpf_cnpj = '39279160303', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 9;
UPDATE usuario SET cpf_cnpj = '24481410027', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 10;
UPDATE usuario SET cpf_cnpj = '38035564900', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 11;
UPDATE usuario SET cpf_cnpj = '25174720097', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 12;
UPDATE usuario SET cpf_cnpj = '60640746713', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 13;
UPDATE usuario SET cpf_cnpj = '59255412191', data_nascimento = COALESCE(NULLIF(data_nascimento, '0000-00-00'), '1990-01-01') WHERE id_usuario = 14;

-- Reputação calculada só a partir de avaliações reais: cliente avaliando o
-- prestador em serviço concluído (avaliações em serviços cancelados não contam)
UPDATE usuario u
LEFT JOIN (
    SELECT a.id_avaliado, AVG(a.nota) AS media, COUNT(*) AS total
    FROM avaliacao a
    JOIN interesse i ON i.id_interesse = a.id_interesse
    WHERE i.situacao = 'concluido' AND a.id_avaliado = i.id_freelancer AND a.id_avaliador = i.id_contratante
    GROUP BY a.id_avaliado
) r ON r.id_avaliado = u.id_usuario
SET u.nota_media = COALESCE(ROUND(r.media, 2), 0),
    u.total_avaliacoes = COALESCE(r.total, 0);

-- Capa do anúncio 18 (antes sem foto), gerada como imagem ilustrativa
INSERT INTO anuncio_foto (id_anuncio, arquivo, ordem) SELECT 18, 'anuncio_18_capa.jpg', 0 FROM DUAL WHERE EXISTS (SELECT 1 FROM anuncio_servico WHERE id_anuncio = 18) AND NOT EXISTS (SELECT 1 FROM anuncio_foto WHERE id_anuncio = 18);
UPDATE anuncio_servico SET foto_capa = 'anuncio_18_capa.jpg' WHERE id_anuncio = 18 AND (foto_capa IS NULL OR foto_capa = '');

-- =====================================================================
-- 19/11/2026: vagas simultâneas por anúncio, login com Google e troca de
-- e-mail por código (mesmo conteúdo de atualizacao_banco_2026-11-19.sql)
-- =====================================================================
ALTER TABLE anuncio_servico
    ADD COLUMN max_simultaneos TINYINT UNSIGNED NOT NULL DEFAULT 1
        COMMENT 'clientes atendidos ao mesmo tempo (propostas aceitas em andamento)'
        AFTER preco;

ALTER TABLE usuario
    ADD COLUMN google_id VARCHAR(64) NULL COMMENT 'identificador da conta Google (campo sub do OpenID)' AFTER email,
    ADD COLUMN email_pendente VARCHAR(100) NULL COMMENT 'novo e-mail aguardando confirmação por código' AFTER google_id,
    ADD COLUMN codigo_email_hash CHAR(64) NULL COMMENT 'sha256 do código de 6 dígitos enviado por e-mail' AFTER email_pendente,
    ADD COLUMN codigo_email_expira DATETIME NULL AFTER codigo_email_hash,
    ADD COLUMN codigo_email_tentativas TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER codigo_email_expira,
    ADD UNIQUE KEY uk_usuario_google (google_id);

CREATE INDEX idx_usuario_pendente ON usuario (email_verificado, token_verificacao_expira);

-- 19/11/2026: e-mail da conta Google conectada (pode ser diferente do e-mail do cadastro)
ALTER TABLE usuario
    ADD COLUMN google_email VARCHAR(100) NULL
        COMMENT 'e-mail da conta Google conectada (pode ser diferente do e-mail do cadastro)'
        AFTER google_id;

-- 19/11/2026: confirmação de conclusão do serviço por cada parte
ALTER TABLE interesse
    ADD COLUMN concluido_contratante TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'cliente confirmou que o serviço foi executado' AFTER data_conclusao,
    ADD COLUMN concluido_freelancer TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'prestador confirmou que o serviço foi executado' AFTER concluido_contratante;
