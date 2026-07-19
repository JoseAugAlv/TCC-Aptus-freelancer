-- ============================================================================
-- TRIGGERS DO SISTEMA APTUS
-- ============================================================================
-- NOTA: Execute este script APÓS os dados, se desejar ativar triggers
-- ============================================================================

DELIMITER //

-- ============================================================================
-- 1. NOTIFICAÇÃO - NOVO ANÚNCIO AGUARDANDO APROVAÇÃO
-- ============================================================================
CREATE TRIGGER trg_notif_novo_anuncio
AFTER INSERT ON anuncio_servico
FOR EACH ROW
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE id_moderador INT;
    DECLARE cur_moderadores CURSOR FOR 
        SELECT id_usuario FROM usuario WHERE id_perfil IN (1, 2, 4);
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur_moderadores;
    
    read_loop: LOOP
        FETCH cur_moderadores INTO id_moderador;
        IF done THEN LEAVE read_loop; END IF;
        
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            id_moderador,
            'novo_anuncio_pendente',
            'Novo anúncio aguardando aprovação',
            CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario), ' criou um novo anúncio: "', NEW.titulo, '". Aguarda moderação.'),
            'anuncio_servico',
            NEW.id_anuncio
        );
    END LOOP;
    
    CLOSE cur_moderadores;
END//

-- ============================================================================
-- 2. NOTIFICAÇÃO - ANÚNCIO APROVADO
-- ============================================================================
CREATE TRIGGER trg_notif_anuncio_aprovado
AFTER UPDATE ON anuncio_servico
FOR EACH ROW
BEGIN
    IF NEW.id_situacao_moderacao = 2 AND OLD.id_situacao_moderacao != 2 THEN
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_usuario,
            'anuncio_aprovado',
            'Seu anúncio foi aprovado!',
            CONCAT('Seu anúncio "', NEW.titulo, '" foi aprovado e já está disponível para visualização pública.'),
            'anuncio_servico',
            NEW.id_anuncio
        );
    END IF;
END//

-- ============================================================================
-- 3. NOTIFICAÇÃO - ANÚNCIO REJEITADO
-- ============================================================================
CREATE TRIGGER trg_notif_anuncio_rejeitado
AFTER UPDATE ON anuncio_servico
FOR EACH ROW
BEGIN
    IF NEW.id_situacao_moderacao = 3 AND OLD.id_situacao_moderacao != 3 THEN
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_usuario,
            'anuncio_rejeitado',
            'Seu anúncio foi rejeitado',
            CONCAT('Seu anúncio "', NEW.titulo, '" foi rejeitado. Motivo: ', COALESCE(NEW.motivo_remocao, 'Não informado')),
            'anuncio_servico',
            NEW.id_anuncio
        );
    END IF;
END//

-- ============================================================================
-- 4. NOTIFICAÇÃO - NOVO INTERESSE
-- ============================================================================
CREATE TRIGGER trg_notif_novo_interesse
AFTER INSERT ON interesse
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
    VALUES (
        NEW.id_freelancer,
        NEW.id_interesse,
        'novo_interesse',
        'Alguém se interessou pelo seu serviço!',
        CONCAT((SELECT nome FROM usuario WHERE id_usuario = NEW.id_contratante), ' demonstrou interesse no seu anúncio "', (SELECT titulo FROM anuncio_servico WHERE id_anuncio = NEW.id_anuncio), '".'),
        'interesse',
        NEW.id_interesse
    );
END//

-- ============================================================================
-- 5. NOTIFICAÇÃO - INTERESSE CONCLUÍDO
-- ============================================================================
CREATE TRIGGER trg_notif_interesse_concluido
AFTER UPDATE ON interesse
FOR EACH ROW
BEGIN
    IF NEW.situacao = 'concluido' AND OLD.situacao != 'concluido' THEN
        -- Notifica o contratante
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_contratante,
            NEW.id_interesse,
            'interesse_concluido',
            'Serviço concluído!',
            CONCAT('O serviço referente ao anúncio "', (SELECT titulo FROM anuncio_servico WHERE id_anuncio = NEW.id_anuncio), '" foi concluído.'),
            'interesse',
            NEW.id_interesse
        );
        
        -- Notifica o freelancer
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_freelancer,
            NEW.id_interesse,
            'interesse_concluido',
            'Serviço concluído!',
            CONCAT('O serviço para o cliente ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_contratante), ' foi concluído.'),
            'interesse',
            NEW.id_interesse
        );
    END IF;
END//

-- ============================================================================
-- 6. NOTIFICAÇÃO - INTERESSE CANCELADO
-- ============================================================================
CREATE TRIGGER trg_notif_interesse_cancelado
AFTER UPDATE ON interesse
FOR EACH ROW
BEGIN
    IF NEW.situacao = 'cancelado' AND OLD.situacao != 'cancelado' THEN
        -- Notifica o contratante
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_contratante,
            NEW.id_interesse,
            'interesse_cancelado',
            'Interesse cancelado',
            CONCAT('O interesse no serviço "', (SELECT titulo FROM anuncio_servico WHERE id_anuncio = NEW.id_anuncio), '" foi cancelado.'),
            'interesse',
            NEW.id_interesse
        );
        
        -- Notifica o freelancer
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_freelancer,
            NEW.id_interesse,
            'interesse_cancelado',
            'Interesse cancelado',
            CONCAT('O interesse do cliente ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_contratante), ' foi cancelado.'),
            'interesse',
            NEW.id_interesse
        );
    END IF;
END//

-- ============================================================================
-- 7. NOTIFICAÇÃO - NOVA AVALIAÇÃO
-- ============================================================================
CREATE TRIGGER trg_notif_nova_avaliacao
AFTER INSERT ON avaliacao
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
    VALUES (
        NEW.id_avaliado,
        NEW.id_interesse,
        'nova_avaliacao',
        'Você recebeu uma nova avaliação!',
        CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_avaliador), ' avaliou seu serviço com nota ', NEW.nota, ' estrelas.'),
        'avaliacao',
        NEW.id_avaliacao
    );
END//

-- ============================================================================
-- 8. NOTIFICAÇÃO - RESPOSTA À AVALIAÇÃO
-- ============================================================================
CREATE TRIGGER trg_notif_resposta_avaliacao
AFTER UPDATE ON avaliacao
FOR EACH ROW
BEGIN
    IF NEW.resposta_avaliado IS NOT NULL AND OLD.resposta_avaliado IS NULL THEN
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_avaliador,
            NEW.id_interesse,
            'resposta_avaliacao',
            'O freelancer respondeu sua avaliação',
            CONCAT((SELECT nome FROM usuario WHERE id_usuario = NEW.id_avaliado), ' respondeu ao seu comentário: "', NEW.resposta_avaliado, '"'),
            'avaliacao',
            NEW.id_avaliacao
        );
    END IF;
END//

-- ============================================================================
-- 9. NOTIFICAÇÃO - NOVA MENSAGEM
-- ============================================================================
CREATE TRIGGER trg_notif_nova_mensagem
AFTER INSERT ON mensagem
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
    VALUES (
        NEW.id_destinatario,
        NEW.id_interesse,
        'nova_mensagem',
        'Nova mensagem no chat',
        CONCAT((SELECT nome FROM usuario WHERE id_usuario = NEW.id_remetente), ' enviou uma mensagem: "', LEFT(NEW.mensagem, 50), '..."'),
        'mensagem',
        NEW.id_mensagem
    );
END//

-- ============================================================================
-- 10. NOTIFICAÇÃO - USUÁRIO BANIDO
-- ============================================================================
CREATE TRIGGER trg_notif_usuario_banido
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    IF NEW.banido = TRUE AND OLD.banido = FALSE THEN
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_usuario,
            'usuario_banido',
            'Sua conta foi banida',
            CONCAT('Sua conta foi banida. Motivo: ', COALESCE(NEW.motivo_banimento, 'Não informado'), '. Entre em contato com o suporte para mais informações.'),
            'usuario',
            NEW.id_usuario
        );
    END IF;
END//

-- ============================================================================
-- 11. NOTIFICAÇÃO - USUÁRIO DESBANIDO
-- ============================================================================
CREATE TRIGGER trg_notif_usuario_desbanido
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    IF NEW.banido = FALSE AND OLD.banido = TRUE THEN
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_usuario,
            'usuario_desbanido',
            'Sua conta foi reativada',
            'Sua conta foi reativada. Você já pode acessar o sistema novamente.',
            'usuario',
            NEW.id_usuario
        );
    END IF;
END//

-- ============================================================================
-- 12. NOTIFICAÇÃO - NOVO FAVORITO
-- ============================================================================
CREATE TRIGGER trg_notif_novo_favorito
AFTER INSERT ON favorito
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
    SELECT 
        a.id_usuario,
        'novo_favorito',
        'Alguém favoritou seu serviço!',
        CONCAT((SELECT nome FROM usuario WHERE id_usuario = NEW.id_usuario), ' favoritou seu anúncio "', a.titulo, '".'),
        'favorito',
        NEW.id_favorito
    FROM anuncio_servico a
    WHERE a.id_anuncio = NEW.id_anuncio;
END//

-- ============================================================================
-- 13. NOTIFICAÇÃO - NOVA DENÚNCIA
-- ============================================================================
CREATE TRIGGER trg_notif_nova_denuncia
AFTER INSERT ON denuncia
FOR EACH ROW
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE id_moderador INT;
    DECLARE cur_moderadores CURSOR FOR 
        SELECT id_usuario FROM usuario WHERE id_perfil IN (1, 2, 4);
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur_moderadores;
    
    read_loop: LOOP
        FETCH cur_moderadores INTO id_moderador;
        IF done THEN LEAVE read_loop; END IF;
        
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            id_moderador,
            'nova_denuncia',
            'Nova denúncia aguardando análise',
            CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_denunciante), ' denunciou ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_denunciado), '. Motivo: ', NEW.motivo),
            'denuncia',
            NEW.id_denuncia
        );
    END LOOP;
    
    CLOSE cur_moderadores;
END//

-- ============================================================================
-- 14. NOTIFICAÇÃO - DENÚNCIA ANALISADA
-- ============================================================================
CREATE TRIGGER trg_notif_denuncia_analisada
AFTER UPDATE ON denuncia
FOR EACH ROW
BEGIN
    IF NEW.id_situacao != OLD.id_situacao THEN
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_denunciante,
            'denuncia_analisada',
            'Sua denúncia foi analisada',
            CONCAT('Sua denúncia foi analisada. Status: ', (SELECT situacao FROM situacao WHERE id_situacao = NEW.id_situacao)),
            'denuncia',
            NEW.id_denuncia
        );
    END IF;
END//

-- ============================================================================
-- 15. NOTIFICAÇÃO - NOVO USUÁRIO (ADMIN)
-- ============================================================================
CREATE TRIGGER trg_notif_novo_usuario_admin
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    DECLARE id_admin INT DEFAULT 1;
    
    INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
    VALUES (
        id_admin,
        'novo_usuario_cadastrado',
        'Novo usuário cadastrado no sistema!',
        CONCAT('O usuário "', NEW.nome, '" (', NEW.email, ') acabou de se cadastrar no sistema.'),
        'usuario',
        NEW.id_usuario
    );
END//

-- ============================================================================
-- 16. NOTIFICAÇÃO - PERFIL ATUALIZADO
-- ============================================================================
CREATE TRIGGER trg_notif_perfil_atualizado
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    IF OLD.nome != NEW.nome OR OLD.telefone != NEW.telefone OR OLD.bio != NEW.bio OR OLD.foto_perfil != NEW.foto_perfil THEN
        INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
        VALUES (
            NEW.id_usuario,
            'perfil_atualizado',
            'Seu perfil foi atualizado',
            'Suas informações foram atualizadas com sucesso.',
            'usuario',
            NEW.id_usuario
        );
    END IF;
END//

-- ============================================================================
-- 17. NOTIFICAÇÃO - NOVO PORTFÓLIO
-- ============================================================================
CREATE TRIGGER trg_notif_novo_portfolio
AFTER INSERT ON portfolio
FOR EACH ROW
BEGIN
    INSERT INTO notificacao (id_usuario, tipo, titulo, mensagem, tabela_origem, registro_id)
    VALUES (
        NEW.id_usuario,
        'portfolio_adicionado',
        'Novo item no seu portfólio',
        CONCAT('Seu item "', NEW.titulo, '" foi adicionado ao portfólio com sucesso.'),
        'portfolio',
        NEW.id_portfolio
    );
END//

-- ============================================================================
-- 18. NOTIFICAÇÃO - NOVA DISPUTA (MODERADORES)
-- ============================================================================
CREATE TRIGGER trg_notif_nova_disputa
AFTER INSERT ON disputa
FOR EACH ROW
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE id_moderador INT;
    DECLARE cur_moderadores CURSOR FOR 
        SELECT id_usuario FROM usuario WHERE id_perfil IN (1, 2, 4);
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur_moderadores;
    
    read_loop: LOOP
        FETCH cur_moderadores INTO id_moderador;
        IF done THEN LEAVE read_loop; END IF;
        
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        SELECT 
            id_moderador,
            NEW.id_interesse,
            'nova_disputa',
            'Nova disputa aguardando análise',
            CONCAT('O usuário ', (SELECT nome FROM usuario WHERE id_usuario = NEW.id_aberto_por), ' abriu uma disputa. Motivo: ', NEW.motivo),
            'disputa',
            NEW.id_disputa
        FROM interesse i
        WHERE i.id_interesse = NEW.id_interesse;
    END LOOP;
    
    CLOSE cur_moderadores;
END//

-- ============================================================================
-- 19. NOTIFICAÇÃO - DISPUTA RESOLVIDA
-- ============================================================================
CREATE TRIGGER trg_notif_disputa_resolvida
AFTER UPDATE ON disputa
FOR EACH ROW
BEGIN
    IF NEW.id_situacao = 2 AND OLD.id_situacao != 2 THEN
        -- Notifica o contratante
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        SELECT 
            i.id_contratante,
            i.id_interesse,
            'disputa_resolvida',
            'Disputa resolvida',
            CONCAT('A disputa referente ao serviço "', (SELECT titulo FROM anuncio_servico WHERE id_anuncio = i.id_anuncio), '" foi resolvida. Resposta: ', COALESCE(NEW.resposta, 'Verifique os detalhes.')),
            'disputa',
            NEW.id_disputa
        FROM interesse i
        WHERE i.id_interesse = NEW.id_interesse;
        
        -- Notifica o freelancer
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        SELECT 
            i.id_freelancer,
            i.id_interesse,
            'disputa_resolvida',
            'Disputa resolvida',
            CONCAT('A disputa referente ao serviço "', (SELECT titulo FROM anuncio_servico WHERE id_anuncio = i.id_anuncio), '" foi resolvida. Resposta: ', COALESCE(NEW.resposta, 'Verifique os detalhes.')),
            'disputa',
            NEW.id_disputa
        FROM interesse i
        WHERE i.id_interesse = NEW.id_interesse;
    END IF;
END//

-- ============================================================================
-- 20. NOTIFICAÇÃO - CONFIRMAÇÃO DE PAGAMENTO (CONTRATANTE)
-- ============================================================================
CREATE TRIGGER trg_notif_pagamento_contratante
AFTER UPDATE ON confirmacao_pagamento
FOR EACH ROW
BEGIN
    IF NEW.confirmado_contratante = TRUE AND OLD.confirmado_contratante = FALSE THEN
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        SELECT 
            i.id_freelancer,
            i.id_interesse,
            'pagamento_confirmado_contratante',
            'Cliente confirmou pagamento',
            CONCAT((SELECT nome FROM usuario WHERE id_usuario = i.id_contratante), ' confirmou que realizou o pagamento no valor de R$ ', FORMAT(NEW.valor_informado_contratante, 2), '.'),
            'confirmacao_pagamento',
            NEW.id_confirmacao
        FROM interesse i
        WHERE i.id_interesse = NEW.id_interesse;
    END IF;
END//

-- ============================================================================
-- 21. NOTIFICAÇÃO - CONFIRMAÇÃO DE PAGAMENTO (FREELANCER)
-- ============================================================================
CREATE TRIGGER trg_notif_pagamento_freelancer
AFTER UPDATE ON confirmacao_pagamento
FOR EACH ROW
BEGIN
    IF NEW.confirmado_freelancer = TRUE AND OLD.confirmado_freelancer = FALSE THEN
        INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id)
        SELECT 
            i.id_contratante,
            i.id_interesse,
            'pagamento_confirmado_freelancer',
            'Freelancer confirmou recebimento',
            CONCAT((SELECT nome FROM usuario WHERE id_usuario = i.id_freelancer), ' confirmou que recebeu o pagamento no valor de R$ ', FORMAT(NEW.valor_informado_freelancer, 2), '.'),
            'confirmacao_pagamento',
            NEW.id_confirmacao
        FROM interesse i
        WHERE i.id_interesse = NEW.id_interesse;
    END IF;
END//

DELIMITER ;