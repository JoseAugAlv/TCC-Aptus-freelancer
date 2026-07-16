CREATE DATABASE RecycleWays;
USE RecycleWays;

-- ============================================================================
-- TABELAS LOOKUP / REFERÊNCIA
-- ============================================================================

CREATE TABLE curso (
    id_curso INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(30) NOT NULL,
    ativo BOOLEAN NOT NULL
);

CREATE TABLE periodo (
    id_periodo INT PRIMARY KEY AUTO_INCREMENT,
    periodo VARCHAR(30) NOT NULL
);
INSERT INTO periodo(periodo) VALUES ('Manhã'), ('Tarde'), ('Noite');

CREATE TABLE perfil (
    id_perfil INT PRIMARY KEY AUTO_INCREMENT,
    perfil VARCHAR(20) UNIQUE NOT NULL
);
INSERT INTO perfil(perfil) VALUES ('Admin'), ('Operador'), ('Participante'), ('Fundo'), ('Master');

CREATE TABLE situacao (
    id_situacao INT PRIMARY KEY AUTO_INCREMENT,
    situacao VARCHAR(20) NOT NULL UNIQUE
);
INSERT INTO situacao(situacao) VALUES ('Pendente'), ('Aprovado'), ('Rejeitado'), ('Cancelado');

-- ============================================================================
-- TABELA CORE: USUÁRIO
-- ============================================================================

CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(15),
    data_nascimento DATE,
    ativo BOOLEAN DEFAULT TRUE,
    token_verificacao VARCHAR(64) NULL,
    email_verificado BOOLEAN DEFAULT FALSE,
    data_verificacao DATETIME NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================================
-- TABELA PROJETOS
-- ============================================================================

CREATE TABLE projeto (
    id_projeto INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    destino VARCHAR(150),
    meta_total_reais DECIMAL(10, 2),
    percentual_reciclavel INT DEFAULT 51,
    meta_por_usuario DECIMAL(10, 2),
    total_arrecadado DECIMAL(10,2) DEFAULT 0,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    slug VARCHAR(50) UNIQUE,
    situacao ENUM('planejamento', 'ativo', 'finalizado') DEFAULT 'planejamento',
    publicado BOOLEAN DEFAULT TRUE,
    imagem_capa VARCHAR(255),
    descricao_detalhada TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- PREÇOS
-- ============================================================================

CREATE TABLE preco_oleo (
    id_preco INT PRIMARY KEY AUTO_INCREMENT,
    valor_litro DECIMAL(10,2) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    observacao TEXT
);

CREATE TABLE preco_oleo_credito (
    id_preco_credito INT PRIMARY KEY AUTO_INCREMENT,
    id_projeto INT NOT NULL,
    valor_litro DECIMAL(10,2) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    observacao TEXT,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)
);

-- ============================================================================
-- VÍNCULO USUÁRIO x PROJETO
-- ============================================================================

CREATE TABLE usuario_projeto (
    id_usuario_projeto INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_projeto INT,
    id_perfil INT NOT NULL,
    id_situacao INT NOT NULL DEFAULT 1,
    total_oleo DECIMAL(10,2) DEFAULT 0,
    total_materiais DECIMAL(10,2) DEFAULT 0,
    total_dinheiro DECIMAL(10,2) DEFAULT 0,
    total_arrecadado DECIMAL(10,2) DEFAULT 0,
    ano_escolar_na_epoca INT,
    id_curso_na_epoca INT,
    id_periodo_na_epoca INT,
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao DATETIME,
    id_usuario_aprovador INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_usuario_aprovador) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_curso_na_epoca) REFERENCES curso(id_curso),
    FOREIGN KEY (id_periodo_na_epoca) REFERENCES periodo(id_periodo)
);

-- ============================================================================
-- ESPECIALIZAÇÕES
-- ============================================================================

CREATE TABLE professor (
    id_professor INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE coordenador (
    id_coordenador INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE aluno (
    id_usuario INT PRIMARY KEY,
    rm CHAR(5) UNIQUE NOT NULL,
    ano_escolar INT,
    id_curso INT,
    id_periodo INT,
    ano_ingresso INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso),
    FOREIGN KEY (id_periodo) REFERENCES periodo(id_periodo)
);

CREATE TABLE ocorrencia_aluno (
    id_ocorrencia INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    descricao TEXT,
    data_ocorrencia DATE NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_aluno) REFERENCES aluno(id_usuario)
);

-- ============================================================================
-- ENTREGAS E TRANSFERÊNCIAS
-- ============================================================================

CREATE TABLE entrega_oleo (
    id_entrega_oleo INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_projeto INT NOT NULL,
    qtd_entregue DECIMAL(10,2) NOT NULL,
    qtd_filtrada DECIMAL(10,2),
    valor DECIMAL(10,2),
    id_situacao INT,
    observacao TEXT,
    data_entrega DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_validacao DATETIME,
    id_usuario_validador INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_usuario_validador) REFERENCES usuario(id_usuario)
);

CREATE TABLE nota_material (
    id_nota INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_projeto INT NOT NULL,
    numero_nota VARCHAR(30),
    valor_total DECIMAL(10,2) NOT NULL,
    comprovante VARCHAR(255),
    id_situacao INT,
    observacao TEXT,
    data_entrega DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_validacao DATETIME,
    id_usuario_validador INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_usuario_validador) REFERENCES usuario(id_usuario)
);

CREATE TABLE pagamento (
    id_pagamento INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_projeto INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    comprovante VARCHAR(255),
    id_situacao INT,
    observacao TEXT,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_validacao DATETIME,
    id_usuario_validador INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_usuario_validador) REFERENCES usuario(id_usuario)
);

CREATE TABLE transferencia_credito (
    id_transferencia INT PRIMARY KEY AUTO_INCREMENT,
    id_projeto INT NOT NULL,
    id_usuario_origem INT NOT NULL,
    id_usuario_destino INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('transferencia', 'doacao') NOT NULL,
    valor_oleo DECIMAL(10,2) DEFAULT 0,
    valor_materiais DECIMAL(10,2) DEFAULT 0,
    valor_dinheiro DECIMAL(10,2) DEFAULT 0,
    id_situacao INT,
    motivo TEXT,
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao DATETIME,
    id_usuario_aprovador INT,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_usuario_origem) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_usuario_destino) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_usuario_aprovador) REFERENCES usuario(id_usuario)
);

CREATE TABLE venda_oleo (
    id_venda INT PRIMARY KEY AUTO_INCREMENT,
    id_projeto INT NOT NULL,
    id_preco INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    valor_litro_na_venda DECIMAL(10,2),
    valor_total DECIMAL(10,2),
    data_venda DATE NOT NULL,
    observacao TEXT,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_preco) REFERENCES preco_oleo(id_preco)
);

-- ============================================================================
-- MÍDIAS
-- ============================================================================

CREATE TABLE foto_video_projeto (
    id_foto_video INT PRIMARY KEY AUTO_INCREMENT,
    id_projeto INT NOT NULL,
    tipo ENUM('foto', 'video') NOT NULL,
    titulo VARCHAR(150),
    descricao TEXT,
    arquivo VARCHAR(255),
    url_video VARCHAR(255),
    ordem INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_situacao INT DEFAULT 1,
    id_usuario_envio INT,
    data_aprovacao DATETIME,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto),
    FOREIGN KEY (id_situacao) REFERENCES situacao(id_situacao),
    FOREIGN KEY (id_usuario_envio) REFERENCES usuario(id_usuario)
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
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE notificacao (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_projeto INT,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensagem TEXT,
    lida BOOLEAN DEFAULT FALSE,
    tabela_origem VARCHAR(50),
    registro_id INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)
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
-- TRIGGERS
-- ============================================================================

-- ============================================================================
-- TRIGGER: Atualiza totais quando entrega é APROVADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_entrega_oleo_apos_update
AFTER UPDATE ON entrega_oleo
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        UPDATE usuario_projeto
        SET total_oleo = total_oleo + NEW.qtd_entregue,
            total_arrecadado = total_arrecadado + NEW.valor
        WHERE id_usuario = NEW.id_usuario AND id_projeto = NEW.id_projeto;
        
        UPDATE projeto SET total_arrecadado = total_arrecadado + NEW.valor WHERE id_projeto = NEW.id_projeto;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Cálculo automático do valor creditado ao aluno
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_entrega_oleo_calcula_valor
BEFORE INSERT ON entrega_oleo
FOR EACH ROW
BEGIN
    DECLARE v_valor_credito DECIMAL(10,2);
    DECLARE v_qtd_util DECIMAL(10,2);
    
    SET v_qtd_util = NEW.qtd_entregue - COALESCE(NEW.qtd_filtrada, 0);
    
    IF v_qtd_util <= 0 THEN
        SET NEW.valor = 0;
    ELSE
        SELECT valor_litro INTO v_valor_credito
        FROM preco_oleo_credito
        WHERE id_projeto = NEW.id_projeto
          AND data_inicio <= CURDATE()
          AND (data_fim IS NULL OR data_fim >= CURDATE())
        ORDER BY data_inicio DESC LIMIT 1;
        
        IF v_valor_credito IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erro: preço de crédito do óleo não configurado para este projeto.';
        END IF;
        
        SET NEW.valor = v_qtd_util * v_valor_credito;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Atualiza totais de material quando nota é APROVADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_nota_material_apos_update
AFTER UPDATE ON nota_material
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        UPDATE usuario_projeto
        SET total_materiais = total_materiais + NEW.valor_total,
            total_arrecadado = total_arrecadado + NEW.valor_total
        WHERE id_usuario = NEW.id_usuario AND id_projeto = NEW.id_projeto;
        
        UPDATE projeto SET total_arrecadado = total_arrecadado + NEW.valor_total WHERE id_projeto = NEW.id_projeto;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Atualiza totais de dinheiro quando pagamento é APROVADO
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_pagamento_apos_update
AFTER UPDATE ON pagamento
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        UPDATE usuario_projeto
        SET total_dinheiro = total_dinheiro + NEW.valor,
            total_arrecadado = total_arrecadado + NEW.valor
        WHERE id_usuario = NEW.id_usuario AND id_projeto = NEW.id_projeto;
        
        UPDATE projeto SET total_arrecadado = total_arrecadado + NEW.valor WHERE id_projeto = NEW.id_projeto;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Validação de preço antes de venda de óleo
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_venda_oleo_validacao
BEFORE INSERT ON venda_oleo
FOR EACH ROW
BEGIN
    DECLARE v_valor DECIMAL(10,2);
    SELECT valor_litro INTO v_valor FROM preco_oleo WHERE id_preco = NEW.id_preco;
    
    IF v_valor IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erro: preço do óleo não encontrado.';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Cálculo automático do valor total em venda de óleo
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_venda_oleo_calcula_total
BEFORE INSERT ON venda_oleo
FOR EACH ROW
BEGIN
    DECLARE v_valor DECIMAL(10,2);
    SELECT valor_litro INTO v_valor FROM preco_oleo WHERE id_preco = NEW.id_preco;
    
    SET NEW.valor_litro_na_venda = v_valor;
    SET NEW.valor_total = NEW.quantidade * v_valor;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Atualiza saldos quando transferência é APROVADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_transferencia_credito_apos_aprovacao
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        UPDATE usuario_projeto
        SET total_oleo = total_oleo - NEW.valor,
            total_arrecadado = total_arrecadado - NEW.valor
        WHERE id_usuario = NEW.id_usuario_origem AND id_projeto = NEW.id_projeto;
        
        UPDATE usuario_projeto
        SET total_oleo = total_oleo + NEW.valor,
            total_arrecadado = total_arrecadado + NEW.valor
        WHERE id_usuario = NEW.id_usuario_destino AND id_projeto = NEW.id_projeto;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Inscrição no Projeto APROVADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_usuario_projeto_aprovado
AFTER UPDATE ON usuario_projeto
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'inscricao_aprovada',
            'Você foi aprovado para participar do projeto!',
            'Sua inscrição no projeto foi aprovada. Você já pode começar a contribuir.',
            'usuario_projeto',
            NEW.id_usuario_projeto,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Inscrição no Projeto REJEITADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_usuario_projeto_rejeitado
AFTER UPDATE ON usuario_projeto
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'inscricao_rejeitada',
            'Sua inscrição foi rejeitada',
            'Sua inscrição no projeto não foi aprovada. Consulte o administrador para mais detalhes.',
            'usuario_projeto',
            NEW.id_usuario_projeto,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Entrega de Óleo APROVADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_entrega_oleo_aprovada
AFTER UPDATE ON entrega_oleo
FOR EACH ROW
BEGIN
    DECLARE v_qtd_util DECIMAL(10,2);
    SET v_qtd_util = NEW.qtd_entregue - COALESCE(NEW.qtd_filtrada, 0);
    
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'entrega_oleo_aprovada',
            'Entrega de óleo aprovada!',
            CONCAT('Sua entrega de ', v_qtd_util, ' L (', NEW.qtd_entregue, ' L entregues - ', COALESCE(NEW.qtd_filtrada, 0), ' L filtrados) foi aprovada. Você recebeu R$ ', FORMAT(NEW.valor, 2), ' de crédito.'),
            'entrega_oleo',
            NEW.id_entrega_oleo,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Entrega de Óleo REJEITADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_entrega_oleo_rejeitada
AFTER UPDATE ON entrega_oleo
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'entrega_oleo_rejeitada',
            'Entrega de óleo foi rejeitada',
            CONCAT('Sua entrega de ', NEW.qtd_entregue, ' L foi rejeitada. Motivo: ', COALESCE(NEW.observacao, 'Não especificado')),
            'entrega_oleo',
            NEW.id_entrega_oleo,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Entrega de Óleo CANCELADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_entrega_oleo_cancelada
AFTER UPDATE ON entrega_oleo
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 4 AND OLD.id_situacao <> 4 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'entrega_oleo_cancelada',
            'Entrega de óleo foi cancelada',
            CONCAT('Sua entrega de ', NEW.qtd_entregue, ' L foi cancelada. ', COALESCE(NEW.observacao, '')),
            'entrega_oleo',
            NEW.id_entrega_oleo,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Entrega de Óleo CRIADA (Pendente)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_entrega_oleo_criada
AFTER INSERT ON entrega_oleo
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
    VALUES (
        NEW.id_usuario,
        NEW.id_projeto,
        'entrega_oleo_pendente',
        'Nova entrega de óleo registrada!',
        CONCAT('Sua entrega de ', NEW.qtd_entregue, ' L foi registrada. Aguarde a validação do operador.'),
        'entrega_oleo',
        NEW.id_entrega_oleo,
        FALSE
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nota de Material APROVADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nota_material_aprovada
AFTER UPDATE ON nota_material
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'nota_material_aprovada',
            'Nota de material aprovada!',
            CONCAT('Sua nota de material foi aprovada. Você recebeu R$ ', FORMAT(NEW.valor_total, 2), ' de crédito.'),
            'nota_material',
            NEW.id_nota,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nota de Material REJEITADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nota_material_rejeitada
AFTER UPDATE ON nota_material
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'nota_material_rejeitada',
            'Nota de material foi rejeitada',
            CONCAT('Sua nota de material foi rejeitada. Motivo: ', COALESCE(NEW.observacao, 'Não especificado')),
            'nota_material',
            NEW.id_nota,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nota de Material CANCELADA
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nota_material_cancelada
AFTER UPDATE ON nota_material
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 4 AND OLD.id_situacao <> 4 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'nota_material_cancelada',
            'Nota de material foi cancelada',
            CONCAT('Sua nota de material foi cancelada. ', COALESCE(NEW.observacao, '')),
            'nota_material',
            NEW.id_nota,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nota de Material CRIADA (Pendente)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nota_material_criada
AFTER INSERT ON nota_material
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
    VALUES (
        NEW.id_usuario,
        NEW.id_projeto,
        'nota_material_pendente',
        'Nova nota de material registrada!',
        CONCAT('Sua nota de material no valor de R$ ', FORMAT(NEW.valor_total, 2), ' foi registrada. Aguarde a validação do operador.'),
        'nota_material',
        NEW.id_nota,
        FALSE
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Pagamento em Dinheiro APROVADO
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_pagamento_aprovado
AFTER UPDATE ON pagamento
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'pagamento_aprovado',
            'Pagamento aprovado!',
            CONCAT('Seu pagamento de R$ ', FORMAT(NEW.valor, 2), ' foi aprovado e creditado na sua conta.'),
            'pagamento',
            NEW.id_pagamento,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Pagamento em Dinheiro REJEITADO
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_pagamento_rejeitado
AFTER UPDATE ON pagamento
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'pagamento_rejeitado',
            'Pagamento foi rejeitado',
            CONCAT('Seu pagamento de R$ ', FORMAT(NEW.valor, 2), ' foi rejeitado. Motivo: ', COALESCE(NEW.observacao, 'Não especificado')),
            'pagamento',
            NEW.id_pagamento,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Pagamento em Dinheiro CANCELADO
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_pagamento_cancelado
AFTER UPDATE ON pagamento
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 4 AND OLD.id_situacao <> 4 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario,
            NEW.id_projeto,
            'pagamento_cancelado',
            'Pagamento foi cancelado',
            CONCAT('Seu pagamento de R$ ', FORMAT(NEW.valor, 2), ' foi cancelado. ', COALESCE(NEW.observacao, '')),
            'pagamento',
            NEW.id_pagamento,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Pagamento CRIADO (Pendente)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_pagamento_criado
AFTER INSERT ON pagamento
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
    VALUES (
        NEW.id_usuario,
        NEW.id_projeto,
        'pagamento_pendente',
        'Novo pagamento registrado!',
        CONCAT('Seu pagamento de R$ ', FORMAT(NEW.valor, 2), ' foi registrado. Aguarde a validação do operador.'),
        'pagamento',
        NEW.id_pagamento,
        FALSE
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Transferência APROVADA (REMETENTE)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_transferencia_aprovada_remetente
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_origem,
            NEW.id_projeto,
            'transferencia_enviada',
            'Transferência realizada com sucesso!',
            CONCAT('Você transferiu ', NEW.valor, ' L para ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_destino), '.'),
            'transferencia_credito',
            NEW.id_transferencia,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Transferência APROVADA (DESTINATÁRIO)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_transferencia_aprovada_destinatario
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_destino,
            NEW.id_projeto,
            'transferencia_recebida',
            'Você recebeu uma transferência!',
            CONCAT('Você recebeu ', NEW.valor, ' L de ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_origem), '.'),
            'transferencia_credito',
            NEW.id_transferencia,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Transferência REJEITADA (REMETENTE)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_transferencia_rejeitada_remetente
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_origem,
            NEW.id_projeto,
            'transferencia_rejeitada',
            'Sua transferência foi rejeitada',
            CONCAT('Sua transferência de ', NEW.valor, ' L foi rejeitada.'),
            'transferencia_credito',
            NEW.id_transferencia,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nova Transferência Pendente para Admin
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nova_transferencia_pendente
AFTER INSERT ON transferencia_credito
FOR EACH ROW
BEGIN
    DECLARE id_admin INT;
    SET id_admin = 1;
    
    INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
    VALUES (
        id_admin,
        NEW.id_projeto,
        'nova_transferencia_pendente',
        'Nova transferência aguardando aprovação!',
        CONCAT((SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_origem), ' solicitou transferência de ', NEW.valor, ' L para ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_destino), '.'),
        'transferencia_credito',
        NEW.id_transferencia,
        FALSE
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Imagem Enviada (Pendente)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_imagem_enviada
AFTER INSERT ON foto_video_projeto
FOR EACH ROW
BEGIN
    IF NEW.id_usuario_envio IS NOT NULL AND NEW.id_usuario_envio != 1 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_envio,
            NEW.id_projeto,
            'imagem_pendente',
            'Imagem enviada para aprovação!',
            CONCAT('Sua imagem "', COALESCE(NEW.titulo, 'Sem título'), '" foi enviada e aguarda aprovação do administrador.'),
            'foto_video_projeto',
            NEW.id_foto_video,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Imagem Aprovada
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_imagem_aprovada
AFTER UPDATE ON foto_video_projeto
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao <> 2 THEN
        IF NEW.id_usuario_envio IS NOT NULL AND NEW.id_usuario_envio != 1 THEN
            INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
            VALUES (
                NEW.id_usuario_envio,
                NEW.id_projeto,
                'imagem_aprovada',
                'Sua imagem foi aprovada!',
                CONCAT('Sua imagem "', COALESCE(NEW.titulo, 'Sem título'), '" foi aprovada e já está disponível na galeria do projeto.'),
                'foto_video_projeto',
                NEW.id_foto_video,
                FALSE
            );
        END IF;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Imagem Rejeitada
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_imagem_rejeitada
AFTER UPDATE ON foto_video_projeto
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        IF NEW.id_usuario_envio IS NOT NULL AND NEW.id_usuario_envio != 1 THEN
            INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
            VALUES (
                NEW.id_usuario_envio,
                NEW.id_projeto,
                'imagem_rejeitada',
                'Sua imagem foi rejeitada',
                CONCAT('Sua imagem "', COALESCE(NEW.titulo, 'Sem título'), '" foi rejeitada pelo administrador.'),
                'foto_video_projeto',
                NEW.id_foto_video,
                FALSE
            );
        END IF;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nova Imagem Pendente para Admin
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nova_imagem_pendente
AFTER INSERT ON foto_video_projeto
FOR EACH ROW
BEGIN
    DECLARE id_admin INT;
    SET id_admin = 1;
    
    IF NEW.id_usuario_envio IS NOT NULL AND NEW.id_usuario_envio != 1 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            id_admin,
            NEW.id_projeto,
            'nova_imagem_pendente',
            'Nova imagem aguardando aprovação!',
            CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_envio), ' enviou uma nova imagem para o projeto. Acesse a área de aprovações.'),
            'foto_video_projeto',
            NEW.id_foto_video,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Imagem Excluída
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_imagem_excluida
AFTER DELETE ON foto_video_projeto
FOR EACH ROW
BEGIN
    IF OLD.id_usuario_envio IS NOT NULL AND OLD.id_usuario_envio != 1 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            OLD.id_usuario_envio,
            OLD.id_projeto,
            'imagem_excluida',
            'Sua imagem foi removida',
            CONCAT('Sua imagem "', COALESCE(OLD.titulo, 'Sem título'), '" foi removida da galeria pelo administrador.'),
            'foto_video_projeto',
            OLD.id_foto_video,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nova Entrega Pendente para Operadores
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nova_entrega_pendente
AFTER INSERT ON entrega_oleo
FOR EACH ROW
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE id_operador INT;
    DECLARE cur_operadores CURSOR FOR 
        SELECT id_usuario FROM usuario_projeto 
        WHERE id_projeto = NEW.id_projeto AND id_perfil = 2;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur_operadores;
    
    read_loop: LOOP
        FETCH cur_operadores INTO id_operador;
        IF done THEN LEAVE read_loop; END IF;
        
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            id_operador,
            NEW.id_projeto,
            'nova_entrega_pendente',
            'Nova entrega aguardando validação!',
            CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario), ' fez uma nova entrega de ', NEW.qtd_entregue, ' L. Aguardando validação.'),
            'entrega_oleo',
            NEW.id_entrega_oleo,
            FALSE
        );
    END LOOP;
    
    CLOSE cur_operadores;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Senha alterada
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_senha_alterada
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    IF OLD.senha <> NEW.senha THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, lida)
        VALUES (
            NEW.id_usuario,
            NULL,
            'senha_alterada',
            'Sua senha foi alterada',
            'Sua senha foi alterada com sucesso. Se você não reconhece esta alteração, entre em contato com o administrador.',
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- LOG - INSERÇÕES (INSERT)
-- ============================================================================

-- ============================================================================
-- TRIGGER: Log - Inserção em usuario
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_usuario_insert
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'usuario',
        NEW.id_usuario,
        CONCAT('Novo usuario criado: ', NEW.nome, ' (', NEW.email, ')'),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Inserção em projeto
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_projeto_insert
AFTER INSERT ON projeto
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'projeto',
        NEW.id_projeto,
        CONCAT('Novo projeto criado: ', NEW.nome, ' (', NEW.slug, ')'),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Inserção em entrega_oleo
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_entrega_oleo_insert
AFTER INSERT ON entrega_oleo
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'entrega_oleo',
        NEW.id_entrega_oleo,
        CONCAT('Nova entrega de oleo: ', NEW.qtd_entregue, ' L - Projeto: ', NEW.id_projeto, ' - Usuario: ', NEW.id_usuario),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Inserção em nota_material
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_nota_material_insert
AFTER INSERT ON nota_material
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'nota_material',
        NEW.id_nota,
        CONCAT('Nova nota de material: R$ ', NEW.valor_total, ' - Projeto: ', NEW.id_projeto, ' - Usuario: ', NEW.id_usuario),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Inserção em pagamento
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_pagamento_insert
AFTER INSERT ON pagamento
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'pagamento',
        NEW.id_pagamento,
        CONCAT('Novo pagamento: R$ ', NEW.valor, ' - Projeto: ', NEW.id_projeto, ' - Usuario: ', NEW.id_usuario),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Inserção em transferencia_credito
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_transferencia_credito_insert
AFTER INSERT ON transferencia_credito
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'transferencia_credito',
        NEW.id_transferencia,
        CONCAT('Nova transferencia: ', NEW.valor, ' L - De: ', NEW.id_usuario_origem, ' Para: ', NEW.id_usuario_destino, ' - Projeto: ', NEW.id_projeto),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Inserção em venda_oleo
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_venda_oleo_insert
AFTER INSERT ON venda_oleo
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'INSERT',
        'venda_oleo',
        NEW.id_venda,
        CONCAT('Nova venda de oleo: ', NEW.quantidade, ' L - R$ ', NEW.valor_total, ' - Projeto: ', NEW.id_projeto),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- LOG - ATUALIZAÇÕES (UPDATE)
-- ============================================================================

-- ============================================================================
-- TRIGGER: Log - Atualização em usuario_projeto (mudança de perfil/projeto)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_usuario_projeto_update
AFTER UPDATE ON usuario_projeto
FOR EACH ROW
BEGIN
    IF OLD.id_perfil <> NEW.id_perfil OR OLD.id_projeto <> NEW.id_projeto OR OLD.id_situacao <> NEW.id_situacao THEN
        INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
        VALUES (
            'UPDATE',
            'usuario_projeto',
            NEW.id_usuario_projeto,
            CONCAT('Usuario: ', NEW.id_usuario, ' | Perfil: ', OLD.id_perfil, ' -> ', NEW.id_perfil, ' | Projeto: ', OLD.id_projeto, ' -> ', NEW.id_projeto, ' | Situacao: ', OLD.id_situacao, ' -> ', NEW.id_situacao),
            @usuario_log
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Atualização em entrega_oleo (aprovação/rejeição)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_entrega_oleo_update
AFTER UPDATE ON entrega_oleo
FOR EACH ROW
BEGIN
    IF OLD.id_situacao <> NEW.id_situacao THEN
        INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
        VALUES (
            'UPDATE',
            'entrega_oleo',
            NEW.id_entrega_oleo,
            CONCAT('Entrega: ', NEW.id_entrega_oleo, ' | Situacao: ', OLD.id_situacao, ' -> ', NEW.id_situacao, ' | Usuario: ', NEW.id_usuario, ' | Validador: ', NEW.id_usuario_validador),
            @usuario_log
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Atualização em nota_material (aprovação/rejeição)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_nota_material_update
AFTER UPDATE ON nota_material
FOR EACH ROW
BEGIN
    IF OLD.id_situacao <> NEW.id_situacao THEN
        INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
        VALUES (
            'UPDATE',
            'nota_material',
            NEW.id_nota,
            CONCAT('Nota: ', NEW.id_nota, ' | Situacao: ', OLD.id_situacao, ' -> ', NEW.id_situacao, ' | Usuario: ', NEW.id_usuario, ' | Validador: ', NEW.id_usuario_validador),
            @usuario_log
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Atualização em pagamento (aprovação/rejeição)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_pagamento_update
AFTER UPDATE ON pagamento
FOR EACH ROW
BEGIN
    IF OLD.id_situacao <> NEW.id_situacao THEN
        INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
        VALUES (
            'UPDATE',
            'pagamento',
            NEW.id_pagamento,
            CONCAT('Pagamento: ', NEW.id_pagamento, ' | Situacao: ', OLD.id_situacao, ' -> ', NEW.id_situacao, ' | Usuario: ', NEW.id_usuario, ' | Validador: ', NEW.id_usuario_validador),
            @usuario_log
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Atualização em transferencia_credito (aprovação/rejeição)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_transferencia_credito_update
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF OLD.id_situacao <> NEW.id_situacao THEN
        INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
        VALUES (
            'UPDATE',
            'transferencia_credito',
            NEW.id_transferencia,
            CONCAT('Transferencia: ', NEW.id_transferencia, ' | Situacao: ', OLD.id_situacao, ' -> ', NEW.id_situacao, ' | Aprovador: ', NEW.id_usuario_aprovador),
            @usuario_log
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- LOG - EXCLUSÕES (DELETE)
-- ============================================================================

-- ============================================================================
-- TRIGGER: Log - Exclusão em usuario
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_usuario_delete
BEFORE DELETE ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'usuario',
        OLD.id_usuario,
        CONCAT('Usuario excluido: ', OLD.nome, ' (', OLD.email, ')'),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Exclusão em projeto
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_projeto_delete
BEFORE DELETE ON projeto
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'projeto',
        OLD.id_projeto,
        CONCAT('Projeto excluido: ', OLD.nome, ' (', OLD.slug, ')'),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Exclusão em entrega_oleo
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_entrega_oleo_delete
BEFORE DELETE ON entrega_oleo
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'entrega_oleo',
        OLD.id_entrega_oleo,
        CONCAT('Entrega excluida: ', OLD.id_entrega_oleo, ' - Usuario: ', OLD.id_usuario, ' - Projeto: ', OLD.id_projeto),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Exclusão em nota_material
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_nota_material_delete
BEFORE DELETE ON nota_material
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'nota_material',
        OLD.id_nota,
        CONCAT('Nota excluida: ', OLD.id_nota, ' - Usuario: ', OLD.id_usuario, ' - Projeto: ', OLD.id_projeto, ' - Valor: R$ ', OLD.valor_total),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Exclusão em pagamento
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_pagamento_delete
BEFORE DELETE ON pagamento
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'pagamento',
        OLD.id_pagamento,
        CONCAT('Pagamento excluido: ', OLD.id_pagamento, ' - Usuario: ', OLD.id_usuario, ' - Projeto: ', OLD.id_projeto, ' - Valor: R$ ', OLD.valor),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Exclusão em transferencia_credito
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_transferencia_credito_delete
BEFORE DELETE ON transferencia_credito
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'transferencia_credito',
        OLD.id_transferencia,
        CONCAT('Transferencia excluida: ', OLD.id_transferencia, ' - De: ', OLD.id_usuario_origem, ' Para: ', OLD.id_usuario_destino, ' - Valor: ', OLD.valor, ' L'),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Log - Exclusão em venda_oleo
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_log_venda_oleo_delete
BEFORE DELETE ON venda_oleo
FOR EACH ROW
BEGIN
    INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, id_usuario)
    VALUES (
        'DELETE',
        'venda_oleo',
        OLD.id_venda,
        CONCAT('Venda excluida: ', OLD.id_venda, ' - Projeto: ', OLD.id_projeto, ' - Quantidade: ', OLD.quantidade, ' L - Total: R$ ', OLD.valor_total),
        @usuario_log
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Transferência CANCELADA (REMETENTE)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_transferencia_cancelada_remetente
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 4 AND OLD.id_situacao <> 4 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_origem,
            NEW.id_projeto,
            'transferencia_cancelada',
            'Sua transferência foi cancelada',
            CONCAT('Sua transferência de ', NEW.valor, ' L para ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_destino), ' foi cancelada. Motivo: ', COALESCE(NEW.motivo, 'Não especificado')),
            'transferencia_credito',
            NEW.id_transferencia,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Transferência CANCELADA (DESTINATÁRIO)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_transferencia_cancelada_destinatario
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 4 AND OLD.id_situacao <> 4 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_destino,
            NEW.id_projeto,
            'transferencia_recebida_cancelada',
            'Transferência recebida foi cancelada',
            CONCAT('A transferência de ', NEW.valor, ' L de ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_origem), ' foi cancelada.'),
            'transferencia_credito',
            NEW.id_transferencia,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Transferência REJEITADA (DESTINATÁRIO)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_transferencia_rejeitada_destinatario
AFTER UPDATE ON transferencia_credito
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 3 AND OLD.id_situacao <> 3 THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            NEW.id_usuario_destino,
            NEW.id_projeto,
            'transferencia_recebida_rejeitada',
            'Transferência recebida foi rejeitada',
            CONCAT('A transferência de ', NEW.valor, ' L de ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario_origem), ' foi rejeitada.'),
            'transferencia_credito',
            NEW.id_transferencia,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Novo Usuário Cadastrado (para Admin)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_novo_usuario_admin
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    DECLARE id_admin INT;
    SET id_admin = 1; -- ID do administrador do sistema
    
    -- Notifica o admin sobre novo cadastro
    INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
    VALUES (
        id_admin,
        NULL,
        'novo_usuario_cadastrado',
        'Novo usuário cadastrado no sistema!',
        CONCAT('O usuário "', NEW.nome, '" (', NEW.email, ') acabou de se cadastrar no sistema.'),
        'usuario',
        NEW.id_usuario,
        FALSE
    );
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Usuário Atualizado (para Admin)
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_usuario_alterado_admin
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    DECLARE id_admin INT;
    SET id_admin = 1;
    
    -- Só notifica se mudou dados importantes (exceto senha)
    IF (OLD.nome <> NEW.nome OR OLD.email <> NEW.email OR OLD.telefone <> NEW.telefone OR OLD.ativo <> NEW.ativo) THEN
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            id_admin,
            NULL,
            'usuario_alterado',
            'Usuário alterou seus dados',
            CONCAT('O usuário "', NEW.nome, '" (', NEW.email, ') atualizou seus dados cadastrais.'),
            'usuario',
            NEW.id_usuario,
            FALSE
        );
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Validação - Impede auto-transferência
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_transferencia_valida_auto
BEFORE INSERT ON transferencia_credito
FOR EACH ROW
BEGIN
    -- Impede que o usuário transfira para si mesmo
    IF NEW.id_usuario_origem = NEW.id_usuario_destino THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Erro: Não é possível transferir crédito para si mesmo.';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Validação - Verifica saldo suficiente antes da transferência
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_transferencia_valida_saldo
BEFORE INSERT ON transferencia_credito
FOR EACH ROW
BEGIN
    DECLARE v_saldo DECIMAL(10,2);
    
    SELECT total_arrecadado INTO v_saldo
    FROM usuario_projeto
    WHERE id_usuario = NEW.id_usuario_origem
      AND id_projeto = NEW.id_projeto;
    
    IF v_saldo < NEW.valor THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Erro: Saldo insuficiente para realizar a transferência.';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- TRIGGER: Notificação - Nova Inscrição Pendente para Admin/Operadores
-- ============================================================================
DELIMITER $$
CREATE TRIGGER trg_notif_nova_inscricao_pendente
AFTER INSERT ON usuario_projeto
FOR EACH ROW
BEGIN
    DECLARE id_admin INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE id_operador INT;
    DECLARE cur_operadores CURSOR FOR 
        SELECT id_usuario 
        FROM usuario_projeto 
        WHERE id_projeto = NEW.id_projeto 
        AND id_perfil IN (1, 2); -- Admin e Operador
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET id_admin = 1;
    
    -- Notifica Admin (fixo)
    INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
    VALUES (
        id_admin,
        NEW.id_projeto,
        'nova_inscricao_pendente',
        'Nova inscrição aguardando aprovação!',
        CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario), ' solicitou inscrição no projeto. Aguardando aprovação.'),
        'usuario_projeto',
        NEW.id_usuario_projeto,
        FALSE
    );
    
    -- Notifica todos os operadores do projeto
    OPEN cur_operadores;
    
    read_loop: LOOP
        FETCH cur_operadores INTO id_operador;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT INTO notificacao (id_usuario, id_projeto, tipo, titulo, mensagem, tabela_origem, registro_id, lida)
        VALUES (
            id_operador,
            NEW.id_projeto,
            'nova_inscricao_pendente',
            'Nova inscrição aguardando aprovação!',
            CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario), ' solicitou inscrição no projeto.'),
            'usuario_projeto',
            NEW.id_usuario_projeto,
            FALSE
        );
    END LOOP;
    
    CLOSE cur_operadores;
END$$
DELIMITER ;

-- ============================================================================
-- DADOS INICIAIS
-- ============================================================================

INSERT INTO curso(nome, ativo) VALUES ('Eletrônica', TRUE), ('Informática', TRUE), ('Administração', TRUE);

INSERT INTO projeto (nome, slug, descricao, destino, meta_total_reais, percentual_reciclavel, meta_por_usuario, data_inicio, data_fim, situacao, publicado, descricao_detalhada) 
VALUES (
    'Projeto Olimpia 2026',
    'projeto-olimpia-2026',
    'Projeto de arrecadação de recicláveis e óleo.',
    'Olímpia - SP',
    25000.00,
    51,
    500.00,
    '2026-02-01',
    '2026-11-30',
    'ativo',
    TRUE,
    'Projeto destinado à viagem anual da escola.'
);

INSERT INTO preco_oleo(valor_litro, data_inicio) VALUES (2.10, '2026-02-01');
INSERT INTO preco_oleo_credito(id_projeto, valor_litro, data_inicio) VALUES (1, 2.00, '2026-02-01');

-- Usuários
INSERT INTO usuario (nome, email, senha, telefone, data_nascimento, ativo, email_verificado) VALUES
('Administrador do Sistema', 'eumumueuemu@gmail.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '11999990001', '1980-01-01', TRUE, TRUE),
('Operador do Projeto', 'operador@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '11999990002', '1985-05-10', TRUE, TRUE),
('Aluno Colaborador', 'aluno@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '11999990003', '2008-03-15', TRUE, TRUE),
('Fundo Olimpia 2026', 'fundo-olimpia@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', NULL, NULL, TRUE, TRUE),
('Aluno Sem projeto', 'aluno2@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '11999990004', '2008-03-15', TRUE, TRUE),
('Master do Sistema', 'master@recycleways.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', NULL, NULL, TRUE, TRUE);

INSERT INTO aluno(id_usuario, rm, ano_escolar, id_curso, id_periodo, ano_ingresso) VALUES (3, '12345', 1, 1, 1, 2026);

-- Vínculos

INSERT INTO usuario_projeto (id_usuario, id_projeto, id_perfil, id_situacao, ano_escolar_na_epoca, id_curso_na_epoca, id_periodo_na_epoca, data_solicitacao, data_aprovacao, id_usuario_aprovador) 
VALUES 
(1, 1, 1, 2, NULL, NULL, NULL, NOW(), NOW(), NULL),
(2, 1, 2, 2, NULL, NULL, NULL, NOW(), NOW(), NULL),
(3, 1, 3, 2, 1, 1, 1, NOW(), NOW(), NULL),
(4, 1, 4, 2, NULL, NULL, NULL, NOW(), NOW(), NULL),
(5, NULL, 3, 1, 1, 1, 1, NOW(), NULL, NULL),
(6, NULL, 5, 2, NULL, NULL, NULL, NOW(), NOW(), NULL);

SELECT id_projeto, nome, imagem_capa FROM projeto WHERE id_projeto = 1;

UPDATE projeto 
SET imagem_capa = 'uploads/projetos/projeto-olimpia-2026/capa/capa.jpg' 
WHERE id_projeto = 1;