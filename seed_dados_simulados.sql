-- ============================================================================
-- DADOS SIMULADOS - APTUS
-- ============================================================================

USE Aptus;

-- ============================================================================
-- 1. LIMPAR DADOS EXISTENTES (opcional - comentar se não quiser)
-- ============================================================================
-- DELETE FROM mensagem;
-- DELETE FROM avaliacao;
-- DELETE FROM notificacao;
-- DELETE FROM interesse;
-- DELETE FROM favorito;
-- DELETE FROM anuncio_servico;
-- DELETE FROM usuario;
-- DELETE FROM categoria;
-- DELETE FROM perfil;

-- ============================================================================
-- 2. PERFIS (já devem existir, mas garantimos)
-- ============================================================================
INSERT INTO perfil (perfil) VALUES ('Admin'), ('Moderador'), ('Usuario'), ('Master')
ON DUPLICATE KEY UPDATE perfil = VALUES(perfil);

-- ============================================================================
-- 3. USUÁRIOS
-- ============================================================================

-- Administradores
INSERT INTO usuario (id_perfil, nome, email, senha, telefone, whatsapp, data_nascimento, foto_perfil, bio, cidade, estado, nota_media, total_avaliacoes, ativo, email_verificado, data_criacao) VALUES
(1, 'Carlos Administrador', 'carlos@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 99999-1111', '(11) 99999-1111', '1985-01-15', 'admin1.jpg', 'Administrador do sistema Aptus', 'São Paulo', 'SP', 0, 0, 1, 1, '2025-01-15 10:00:00'),
(1, 'Mariana Administradora', 'mariana@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 99999-2222', '(11) 99999-2222', '1988-03-20', 'admin2.jpg', 'Administradora e moderadora', 'São Paulo', 'SP', 0, 0, 1, 1, '2025-02-01 14:30:00');

-- Moderadores
INSERT INTO usuario (id_perfil, nome, email, senha, telefone, whatsapp, data_nascimento, foto_perfil, bio, cidade, estado, nota_media, total_avaliacoes, ativo, email_verificado, data_criacao) VALUES
(2, 'Roberto Moderador', 'roberto@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 99999-3333', '(11) 99999-3333', '1990-05-10', 'mod1.jpg', 'Moderador de conteúdo', 'Rio de Janeiro', 'RJ', 0, 0, 1, 1, '2025-02-15 09:00:00'),
(2, 'Fernanda Moderadora', 'fernanda@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 99999-4444', '(11) 99999-4444', '1992-07-25', 'mod2.jpg', 'Moderadora de disputas', 'Belo Horizonte', 'MG', 0, 0, 1, 1, '2025-03-01 11:00:00');

-- Freelancers (Usuários)
INSERT INTO usuario (id_perfil, nome, email, senha, telefone, whatsapp, data_nascimento, foto_perfil, bio, cidade, estado, nota_media, total_avaliacoes, ativo, email_verificado, data_criacao) VALUES
(3, 'João Silva', 'joao@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 91234-5678', '(11) 91234-5678', '1995-03-15', 'joao.jpg', 'Eletricista com 10 anos de experiência. Especializado em instalações elétricas residenciais e comerciais.', 'São Paulo', 'SP', 4.8, 15, 1, 1, '2025-01-20 08:30:00'),
(3, 'Maria Santos', 'maria@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 92345-6789', '(11) 92345-6789', '1992-07-22', 'maria.jpg', 'Encanadora profissional, atuo há 8 anos no mercado. Faço instalações, reparos e manutenção.', 'São Paulo', 'SP', 4.9, 22, 1, 1, '2025-01-25 10:15:00'),
(3, 'Pedro Oliveira', 'pedro@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 93456-7890', '(11) 93456-7890', '1988-11-05', 'pedro.jpg', 'Pedreiro e mestre de obras com 15 anos de experiência. Reformas, construções e acabamentos.', 'São Paulo', 'SP', 4.7, 18, 1, 1, '2025-02-01 07:45:00'),
(3, 'Ana Costa', 'ana@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 94567-8901', '(11) 94567-8901', '1993-09-12', 'ana.jpg', 'Especialista em limpeza pós-obra e residencial. Atendo com excelência e produtos de qualidade.', 'São Paulo', 'SP', 4.6, 12, 1, 1, '2025-02-10 09:30:00'),
(3, 'Carlos Souza', 'carlos.s@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 95678-9012', '(11) 95678-9012', '1980-06-18', 'carlos.jpg', 'Jardinheiro profissional, cuido de jardins, paisagismo e manutenção de áreas verdes.', 'São Paulo', 'SP', 4.5, 9, 1, 1, '2025-02-15 14:20:00'),
(3, 'Luciana Lima', 'luciana@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 96789-0123', '(11) 96789-0123', '1991-12-30', 'luciana.jpg', 'Pintora residencial e comercial. Trabalho com acabamentos de qualidade e atendimento personalizado.', 'São Paulo', 'SP', 4.8, 14, 1, 1, '2025-03-01 08:00:00'),
(3, 'Rafael Almeida', 'rafael@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 97890-1234', '(11) 97890-1234', '1985-04-08', 'rafael.jpg', 'Cuidador de idosos com 12 anos de experiência. Especializado em cuidados paliativos.', 'São Paulo', 'SP', 4.9, 20, 1, 1, '2025-03-10 11:30:00');

-- Clientes (Usuários)
INSERT INTO usuario (id_perfil, nome, email, senha, telefone, whatsapp, data_nascimento, foto_perfil, bio, cidade, estado, nota_media, total_avaliacoes, ativo, email_verificado, data_criacao) VALUES
(3, 'Ana Carolina', 'ana.c@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 91234-0001', '(11) 91234-0001', '1990-02-14', 'cliente1.jpg', 'Cliente desde 2025', 'São Paulo', 'SP', 0, 0, 1, 1, '2025-02-20 16:00:00'),
(3, 'Roberto Martins', 'roberto.m@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 92345-0002', '(11) 92345-0002', '1975-08-22', 'cliente2.jpg', 'Cliente desde 2025', 'São Paulo', 'SP', 0, 0, 1, 1, '2025-03-01 10:00:00'),
(3, 'Patrícia Lima', 'patricia@email.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', '(11) 93456-0003', '(11) 93456-0003', '1988-11-10', 'cliente3.jpg', 'Cliente desde 2025', 'São Paulo', 'SP', 0, 0, 1, 1, '2025-03-15 09:30:00');

-- Master
INSERT INTO usuario (id_perfil, nome, email, senha, telefone, whatsapp, data_nascimento, foto_perfil, bio, cidade, estado, nota_media, total_avaliacoes, ativo, email_verificado, data_criacao) VALUES
(4, 'Master System', 'master@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', NULL, NULL, '1980-01-01', 'master.jpg', 'Master do sistema', 'São Paulo', 'SP', 0, 0, 1, 1, '2025-01-01 00:00:00');

-- ============================================================================
-- 4. CATEGORIAS
-- ============================================================================
INSERT INTO categoria (nome, icone, descricao, ativo) VALUES
('Eletricista', 'fas fa-bolt', 'Serviços elétricos residenciais e comerciais', 1),
('Encanador', 'fas fa-wrench', 'Serviços de encanamento e hidráulica', 1),
('Limpeza', 'fas fa-broom', 'Serviços de limpeza e conservação', 1),
('Construção', 'fas fa-hammer', 'Serviços de construção e reformas', 1),
('Cuidador', 'fas fa-heart', 'Cuidados com idosos e pessoas especiais', 1),
('Jardineiro', 'fas fa-leaf', 'Jardinagem e paisagismo', 1),
('Pintor', 'fas fa-paint-roller', 'Pintura residencial e comercial', 1),
('Outros', 'fas fa-ellipsis-h', 'Outros serviços', 1);

-- ============================================================================
-- 5. ANÚNCIOS DE SERVIÇOS
-- ============================================================================
INSERT INTO anuncio_servico (id_usuario, id_categoria, titulo, descricao, slug, preco, foto_capa, situacao, id_situacao_moderacao, visualizacoes, data_criacao) VALUES
-- João Silva - Eletricista
(5, 1, 'Reparos Elétricos Residenciais', 'Realizo reparos elétricos em residências. Instalação de tomadas, troca de fiação, manutenção de quadros elétricos e muito mais. Atendimento rápido e com garantia.', 'reparos-eletricos-residenciais', 150.00, 'eletrica1.jpg', 'ativo', 2, 145, '2025-01-21 09:00:00'),
(5, 1, 'Instalação Elétrica Comercial', 'Instalação elétrica completa para comércios e pequenas empresas. Projeto e execução com normas técnicas.', 'instalacao-eletrica-comercial', 350.00, 'eletrica2.jpg', 'ativo', 2, 89, '2025-02-05 14:30:00'),

-- Maria Santos - Encanadora
(6, 2, 'Desentupimento de Pias e Vasos', 'Desentupimento de pias, vasos sanitários, ralos e caixas de gordura. Atendimento 24 horas.', 'desentupimento-pias-vasos', 200.00, 'encanador1.jpg', 'ativo', 2, 210, '2025-01-26 11:00:00'),
(6, 2, 'Instalação de Sistema Hidráulico', 'Instalação completa de sistemas hidráulicos para casas e apartamentos. Inclui caixas d\'água, torneiras e registros.', 'instalacao-sistema-hidraulico', 450.00, 'encanador2.jpg', 'pausado', 2, 67, '2025-02-12 08:45:00'),

-- Pedro Oliveira - Pedreiro
(7, 4, 'Reforma de Banheiros', 'Reforma completa de banheiros. Troca de revestimentos, louças, metais e acabamentos.', 'reforma-banheiros', 800.00, 'pedreiro1.jpg', 'ativo', 2, 178, '2025-02-02 07:30:00'),
(7, 4, 'Pequenas Reformas e Manutenção', 'Pequenas reformas, reparos em alvenaria, pintura e acabamentos. Atendimento rápido.', 'pequenas-reformas', 500.00, 'pedreiro2.jpg', 'ativo', 2, 134, '2025-02-20 10:00:00'),

-- Ana Costa - Limpeza
(8, 3, 'Limpeza Pós-Obra Especializada', 'Limpeza profunda para casas e apartamentos reformados. Inclui vidros, pisos e remoção de resíduos.', 'limpeza-pos-obra', 350.00, 'limpeza1.jpg', 'ativo', 2, 156, '2025-02-11 09:15:00'),
(8, 3, 'Limpeza Residencial', 'Limpeza residencial completa. Inclui sala, quartos, cozinha, banheiros e áreas de serviço.', 'limpeza-residencial', 180.00, 'limpeza2.jpg', 'ativo', 2, 92, '2025-03-02 08:00:00'),

-- Carlos Souza - Jardineiro
(9, 6, 'Jardinagem e Paisagismo', 'Serviços de jardinagem, paisagismo e manutenção de áreas verdes.', 'jardinagem-paisagismo', 250.00, 'jardim1.jpg', 'ativo', 2, 78, '2025-02-16 13:00:00'),
(9, 6, 'Poda de Árvores', 'Poda de árvores e arbustos. Manutenção de jardins e áreas verdes.', 'poda-arvores', 120.00, 'jardim2.jpg', 'pausado', 2, 45, '2025-03-05 10:30:00'),

-- Luciana Lima - Pintora
(10, 7, 'Pintura Residencial', 'Pintura interna e externa de residências. Acabamento de qualidade e atendimento personalizado.', 'pintura-residencial', 300.00, 'pintura1.jpg', 'ativo', 2, 123, '2025-03-02 07:00:00'),
(10, 7, 'Pintura Comercial', 'Pintura de comércios, escritórios e ambientes corporativos.', 'pintura-comercial', 500.00, 'pintura2.jpg', 'ativo', 2, 56, '2025-03-12 14:00:00'),

-- Rafael Almeida - Cuidador
(11, 5, 'Cuidados com Idosos', 'Cuidados diários com idosos. Acompanhamento médico, alimentação, higiene e lazer.', 'cuidados-idosos', 400.00, 'cuidador1.jpg', 'ativo', 2, 89, '2025-03-11 09:30:00');

-- ============================================================================
-- 6. FAVORITOS
-- ============================================================================
INSERT INTO favorito (id_usuario, id_anuncio, data_criacao) VALUES
(12, 1, '2025-02-25 10:30:00'),
(12, 3, '2025-03-01 14:15:00'),
(13, 5, '2025-03-10 09:00:00'),
(13, 8, '2025-03-15 11:30:00'),
(14, 2, '2025-03-20 16:45:00'),
(14, 6, '2025-03-22 08:20:00');

-- ============================================================================
-- 7. INTERESSES (Contratações)
-- ============================================================================
INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao, data_interesse, data_conclusao) VALUES
-- João Silva (freelancer 5)
(1, 12, 5, 'Olá João! Preciso de um eletricista para instalar algumas tomadas na minha casa. Pode me atender?', 'concluido', '2025-02-10 09:00:00', '2025-02-15 18:00:00'),
(2, 13, 5, 'Bom dia! Estou abrindo uma loja e preciso de instalação elétrica. Pode me dar um orçamento?', 'ativo', '2025-03-05 11:30:00', NULL),
(1, 14, 5, 'Olá, vi seu anúncio e gostaria de agendar uma visita para avaliar os reparos.', 'concluido', '2025-03-12 14:00:00', '2025-03-18 17:30:00'),

-- Maria Santos (freelancer 6)
(3, 12, 6, 'Olá Maria! Estou com um vaso sanitário entupido. Pode vir hoje?', 'concluido', '2025-02-15 08:00:00', '2025-02-15 12:00:00'),
(4, 14, 6, 'Gostaria de um orçamento para instalação hidráulica completa da minha casa nova.', 'ativo', '2025-03-20 10:00:00', NULL),

-- Pedro Oliveira (freelancer 7)
(5, 13, 7, 'Olá Pedro! Quero reformar dois banheiros. Pode fazer uma visita para avaliar?', 'concluido', '2025-02-20 09:30:00', '2025-03-05 16:00:00'),
(6, 12, 7, 'Preciso de uma pequena reforma na minha cozinha. Tem disponibilidade?', 'ativo', '2025-03-15 15:00:00', NULL),

-- Ana Costa (freelancer 8)
(7, 14, 8, 'Olá Ana! Reformei meu apartamento e preciso de limpeza pós-obra.', 'concluido', '2025-02-25 13:00:00', '2025-02-28 18:00:00'),
(8, 13, 8, 'Gostaria de contratar limpeza residencial semanal. Tem disponibilidade?', 'ativo', '2025-03-18 09:00:00', NULL),

-- Carlos Souza (freelancer 9)
(9, 12, 9, 'Olá Carlos! Preciso de manutenção no jardim da minha casa.', 'concluido', '2025-03-01 10:30:00', '2025-03-03 17:00:00'),

-- Luciana Lima (freelancer 10)
(11, 13, 10, 'Olá Luciana! Gostaria de um orçamento para pintar minha casa.', 'concluido', '2025-03-10 14:30:00', '2025-03-20 19:00:00'),

-- Rafael Almeida (freelancer 11)
(13, 14, 11, 'Olá Rafael! Preciso de cuidados para minha mãe durante o período da tarde.', 'ativo', '2025-03-22 08:00:00', NULL);

-- ============================================================================
-- 8. CONFIRMAÇÃO DE PAGAMENTO
-- ============================================================================
INSERT INTO confirmacao_pagamento (id_interesse, confirmado_contratante, valor_informado_contratante, forma_pagamento_contratante, data_pagamento_contratante, data_confirmacao_contratante, confirmado_freelancer, valor_informado_freelancer, data_recebimento_freelancer, data_confirmacao_freelancer, situacao_final) VALUES
(1, 1, 150.00, 'pix', '2025-02-14', '2025-02-14 10:00:00', 1, 150.00, '2025-02-14', '2025-02-14 10:30:00', 'confirmado'),
(3, 1, 150.00, 'dinheiro', '2025-03-17', '2025-03-17 15:00:00', 1, 150.00, '2025-03-17', '2025-03-17 15:30:00', 'confirmado'),
(4, 1, 200.00, 'pix', '2025-02-15', '2025-02-15 09:00:00', 1, 200.00, '2025-02-15', '2025-02-15 09:20:00', 'confirmado'),
(5, 1, 800.00, 'transferencia', '2025-03-04', '2025-03-04 11:00:00', 1, 800.00, '2025-03-04', '2025-03-04 11:30:00', 'confirmado'),
(7, 1, 350.00, 'pix', '2025-02-27', '2025-02-27 14:00:00', 1, 350.00, '2025-02-27', '2025-02-27 14:30:00', 'confirmado'),
(9, 1, 250.00, 'dinheiro', '2025-03-02', '2025-03-02 15:00:00', 1, 250.00, '2025-03-02', '2025-03-02 15:30:00', 'confirmado'),
(11, 1, 300.00, 'pix', '2025-03-19', '2025-03-19 18:00:00', 1, 300.00, '2025-03-19', '2025-03-19 18:30:00', 'confirmado');

-- ============================================================================
-- 9. AVALIAÇÕES
-- ============================================================================
INSERT INTO avaliacao (id_interesse, id_avaliador, id_avaliado, nota, comentario, resposta_avaliado, data_resposta, data_avaliacao) VALUES
(1, 12, 5, 5, 'Excelente profissional! Fez um trabalho impecável e foi muito pontual.', 'Muito obrigado! Foi um prazer atender.', '2025-02-16 10:00:00', '2025-02-15 19:00:00'),
(4, 12, 6, 5, 'Maria foi muito rápida e resolveu o problema em minutos. Recomendo!', NULL, NULL, '2025-02-15 13:00:00'),
(5, 13, 7, 4, 'Pedro fez um bom trabalho, mas atrasou um pouco na entrega.', 'Peço desculpas pelo atraso, tive um imprevisto.', '2025-03-06 09:00:00', '2025-03-05 17:00:00'),
(7, 14, 8, 5, 'Ana fez uma limpeza incrível! Minha casa ficou como nova.', 'Fico feliz que gostou!', '2025-03-01 10:00:00', '2025-02-28 19:00:00'),
(9, 12, 9, 4, 'Carlos cuidou do jardim muito bem. Só demorou um pouco mais que o combinado.', NULL, NULL, '2025-03-03 18:00:00'),
(11, 13, 10, 5, 'Luciana é uma pintora fantástica! A casa ficou linda.', 'Obrigada pelo carinho!', '2025-03-21 08:00:00', '2025-03-20 20:00:00'),
(3, 14, 5, 5, 'João é muito competente. Recomendo a todos!', 'Obrigado pela confiança!', '2025-03-19 10:00:00', '2025-03-18 18:00:00');

-- ============================================================================
-- 10. MENSAGENS (Chat)
-- ============================================================================
INSERT INTO mensagem (id_interesse, id_remetente, id_destinatario, mensagem, lida, data_envio) VALUES
-- Interesse 1 (João Silva - Ana Carolina)
(1, 12, 5, 'Olá João! Preciso de um eletricista para instalar algumas tomadas na minha casa. Pode me atender?', 1, '2025-02-10 09:00:00'),
(1, 5, 12, 'Olá! Claro, posso sim. Qual o endereço e horário disponível?', 1, '2025-02-10 09:15:00'),
(1, 12, 5, 'Rua das Flores, 123, Jardim Paulista. Pode ser amanhã às 14h?', 1, '2025-02-10 09:30:00'),
(1, 5, 12, 'Perfeito! Estarei lá amanhã às 14h.', 1, '2025-02-10 09:45:00'),
(1, 12, 5, 'Ótimo! Até amanhã.', 1, '2025-02-10 10:00:00'),

-- Interesse 3 (João Silva - Patrícia Lima)
(3, 14, 5, 'Olá, vi seu anúncio e gostaria de agendar uma visita para avaliar os reparos.', 1, '2025-03-12 14:00:00'),
(3, 5, 14, 'Olá! Posso ir amanhã às 10h. Pode ser?', 1, '2025-03-12 14:30:00'),
(3, 14, 5, 'Pode sim! Aguardo você.', 1, '2025-03-12 15:00:00'),

-- Interesse 4 (Maria Santos - Ana Carolina)
(4, 12, 6, 'Olá Maria! Estou com um vaso sanitário entupido. Pode vir hoje?', 1, '2025-02-15 08:00:00'),
(4, 6, 12, 'Oi! Posso sim. Chego em 1 hora.', 1, '2025-02-15 08:15:00'),
(4, 12, 6, 'Perfeito! Aguardo.', 1, '2025-02-15 08:30:00'),

-- Interesse 5 (Pedro Oliveira - Roberto Martins)
(5, 13, 7, 'Olá Pedro! Quero reformar dois banheiros. Pode fazer uma visita para avaliar?', 1, '2025-02-20 09:30:00'),
(5, 7, 13, 'Olá! Posso ir amanhã às 14h.', 1, '2025-02-20 10:00:00'),
(5, 13, 7, 'Ótimo! Até amanhã.', 1, '2025-02-20 10:30:00'),

-- Interesse 7 (Ana Costa - Patrícia Lima)
(7, 14, 8, 'Olá Ana! Reformei meu apartamento e preciso de limpeza pós-obra.', 1, '2025-02-25 13:00:00'),
(7, 8, 14, 'Olá! Posso começar amanhã cedo.', 1, '2025-02-25 13:30:00'),
(7, 14, 8, 'Perfeito!', 1, '2025-02-25 14:00:00'),

-- Interesse 9 (Carlos Souza - Ana Carolina)
(9, 12, 9, 'Olá Carlos! Preciso de manutenção no jardim da minha casa.', 1, '2025-03-01 10:30:00'),
(9, 9, 12, 'Olá! Posso ir quarta-feira.', 1, '2025-03-01 11:00:00'),

-- Interesse 11 (Luciana Lima - Roberto Martins)
(11, 13, 10, 'Olá Luciana! Gostaria de um orçamento para pintar minha casa.', 1, '2025-03-10 14:30:00'),
(11, 10, 13, 'Olá! Posso ir fazer uma avaliação amanhã.', 1, '2025-03-10 15:00:00');

-- ============================================================================
-- 11. NOTIFICAÇÕES
-- ============================================================================
INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, lida, data_criacao) VALUES
-- Para freelancers sobre novos interesses
(5, 1, 'novo_interesse', 'Novo interesse no seu serviço!', 'Ana Carolina demonstrou interesse no seu serviço "Reparos Elétricos Residenciais".', 1, '2025-02-10 09:00:00'),
(5, 2, 'novo_interesse', 'Novo interesse no seu serviço!', 'Roberto Martins demonstrou interesse no seu serviço "Instalação Elétrica Comercial".', 0, '2025-03-05 11:30:00'),
(5, 3, 'novo_interesse', 'Novo interesse no seu serviço!', 'Patrícia Lima demonstrou interesse no seu serviço "Reparos Elétricos Residenciais".', 1, '2025-03-12 14:00:00'),
(6, 4, 'novo_interesse', 'Novo interesse no seu serviço!', 'Ana Carolina demonstrou interesse no seu serviço "Desentupimento de Pias e Vasos".', 1, '2025-02-15 08:00:00'),
(6, 5, 'novo_interesse', 'Novo interesse no seu serviço!', 'Patrícia Lima demonstrou interesse no seu serviço "Instalação de Sistema Hidráulico".', 0, '2025-03-20 10:00:00'),
(7, 6, 'novo_interesse', 'Novo interesse no seu serviço!', 'Roberto Martins demonstrou interesse no seu serviço "Reforma de Banheiros".', 1, '2025-02-20 09:30:00'),
(7, 7, 'novo_interesse', 'Novo interesse no seu serviço!', 'Ana Carolina demonstrou interesse no seu serviço "Pequenas Reformas e Manutenção".', 0, '2025-03-15 15:00:00'),
(8, 8, 'novo_interesse', 'Novo interesse no seu serviço!', 'Patrícia Lima demonstrou interesse no seu serviço "Limpeza Pós-Obra Especializada".', 1, '2025-02-25 13:00:00'),
(8, 9, 'novo_interesse', 'Novo interesse no seu serviço!', 'Roberto Martins demonstrou interesse no seu serviço "Limpeza Residencial".', 0, '2025-03-18 09:00:00'),
(9, 10, 'novo_interesse', 'Novo interesse no seu serviço!', 'Ana Carolina demonstrou interesse no seu serviço "Jardinagem e Paisagismo".', 1, '2025-03-01 10:30:00'),
(10, 11, 'novo_interesse', 'Novo interesse no seu serviço!', 'Roberto Martins demonstrou interesse no seu serviço "Pintura Residencial".', 1, '2025-03-10 14:30:00'),
(11, 12, 'novo_interesse', 'Novo interesse no seu serviço!', 'Patrícia Lima demonstrou interesse no seu serviço "Cuidados com Idosos".', 0, '2025-03-22 08:00:00'),

-- Para freelancers sobre interesses concluídos
(5, 1, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Ana Carolina foi concluído.', 1, '2025-02-15 18:00:00'),
(6, 4, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Ana Carolina foi concluído.', 1, '2025-02-15 12:00:00'),
(7, 6, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Roberto Martins foi concluído.', 1, '2025-03-05 16:00:00'),
(8, 8, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Patrícia Lima foi concluído.', 1, '2025-02-28 18:00:00'),
(9, 10, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Ana Carolina foi concluído.', 1, '2025-03-03 17:00:00'),
(10, 11, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Roberto Martins foi concluído.', 1, '2025-03-20 19:00:00'),
(5, 3, 'interesse_concluido', 'Serviço concluído!', 'O serviço para Patrícia Lima foi concluído.', 1, '2025-03-18 17:30:00'),

-- Para contratantes sobre interesses concluídos
(12, 1, 'interesse_concluido', 'Serviço concluído!', 'O serviço com João Silva foi concluído.', 1, '2025-02-15 18:00:00'),
(12, 4, 'interesse_concluido', 'Serviço concluído!', 'O serviço com Maria Santos foi concluído.', 1, '2025-02-15 12:00:00'),
(13, 6, 'interesse_concluido', 'Serviço concluído!', 'O serviço com Pedro Oliveira foi concluído.', 1, '2025-03-05 16:00:00'),
(14, 8, 'interesse_concluido', 'Serviço concluído!', 'O serviço com Ana Costa foi concluído.', 1, '2025-02-28 18:00:00'),
(12, 10, 'interesse_concluido', 'Serviço concluído!', 'O serviço com Carlos Souza foi concluído.', 1, '2025-03-03 17:00:00'),
(13, 11, 'interesse_concluido', 'Serviço concluído!', 'O serviço com Luciana Lima foi concluído.', 1, '2025-03-20 19:00:00'),
(14, 3, 'interesse_concluido', 'Serviço concluído!', 'O serviço com João Silva foi concluído.', 1, '2025-03-18 17:30:00'),

-- Avaliações
(5, 1, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Ana Carolina avaliou seu serviço com nota 5 estrelas.', 1, '2025-02-15 19:00:00'),
(6, 4, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Ana Carolina avaliou seu serviço com nota 5 estrelas.', 1, '2025-02-15 13:00:00'),
(7, 6, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Roberto Martins avaliou seu serviço com nota 4 estrelas.', 1, '2025-03-05 17:00:00'),
(8, 8, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Patrícia Lima avaliou seu serviço com nota 5 estrelas.', 1, '2025-02-28 19:00:00'),
(9, 10, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Ana Carolina avaliou seu serviço com nota 4 estrelas.', 1, '2025-03-03 18:00:00'),
(10, 11, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Roberto Martins avaliou seu serviço com nota 5 estrelas.', 1, '2025-03-20 20:00:00'),
(5, 3, 'nova_avaliacao', 'Você recebeu uma nova avaliação!', 'Patrícia Lima avaliou seu serviço com nota 5 estrelas.', 1, '2025-03-18 18:00:00'),

-- Respostas às avaliações
(12, 1, 'resposta_avaliacao', 'O freelancer respondeu sua avaliação', 'João Silva respondeu ao seu comentário: "Muito obrigado! Foi um prazer atender."', 1, '2025-02-16 10:00:00'),
(13, 6, 'resposta_avaliacao', 'O freelancer respondeu sua avaliação', 'Pedro Oliveira respondeu ao seu comentário: "Peço desculpas pelo atraso, tive um imprevisto."', 1, '2025-03-06 09:00:00'),
(14, 8, 'resposta_avaliacao', 'O freelancer respondeu sua avaliação', 'Ana Costa respondeu ao seu comentário: "Fico feliz que gostou!"', 1, '2025-03-01 10:00:00'),
(13, 11, 'resposta_avaliacao', 'O freelancer respondeu sua avaliação', 'Luciana Lima respondeu ao seu comentário: "Obrigada pelo carinho!"', 1, '2025-03-21 08:00:00'),
(14, 3, 'resposta_avaliacao', 'O freelancer respondeu sua avaliação', 'João Silva respondeu ao seu comentário: "Obrigado pela confiança!"', 1, '2025-03-19 10:00:00');

-- ============================================================================
-- 12. ATUALIZAR NOTA MÉDIA DOS FREELANCERS
-- ============================================================================
UPDATE usuario SET nota_media = 4.8, total_avaliacoes = 3 WHERE id_usuario = 5;  -- João Silva
UPDATE usuario SET nota_media = 5.0, total_avaliacoes = 1 WHERE id_usuario = 6;  -- Maria Santos
UPDATE usuario SET nota_media = 4.0, total_avaliacoes = 1 WHERE id_usuario = 7;  -- Pedro Oliveira
UPDATE usuario SET nota_media = 5.0, total_avaliacoes = 1 WHERE id_usuario = 8;  -- Ana Costa
UPDATE usuario SET nota_media = 4.0, total_avaliacoes = 1 WHERE id_usuario = 9;  -- Carlos Souza
UPDATE usuario SET nota_media = 5.0, total_avaliacoes = 1 WHERE id_usuario = 10; -- Luciana Lima

-- ============================================================================
-- 13. RESULTADO FINAL
-- ============================================================================
SELECT 'DADOS SIMULADOS INSERIDOS COM SUCESSO!' as status;
SELECT 
    (SELECT COUNT(*) FROM usuario) as total_usuarios,
    (SELECT COUNT(*) FROM anuncio_servico) as total_anuncios,
    (SELECT COUNT(*) FROM interesse) as total_interesses,
    (SELECT COUNT(*) FROM avaliacao) as total_avaliacoes,
    (SELECT COUNT(*) FROM mensagem) as total_mensagens,
    (SELECT COUNT(*) FROM notificacao) as total_notificacoes;