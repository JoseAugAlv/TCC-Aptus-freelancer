-- ============================================================================
-- DADOS DE EXEMPLO - SISTEMA EM USO
-- ============================================================================

-- ============================================================================
-- 1. MAIS CATEGORIAS E HABILIDADES
-- ============================================================================

INSERT INTO categoria (nome, descricao, icone, ativo) VALUES
('Design Gráfico', 'Criação de identidade visual, logos, materiais gráficos', 'fas fa-palette', 1),
('Desenvolvimento Web', 'Criação de sites, sistemas e aplicações web', 'fas fa-code', 1),
('Fotografia', 'Fotografia profissional para eventos, produtos e ensaios', 'fas fa-camera', 1),
('Tradução', 'Tradução de documentos, textos e interpretação', 'fas fa-language', 1),
('Consultoria', 'Consultoria empresarial, de marketing e gestão', 'fas fa-chart-line', 1);

INSERT INTO habilidade (id_categoria, nome) VALUES
-- Design Gráfico (categoria 5)
(5, 'Adobe Photoshop'),
(5, 'Adobe Illustrator'),
(5, 'Adobe InDesign'),
(5, 'Identidade Visual'),
(5, 'Social Media Design'),
-- Desenvolvimento Web (categoria 6)
(6, 'PHP'),
(6, 'JavaScript'),
(6, 'React'),
(6, 'Node.js'),
(6, 'HTML/CSS'),
(6, 'MySQL'),
-- Fotografia (categoria 7)
(7, 'Fotografia de Produto'),
(7, 'Fotografia de Casamento'),
(7, 'Edição de Imagens'),
(7, 'Iluminação'),
-- Tradução (categoria 8)
(8, 'Português-Inglês'),
(8, 'Inglês-Português'),
(8, 'Espanhol-Português'),
-- Consultoria (categoria 9)
(9, 'Marketing Digital'),
(9, 'Gestão de Projetos'),
(9, 'Análise de Dados');

-- ============================================================================
-- 2. MAIS USUÁRIOS (FREELANCERS E CONTRATANTES)
-- ============================================================================

INSERT INTO usuario (
    id_perfil, nome, email, senha, telefone, whatsapp, cpf_cnpj, 
    data_nascimento, foto_perfil, bio, cidade, estado, 
    nota_media, total_avaliacoes, ativo, banido, email_verificado, 
    data_verificacao, data_criacao
) VALUES
(3, 'Ana Paula Costa', 'ana@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '11987654321', '11987654321', '12345678901', '1990-05-15', 'ana_perfil.jpg', 
 'Designer gráfica com 8 anos de experiência, especializada em identidade visual', 
 'São Paulo', 'SP', 4.80, 5, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 180 DAY)),

(3, 'Roberto Almeida', 'roberto@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '21998765432', '21998765432', '98765432109', '1985-08-22', 'roberto_perfil.jpg', 
 'Desenvolvedor fullstack com foco em PHP e React', 
 'Rio de Janeiro', 'RJ', 4.60, 8, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 200 DAY)),

(3, 'Carla Mendes', 'carla@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '31998765432', '31998765432', '45678912345', '1992-11-30', 'carla_perfil.jpg', 
 'Fotógrafa especializada em casamentos e eventos', 
 'Belo Horizonte', 'MG', 4.90, 12, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 150 DAY)),

(3, 'Fernando Lima', 'fernando@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '41998765432', '41998765432', '78912345678', '1988-03-10', 'fernando_perfil.jpg', 
 'Tradutor juramentado e intérprete de conferências', 
 'Curitiba', 'PR', 4.70, 6, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 220 DAY)),

(3, 'Mariana Souza', 'mariana@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '51998765432', '51998765432', '45612378945', '1995-07-18', 'mariana_perfil.jpg', 
 'Consultora de marketing digital e growth hacking', 
 'Porto Alegre', 'RS', 4.50, 4, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 100 DAY)),

(3, 'Lucas Ferreira', 'lucas@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '61998765432', '61998765432', '78945612378', '1993-09-05', 'lucas_perfil.jpg', 
 'Eletricista residencial e industrial', 
 'Brasília', 'DF', 4.30, 3, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 90 DAY)),

(3, 'Patrícia Oliveira', 'patricia@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '71998765432', '71998765432', '12378945612', '1991-12-12', 'patricia_perfil.jpg', 
 'Encanadora com 10 anos de experiência', 
 'Salvador', 'BA', 4.80, 9, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 130 DAY));

-- ============================================================================
-- 3. HABILIDADES DOS USUÁRIOS
-- ============================================================================

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel) VALUES
-- Ana (designer) - IDs 7 a 11
(7, 1, 'avancado'),
(7, 2, 'avancado'),
(7, 3, 'intermediario'),
(7, 4, 'avancado'),
(7, 5, 'avancado'),
-- Roberto (dev)
(8, 6, 'avancado'),
(8, 7, 'avancado'),
(8, 8, 'intermediario'),
(8, 9, 'basico'),
(8, 10, 'avancado'),
(8, 11, 'avancado'),
-- Carla (fotógrafa)
(9, 12, 'avancado'),
(9, 13, 'avancado'),
(9, 14, 'intermediario'),
(9, 15, 'avancado'),
-- Fernando (tradutor)
(10, 16, 'avancado'),
(10, 17, 'avancado'),
(10, 18, 'intermediario'),
-- Mariana (consultora)
(11, 19, 'avancado'),
(11, 20, 'intermediario'),
(11, 21, 'intermediario'),
-- Lucas (eletricista) - habilidade da categoria Eletricista
(12, 1, 'intermediario'),
-- Patrícia (encanadora) - habilidade da categoria Encanador
(13, 2, 'intermediario');

-- ============================================================================
-- 4. PORTFÓLIO DOS USUÁRIOS
-- ============================================================================

INSERT INTO portfolio (id_usuario, titulo, descricao, imagem, ordem, data_criacao) VALUES
(7, 'Identidade Visual - Empresa Tech', 'Criação de logo, papelaria e redes sociais para startup de tecnologia', 
 'portfolio_ana_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(7, 'Redesign de Marca - Café Gourmet', 'Projeto completo de rebranding para cafeteria especializada', 
 'portfolio_ana_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(7, 'Material Gráfico - Evento Cultural', 'Cartazes, banners e flyers para festival de música', 
 'portfolio_ana_3.jpg', 3, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(8, 'Sistema de Gestão - Empresa de Logística', 'Desenvolvimento completo de sistema web com PHP e MySQL', 
 'portfolio_roberto_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(8, 'E-commerce - Loja de Roupas', 'Loja virtual completa com integração de pagamento', 
 'portfolio_roberto_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(9, 'Ensaio Fotográfico - Casamento', 'Fotos de casamento realizado na praia', 
 'portfolio_carla_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 120 DAY)),
(9, 'Fotografia de Produto - Joias', 'Catálogo de joias para loja de luxo', 
 'portfolio_carla_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(9, 'Ensaio Gestacional', 'Ensaio fotográfico de gestante em estúdio', 
 'portfolio_carla_3.jpg', 3, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(10, 'Tradução - Manual Técnico', 'Tradução de manual de equipamentos industriais (Inglês-Português)', 
 'portfolio_fernando_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(10, 'Interpretação - Conferência', 'Interpretação simultânea em conferência internacional', 
 'portfolio_fernando_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 55 DAY));

-- ============================================================================
-- 5. ANÚNCIOS DE SERVIÇO
-- ============================================================================

INSERT INTO anuncio_servico (
    id_usuario, id_categoria, titulo, descricao, slug, preco, 
    situacao, id_situacao_moderacao, foto_capa, visualizacoes, data_criacao
) VALUES
(7, 5, 'Criação de Identidade Visual Completa', 
 'Crio logos, paletas de cores, tipografia e todos os elementos visuais para sua marca.', 
 'identidade-visual-completa', 1500.00, 'ativo', 2, 'anuncio_ana_1.jpg', 45, DATE_SUB(NOW(), INTERVAL 80 DAY)),

(7, 5, 'Design de Redes Sociais', 
 'Criação de posts, stories e arte para redes sociais', 
 'design-redes-sociais', 800.00, 'ativo', 2, 'anuncio_ana_2.jpg', 32, DATE_SUB(NOW(), INTERVAL 60 DAY)),

(8, 6, 'Desenvolvimento de Sites Profissionais', 
 'Criação de sites responsivos e otimizados para SEO', 
 'desenvolvimento-sites', 2500.00, 'ativo', 2, 'anuncio_roberto_1.jpg', 38, DATE_SUB(NOW(), INTERVAL 90 DAY)),

(8, 6, 'Sistemas Web Personalizados', 
 'Desenvolvimento de sistemas sob medida para sua empresa', 
 'sistemas-web-personalizados', 4000.00, 'ativo', 2, 'anuncio_roberto_2.jpg', 28, DATE_SUB(NOW(), INTERVAL 70 DAY)),

(9, 7, 'Fotografia de Casamentos', 
 'Cobertura completa de casamentos com ensaio e making of', 
 'fotografia-casamentos', 3000.00, 'ativo', 2, 'anuncio_carla_1.jpg', 52, DATE_SUB(NOW(), INTERVAL 100 DAY)),

(9, 7, 'Ensaio Fotográfico Profissional', 
 'Ensaio para perfis, currículos e portfólios', 
 'ensaio-fotografico', 600.00, 'ativo', 2, 'anuncio_carla_2.jpg', 25, DATE_SUB(NOW(), INTERVAL 50 DAY)),

(10, 8, 'Tradução de Documentos', 
 'Tradução de documentos técnicos, acadêmicos e oficiais', 
 'traducao-documentos', 120.00, 'ativo', 2, 'anuncio_fernando_1.jpg', 20, DATE_SUB(NOW(), INTERVAL 75 DAY)),

(10, 8, 'Tradução Juramentada', 
 'Tradução de documentos oficiais com validade jurídica', 
 'traducao-juramentada', 250.00, 'ativo', 2, 'anuncio_fernando_2.jpg', 15, DATE_SUB(NOW(), INTERVAL 65 DAY)),

(11, 9, 'Consultoria em Marketing Digital', 
 'Elaboração de estratégia de marketing digital para seu negócio', 
 'consultoria-marketing', 1800.00, 'ativo', 2, 'anuncio_mariana_1.jpg', 30, DATE_SUB(NOW(), INTERVAL 55 DAY)),

(11, 9, 'Análise de Dados para Negócios', 
 'Análise de dados e criação de dashboards para tomada de decisão', 
 'analise-dados-negocios', 2000.00, 'ativo', 2, 'anuncio_mariana_2.jpg', 18, DATE_SUB(NOW(), INTERVAL 40 DAY)),

(12, 1, 'Serviços Elétricos Residenciais', 
 'Instalação, reparo e manutenção elétrica', 
 'servicos-eletricos', 200.00, 'ativo', 2, 'anuncio_lucas_1.jpg', 60, DATE_SUB(NOW(), INTERVAL 85 DAY)),

(13, 2, 'Serviços de Encanamento', 
 'Desentupimento, instalação e reparos de encanamento', 
 'servicos-encanamento', 250.00, 'ativo', 2, 'anuncio_patricia_1.jpg', 45, DATE_SUB(NOW(), INTERVAL 70 DAY)),

(7, 5, 'Design de E-books', 
 'Criação de e-books profissionais', 
 'design-ebooks', 500.00, 'pausado', 2, 'anuncio_ana_3.jpg', 12, DATE_SUB(NOW(), INTERVAL 30 DAY)),

(8, 6, 'Aplicativos Mobile', 
 'Desenvolvimento de apps híbridos com React Native', 
 'apps-mobile', 3500.00, 'ativo', 1, 'anuncio_roberto_3.jpg', 5, DATE_SUB(NOW(), INTERVAL 15 DAY)),

(9, 7, 'Fotografia de Produtos', 
 'Fotos profissionais para e-commerce', 
 'fotografia-produtos', 400.00, 'ativo', 3, NULL, 10, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- ============================================================================
-- 6. FOTOS ADICIONAIS DOS ANÚNCIOS
-- ============================================================================

INSERT INTO anuncio_foto (id_anuncio, arquivo, ordem, data_criacao) VALUES
(5, 'ana_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(5, 'ana_anuncio1_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(6, 'ana_anuncio2_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(7, 'roberto_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(7, 'roberto_anuncio1_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(8, 'roberto_anuncio2_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(9, 'carla_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 100 DAY)),
(9, 'carla_anuncio1_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 100 DAY)),
(10, 'carla_anuncio2_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(14, 'lucas_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 85 DAY)),
(15, 'patricia_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 70 DAY));

-- ============================================================================
-- 7. FAVORITOS
-- ============================================================================

INSERT INTO favorito (id_usuario, id_anuncio, data_criacao) VALUES
-- Usuário 4 (João) favoritando
(4, 5, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(4, 7, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(4, 9, DATE_SUB(NOW(), INTERVAL 30 DAY)),
-- Usuário 5 (Maria) favoritando
(5, 6, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(5, 8, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(5, 11, DATE_SUB(NOW(), INTERVAL 25 DAY)),
-- Usuário 6 (Carlos) favoritando
(6, 9, DATE_SUB(NOW(), INTERVAL 55 DAY)),
(6, 10, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(6, 12, DATE_SUB(NOW(), INTERVAL 20 DAY)),
-- Usuários novos favoritando
(7, 14, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(7, 15, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(8, 5, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(8, 6, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(9, 7, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(9, 8, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(10, 9, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(10, 10, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(11, 5, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(11, 11, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- ============================================================================
-- 8. INTERESSES (NEGOCIAÇÕES)
-- ============================================================================

-- Interesses ativos
INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao, data_interesse) VALUES
(5, 4, 7, 'Olá Ana, gostei muito do seu trabalho! Gostaria de contratar a identidade visual para minha empresa.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(7, 5, 8, 'Preciso de um site para minha empresa de consultoria. Podemos conversar?', 
 'ativo', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(9, 6, 9, 'Carla, gostei do seu portfólio de casamentos. Gostaria de fazer um orçamento para meu casamento.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 18 DAY)),
(14, 5, 12, 'Lucas, preciso de uma reforma elétrica completa na minha casa.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(15, 4, 13, 'Patrícia, preciso de desentupimento urgente no meu banheiro.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 12 DAY));

-- Interesses concluídos
INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao, data_interesse, data_conclusao) VALUES
(6, 7, 7, 'Preciso de posts para redes sociais da minha loja.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),
(8, 8, 8, 'Preciso de um sistema de agendamento para meu salão.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),
(10, 9, 9, 'Quero fazer um ensaio fotográfico profissional.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(11, 10, 10, 'Preciso traduzir documentos para um processo.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(12, 4, 10, 'Preciso de tradução juramentada do meu diploma.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(13, 5, 11, 'Quero consultoria de marketing para minha startup.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Interesses cancelados
INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao, data_interesse, data_conclusao) VALUES
(5, 11, 7, 'Gostaria de um orçamento para design de logo.', 
 'cancelado', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(7, 6, 8, 'Preciso de um site para minha loja virtual.', 
 'cancelado', DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(14, 4, 12, 'Lucas, preciso de um eletricista urgente.', 
 'cancelado', DATE_SUB(NOW(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY));

-- ============================================================================
-- 9. CONFIRMAÇÕES DE PAGAMENTO
-- ============================================================================

-- Pagamentos confirmados (interesses concluídos)
INSERT INTO confirmacao_pagamento (
    id_interesse, confirmado_contratante, valor_informado_contratante, 
    forma_pagamento_contratante, data_pagamento_contratante, data_confirmacao_contratante,
    confirmado_freelancer, valor_informado_freelancer, data_recebimento_freelancer, 
    data_confirmacao_freelancer, situacao_final, data_criacao
) VALUES
(6, 1, 800.00, 'pix', DATE_SUB(NOW(), INTERVAL 46 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY),
 1, 800.00, DATE_SUB(NOW(), INTERVAL 46 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 60 DAY)),

(7, 1, 4000.00, 'transferencia', DATE_SUB(NOW(), INTERVAL 41 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY),
 1, 4000.00, DATE_SUB(NOW(), INTERVAL 41 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 55 DAY)),

(8, 1, 600.00, 'pix', DATE_SUB(NOW(), INTERVAL 36 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY),
 1, 600.00, DATE_SUB(NOW(), INTERVAL 36 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 50 DAY)),

(9, 1, 120.00, 'dinheiro', DATE_SUB(NOW(), INTERVAL 31 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY),
 1, 120.00, DATE_SUB(NOW(), INTERVAL 31 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 45 DAY)),

(10, 1, 250.00, 'pix', DATE_SUB(NOW(), INTERVAL 26 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY),
 1, 250.00, DATE_SUB(NOW(), INTERVAL 26 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 40 DAY)),

(11, 1, 1800.00, 'transferencia', DATE_SUB(NOW(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY),
 1, 1800.00, DATE_SUB(NOW(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 35 DAY));

-- Pagamento divergente (com disputa)
INSERT INTO confirmacao_pagamento (
    id_interesse, confirmado_contratante, valor_informado_contratante, 
    forma_pagamento_contratante, data_pagamento_contratante, data_confirmacao_contratante,
    confirmado_freelancer, valor_informado_freelancer, data_recebimento_freelancer, 
    data_confirmacao_freelancer, situacao_final, data_criacao
) VALUES
(5, 1, 1500.00, 'pix', DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 23 DAY),
 1, 1500.00, DATE_SUB(NOW(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY), 
 'divergente', DATE_SUB(NOW(), INTERVAL 30 DAY));

-- ============================================================================
-- 10. DISPUTAS
-- ============================================================================

INSERT INTO disputa (
    id_interesse, id_aberto_por, motivo, descricao, id_situacao, 
    id_responsavel, resposta, data_abertura, data_resolucao
) VALUES
(5, 4, 'Pagamento não reconhecido', 
 'O contratante alega que o pagamento foi feito, mas o freelancer não confirma o recebimento.', 
 2, 2, 'Após análise dos comprovantes enviados por ambas as partes, verificamos que o pagamento foi realizado corretamente. A disputa foi resolvida a favor do contratante.', 
 DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY));

-- Anexos da disputa
INSERT INTO disputa_anexo (id_disputa, id_usuario, arquivo, data_criacao) VALUES
(1, 4, 'comprovante_pagamento_contratante.jpg', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 7, 'comprovante_pagamento_freelancer.jpg', DATE_SUB(NOW(), INTERVAL 19 DAY));

-- ============================================================================
-- 11. AVALIAÇÕES
-- ============================================================================

INSERT INTO avaliacao (
    id_interesse, id_avaliador, id_avaliado, nota, comentario, 
    resposta_avaliado, data_resposta, data_avaliacao
) VALUES
(6, 7, 7, 5, 'Excelente profissional! Entregou o projeto antes do prazo e com muita qualidade. Super recomendo.', 
 'Muito obrigado pela avaliação! Foi um prazer trabalhar com você.', 
 DATE_SUB(NOW(), INTERVAL 44 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),

(7, 8, 8, 4, 'Ótimo desenvolvimento, embora tenha atrasado um pouco na entrega. O resultado final é excelente.', 
 'Peço desculpas pelo atraso, tivemos um imprevisto, mas fico feliz que gostou do resultado.', 
 DATE_SUB(NOW(), INTERVAL 39 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),

(8, 9, 9, 5, 'Fotos maravilhosas! Ficou exatamente como eu queria. A Carla é muito profissional e criativa.', 
 'Foi um prazer realizar esse ensaio! Obrigado pela confiança.', 
 DATE_SUB(NOW(), INTERVAL 34 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),

(9, 10, 10, 4, 'Tradução bem feita e dentro do prazo. Recomendo.', 
 'Obrigado pela avaliação! Estou sempre à disposição.', 
 DATE_SUB(NOW(), INTERVAL 29 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),

(10, 4, 10, 5, 'Tradução juramentada perfeita, documentação aprovada sem problemas.', 
 'Que bom que deu tudo certo! Obrigado pela avaliação.', 
 DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),

(11, 5, 11, 4, 'Boa consultoria, ajudou a melhorar nossas estratégias de marketing.', 
 'Obrigado pela confiança! Continue com as estratégias implementadas.', 
 DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Avaliações adicionais (sem resposta)
INSERT INTO avaliacao (id_interesse, id_avaliador, id_avaliado, nota, comentario, data_avaliacao) VALUES
(12, 11, 7, 5, 'Trabalho fantástico! Mesmo com o cancelamento, o atendimento foi excelente.', 
 DATE_SUB(NOW(), INTERVAL 24 DAY)),
(13, 6, 8, 3, 'Comunicação poderia ser melhor, mas o serviço foi bom.', 
 DATE_SUB(NOW(), INTERVAL 19 DAY));

-- ============================================================================
-- 12. MENSAGENS
-- ============================================================================

-- Mensagens do interesse ativo (contratação da identidade visual)
INSERT INTO mensagem (id_interesse, id_remetente, id_destinatario, mensagem, data_envio) VALUES
(1, 4, 7, 'Olá Ana! Gostei muito do seu trabalho. Gostaria de contratar a identidade visual para minha empresa.', 
 DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 7, 4, 'Olá João! Obrigado pelo interesse. Podemos conversar sobre o projeto? O que você tem em mente?', 
 DATE_SUB(NOW(), INTERVAL 24 DAY)),
(1, 4, 7, 'Preciso de logo, papelaria e redes sociais. Tenho um orçamento de R$ 1500.', 
 DATE_SUB(NOW(), INTERVAL 23 DAY)),
(1, 7, 4, 'Perfeito! Vou preparar uma proposta detalhada com o cronograma.', 
 DATE_SUB(NOW(), INTERVAL 22 DAY)),
(1, 4, 7, 'Ótimo! Estou animado para começar.', 
 DATE_SUB(NOW(), INTERVAL 21 DAY));

-- Mensagens do interesse concluído (sistema de agendamento)
INSERT INTO mensagem (id_interesse, id_remetente, id_destinatario, mensagem, data_envio) VALUES
(7, 8, 8, 'Roberto, preciso de um sistema de agendamento para meu salão de beleza.', 
 DATE_SUB(NOW(), INTERVAL 55 DAY)),
(7, 8, 8, 'Entendido! Qual o seu prazo e orçamento?', 
 DATE_SUB(NOW(), INTERVAL 54 DAY)),
(7, 8, 8, 'Preciso em 30 dias. Orçamento de R$ 4000.', 
 DATE_SUB(NOW(), INTERVAL 53 DAY)),
(7, 8, 8, 'Fechado! Vou iniciar o projeto hoje mesmo.', 
 DATE_SUB(NOW(), INTERVAL 52 DAY)),
(7, 8, 8, 'Sistema concluído. Vou enviar o link para teste.', 
 DATE_SUB(NOW(), INTERVAL 42 DAY)),
(7, 8, 8, 'Funcionou perfeitamente! Muito obrigado.', 
 DATE_SUB(NOW(), INTERVAL 41 DAY));

-- Mensagens do interesse com disputa
INSERT INTO mensagem (id_interesse, id_remetente, id_destinatario, mensagem, data_envio) VALUES
(5, 4, 7, 'Ana, já fiz o pagamento do pix.', 
 DATE_SUB(NOW(), INTERVAL 23 DAY)),
(5, 7, 4, 'Não recebi ainda. Você tem certeza que enviou para a chave correta?', 
 DATE_SUB(NOW(), INTERVAL 22 DAY)),
(5, 4, 7, 'Sim, enviei para o email que você passou. Vou anexar o comprovante.', 
 DATE_SUB(NOW(), INTERVAL 21 DAY)),
(5, 7, 4, 'Verifiquei e realmente não chegou. Vou abrir uma disputa.', 
 DATE_SUB(NOW(), INTERVAL 20 DAY));

-- ============================================================================
-- 13. DENÚNCIAS
-- ============================================================================

INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, id_situacao, data_criacao) VALUES
(4, 9, 16, 'Anúncio ofensivo', 'O anúncio contém conteúdo inadequado.', 2, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(5, 12, 14, 'Falta de profissionalismo', 'O profissional não compareceu ao local combinado.', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(11, 8, 17, 'Conteúdo enganoso', 'O anúncio promete mais do que entrega.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Denúncia analisada por moderador
UPDATE denuncia SET id_situacao = 2, id_moderador_analise = 2, data_analise = DATE_SUB(NOW(), INTERVAL 12 DAY) WHERE id_denuncia = 1;

-- ============================================================================
-- 14. LOGS DE BUSCA
-- ============================================================================

INSERT INTO busca_log (id_usuario, termo_buscado, id_categoria, data_busca) VALUES
(4, 'design', 5, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 'identidade visual', 5, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(5, 'desenvolvimento', 6, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(5, 'site', 6, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 'fotografia', 7, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(6, 'casamento', 7, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 'eletricista', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(8, 'encanamento', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(NULL, 'tradução', 8, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(NULL, 'marketing', 9, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(9, 'fotografia', 7, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(10, 'tradução', 8, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(11, 'consultoria', 9, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(12, 'eletricista', 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(13, 'encanador', 2, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 'designer', 5, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(5, 'sistema', 6, DATE_SUB(NOW(), INTERVAL 6 DAY));

-- ============================================================================
-- 15. LOGS DO SISTEMA
-- ============================================================================

INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, data_criacao, id_usuario) VALUES
('Aprovação de anúncio', 'anuncio_servico', 16, 'Anúncio aprovado pelo moderador', DATE_SUB(NOW(), INTERVAL 14 DAY), 2),
('Rejeição de anúncio', 'anuncio_servico', 18, 'Anúncio rejeitado por conteúdo inadequado', DATE_SUB(NOW(), INTERVAL 13 DAY), 2),
('Resolução de disputa', 'disputa', 1, 'Disputa resolvida a favor do contratante', DATE_SUB(NOW(), INTERVAL 12 DAY), 2),
('Avaliação recebida', 'avaliacao', 1, 'Usuário 7 avaliou usuário 7', DATE_SUB(NOW(), INTERVAL 44 DAY), 7),
('Avaliação recebida', 'avaliacao', 2, 'Usuário 8 avaliou usuário 8', DATE_SUB(NOW(), INTERVAL 39 DAY), 8),
('Novo interesse', 'interesse', 1, 'Usuário 4 demonstrou interesse no anúncio 5', DATE_SUB(NOW(), INTERVAL 25 DAY), 4),
('Novo interesse', 'interesse', 2, 'Usuário 5 demonstrou interesse no anúncio 7', DATE_SUB(NOW(), INTERVAL 20 DAY), 5);

-- ============================================================================
-- 16. NOTIFICAÇÕES
-- ============================================================================

INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id, lida, data_criacao) VALUES
(7, 1, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'João Silva demonstrou interesse no seu anúncio "Criação de Identidade Visual Completa"', 
 'interesse', 1, 0, DATE_SUB(NOW(), INTERVAL 25 DAY)),

(8, 2, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Maria Santos demonstrou interesse no seu anúncio "Desenvolvimento de Sites Profissionais"', 
 'interesse', 2, 0, DATE_SUB(NOW(), INTERVAL 20 DAY)),

(9, 3, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Carlos Oliveira demonstrou interesse no seu anúncio "Fotografia de Casamentos"', 
 'interesse', 3, 0, DATE_SUB(NOW(), INTERVAL 18 DAY)),

(4, 1, 'mensagem_nova', 'Nova mensagem de Ana Paula Costa', 
 'Ana Paula Costa respondeu sua mensagem sobre o anúncio "Criação de Identidade Visual Completa"', 
 'mensagem', 2, 0, DATE_SUB(NOW(), INTERVAL 24 DAY)),

(4, 5, 'nova_disputa', 'Disputa aberta', 
 'Sua disputa sobre o pagamento foi aberta com sucesso.', 
 'disputa', 1, 0, DATE_SUB(NOW(), INTERVAL 20 DAY)),

(7, 5, 'nova_disputa', 'Disputa aberta contra você', 
 'Uma disputa foi aberta sobre o anúncio "Criação de Identidade Visual Completa"', 
 'disputa', 1, 0, DATE_SUB(NOW(), INTERVAL 20 DAY)),

(4, 1, 'pagamento_confirmado', 'Pagamento confirmado', 
 'O pagamento do interesse "Criação de Identidade Visual Completa" foi confirmado pelo contratante', 
 'confirmacao_pagamento', 1, 0, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(7, 1, 'pagamento_confirmado', 'Pagamento confirmado', 
 'O pagamento do interesse "Criação de Identidade Visual Completa" foi confirmado pelo freelancer', 
 'confirmacao_pagamento', 1, 0, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(7, 1, 'avaliacao_recebida', 'Nova avaliação', 
 'João Silva avaliou você com nota 5', 
 'avaliacao', 1, 0, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(4, 1, 'avaliacao_respondida', 'Resposta à sua avaliação', 
 'Ana Paula Costa respondeu sua avaliação', 
 'avaliacao', 1, 0, DATE_SUB(NOW(), INTERVAL 44 DAY));

-- Notificações lidas
INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id, lida, data_criacao, data_leitura) VALUES
(4, 6, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Maria Santos demonstrou interesse no seu anúncio "Design de Redes Sociais"', 
 'interesse', 6, 1, DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 59 DAY)),

(7, 7, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Carlos Oliveira demonstrou interesse no seu anúncio "Desenvolvimento de Sites Profissionais"', 
 'interesse', 7, 1, DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 54 DAY));

-- ============================================================================
-- 17. TOKENS DE RESET DE SENHA
-- ============================================================================

INSERT INTO reset_senha (id_usuario, token, expiracao, usado, data_criacao) VALUES
(7, 'token_expirado_ana_123456', DATE_SUB(NOW(), INTERVAL 5 DAY), 0, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(8, 'token_usado_roberto_789012', DATE_ADD(NOW(), INTERVAL 1 DAY), 1, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================================
-- 18. ATUALIZAÇÃO DE NOTAS MÉDIAS
-- ============================================================================

UPDATE usuario SET nota_media = 4.80, total_avaliacoes = 2 WHERE id_usuario = 7;
UPDATE usuario SET nota_media = 4.60, total_avaliacoes = 2 WHERE id_usuario = 8;
UPDATE usuario SET nota_media = 4.90, total_avaliacoes = 1 WHERE id_usuario = 9;
UPDATE usuario SET nota_media = 4.70, total_avaliacoes = 1 WHERE id_usuario = 10;
UPDATE usuario SET nota_media = 4.50, total_avaliacoes = 1 WHERE id_usuario = 11;
UPDATE usuario SET nota_media = 4.30, total_avaliacoes = 1 WHERE id_usuario = 12;
UPDATE usuario SET nota_media = 4.80, total_avaliacoes = 1 WHERE id_usuario = 13;