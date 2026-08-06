
INSERT INTO categoria (nome, descricao, icone, ativo) VALUES
('Eletricista', 'Serviços elétricos residenciais e comerciais', 'fas fa-bolt', 1),
('Encanador', 'Reparos e instalações hidráulicas', 'fas fa-wrench', 1),
('Diarista', 'Limpeza e organização residencial', 'fas fa-broom', 1),
('Pedreiro', 'Construção, reformas e acabamentos', 'fas fa-hard-hat', 1),
('Pintor', 'Pintura residencial e comercial', 'fas fa-paint-roller', 1),
('Jardineiro', 'Jardinagem e paisagismo', 'fas fa-seedling', 1),
('Cuidador', 'Cuidados com idosos e crianças', 'fas fa-hands', 1),
('Design Gráfico', 'Criação de identidade visual, logos, materiais gráficos', 'fas fa-palette', 1),
('Desenvolvimento Web', 'Criação de sites, sistemas e aplicações web', 'fas fa-code', 1),
('Fotografia', 'Fotografia profissional para eventos, produtos e ensaios', 'fas fa-camera', 1),
('Tradução', 'Tradução de documentos, textos e interpretação', 'fas fa-language', 1),
('Consultoria', 'Consultoria empresarial, de marketing e gestão', 'fas fa-chart-line', 1),
('Outros', 'Diversos serviços gerais', 'fas fa-tools', 1);

-- ============================================================================
-- 2. HABILIDADES (com os IDs corretos das categorias)
-- ============================================================================

-- Design Gráfico (categoria 8)
INSERT INTO habilidade (id_categoria, nome) VALUES
(8, 'Adobe Photoshop'),
(8, 'Adobe Illustrator'),
(8, 'Adobe InDesign'),
(8, 'Identidade Visual'),
(8, 'Social Media Design'),
(8, 'Design de E-books'),
(8, 'Criação de Logotipos');

-- Desenvolvimento Web (categoria 9)
INSERT INTO habilidade (id_categoria, nome) VALUES
(9, 'PHP'),
(9, 'JavaScript'),
(9, 'React'),
(9, 'Node.js'),
(9, 'HTML/CSS'),
(9, 'MySQL'),
(9, 'React Native'),
(9, 'APIs RESTful');

-- Fotografia (categoria 10)
INSERT INTO habilidade (id_categoria, nome) VALUES
(10, 'Fotografia de Produto'),
(10, 'Fotografia de Casamento'),
(10, 'Edição de Imagens'),
(10, 'Iluminação'),
(10, 'Fotografia de Retratos'),
(10, 'Fotografia de Eventos');

-- Tradução (categoria 11)
INSERT INTO habilidade (id_categoria, nome) VALUES
(11, 'Português-Inglês'),
(11, 'Inglês-Português'),
(11, 'Espanhol-Português'),
(11, 'Tradução Juramentada'),
(11, 'Interpretação Simultânea');

-- Consultoria (categoria 12)
INSERT INTO habilidade (id_categoria, nome) VALUES
(12, 'Marketing Digital'),
(12, 'Gestão de Projetos'),
(12, 'Análise de Dados'),
(12, 'Growth Hacking'),
(12, 'Planejamento Estratégico');

-- Eletricista (categoria 1)
INSERT INTO habilidade (id_categoria, nome) VALUES
(1, 'Instalação Elétrica'),
(1, 'Manutenção Elétrica'),
(1, 'Automação Residencial');

-- Encanador (categoria 2)
INSERT INTO habilidade (id_categoria, nome) VALUES
(2, 'Desentupimento'),
(2, 'Instalação Hidráulica'),
(2, 'Reparo de Vazamentos');

-- Diarista (categoria 3)
INSERT INTO habilidade (id_categoria, nome) VALUES
(3, 'Limpeza Pesada'),
(3, 'Organização de Ambientes'),
(3, 'Higienização');

-- Pedreiro (categoria 4)
INSERT INTO habilidade (id_categoria, nome) VALUES
(4, 'Construção'),
(4, 'Reformas'),
(4, 'Acabamentos');

-- Pintor (categoria 5)
INSERT INTO habilidade (id_categoria, nome) VALUES
(5, 'Pintura Interna'),
(5, 'Pintura Externa');

-- Jardineiro (categoria 6)
INSERT INTO habilidade (id_categoria, nome) VALUES
(6, 'Jardinagem'),
(6, 'Paisagismo'),
(6, 'Poda de Árvores');

-- Cuidador (categoria 7)
INSERT INTO habilidade (id_categoria, nome) VALUES
(7, 'Cuidados com Idosos'),
(7, 'Cuidados com Crianças'),
(7, 'Acompanhamento Médico');

-- Outros (categoria 13)
INSERT INTO habilidade (id_categoria, nome) VALUES
(13, 'Serviços Gerais');

-- ============================================================================
-- 3. USUÁRIOS ADICIONAIS (FREELANCERS E CLIENTES)
-- ============================================================================

-- Os usuários 1 a 4 já existem (admin, moderador, usuario, master)
-- Vamos criar mais usuários para simular o sistema em uso

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
 'Salvador', 'BA', 4.80, 9, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 130 DAY)),

(3, 'João Silva', 'joao@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '11991234567', '11991234567', '98765432100', '1982-07-20', 'joao_perfil.jpg', 
 'Pedreiro com 15 anos de experiência em obras e reformas', 
 'São Paulo', 'SP', 4.70, 7, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 110 DAY)),

(3, 'Maria Santos', 'maria.santos@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '21997654321', '21997654321', '45612378912', '1989-10-05', 'maria_perfil.jpg', 
 'Diarista e organizadora de ambientes', 
 'Rio de Janeiro', 'RJ', 4.90, 14, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 160 DAY)),

(3, 'Carlos Oliveira', 'carlos@aptus.com', '$2y$10$SnllgubFRD7R8JZpxkCpxOwXTvW1DARdwXkSxMYBc5qs/eUm8eCiG', 
 '31999876543', '31999876543', '78945612301', '1986-03-25', 'carlos_perfil.jpg', 
 'Jardineiro especializado em paisagismo', 
 'Belo Horizonte', 'MG', 4.60, 5, 1, 0, 1, NOW(), DATE_SUB(NOW(), INTERVAL 140 DAY));

-- ============================================================================
-- 4. HABILIDADES DOS USUÁRIOS
-- ============================================================================

-- Nota: Os IDs das habilidades dependem da ordem de inserção.
-- Vamos usar SELECT para obter os IDs corretos dinamicamente.
-- Mas como é um script de exemplo, usaremos valores fixos baseados na ordem.

-- Para simplificar, vamos supor os seguintes IDs (ajuste conforme necessário):
-- Habilidades: 1-5 Design, 6-13 Web, 14-19 Fotografia, 20-24 Tradução, 25-29 Consultoria, 
--             30-32 Eletricista, 33-35 Encanador, 36-38 Diarista, 39-41 Pedreiro,
--             42-43 Pintor, 44-46 Jardineiro, 47-49 Cuidador, 50 Serviços Gerais

-- Mas como não temos certeza, vamos usar INSERT IGNORE ou SELECT com subconsultas.
-- Vou usar subconsultas para garantir.

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 5, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Adobe Photoshop' UNION
SELECT 5, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Adobe Illustrator' UNION
SELECT 5, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Adobe InDesign' UNION
SELECT 5, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Identidade Visual' UNION
SELECT 5, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Social Media Design';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 6, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'PHP' UNION
SELECT 6, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'JavaScript' UNION
SELECT 6, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'React' UNION
SELECT 6, id_habilidade, 'basico' FROM habilidade WHERE nome = 'Node.js' UNION
SELECT 6, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'HTML/CSS' UNION
SELECT 6, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'MySQL';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 7, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Fotografia de Casamento' UNION
SELECT 7, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Edição de Imagens' UNION
SELECT 7, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Iluminação' UNION
SELECT 7, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Fotografia de Eventos';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 8, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Português-Inglês' UNION
SELECT 8, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Inglês-Português' UNION
SELECT 8, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Espanhol-Português' UNION
SELECT 8, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Tradução Juramentada';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 9, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Marketing Digital' UNION
SELECT 9, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Gestão de Projetos' UNION
SELECT 9, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Análise de Dados';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 10, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Instalação Elétrica' UNION
SELECT 10, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Manutenção Elétrica';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 11, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Desentupimento' UNION
SELECT 11, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Instalação Hidráulica';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 12, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Construção' UNION
SELECT 12, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Reformas' UNION
SELECT 12, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Acabamentos';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 13, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Limpeza Pesada' UNION
SELECT 13, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Organização de Ambientes';

INSERT INTO usuario_habilidade (id_usuario, id_habilidade, nivel)
SELECT 14, id_habilidade, 'avancado' FROM habilidade WHERE nome = 'Jardinagem' UNION
SELECT 14, id_habilidade, 'intermediario' FROM habilidade WHERE nome = 'Paisagismo' UNION
SELECT 14, id_habilidade, 'basico' FROM habilidade WHERE nome = 'Poda de Árvores';

-- ============================================================================
-- 5. PORTFÓLIO DOS USUÁRIOS
-- ============================================================================

INSERT INTO portfolio (id_usuario, titulo, descricao, imagem, ordem, data_criacao) VALUES
(5, 'Identidade Visual - Empresa Tech', 'Criação de logo, papelaria e redes sociais para startup de tecnologia', 
 'portfolio_ana_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(5, 'Redesign de Marca - Café Gourmet', 'Projeto completo de rebranding para cafeteria especializada', 
 'portfolio_ana_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(5, 'Material Gráfico - Evento Cultural', 'Cartazes, banners e flyers para festival de música', 
 'portfolio_ana_3.jpg', 3, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(6, 'Sistema de Gestão - Empresa de Logística', 'Desenvolvimento completo de sistema web com PHP e MySQL', 
 'portfolio_roberto_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(6, 'E-commerce - Loja de Roupas', 'Loja virtual completa com integração de pagamento', 
 'portfolio_roberto_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(7, 'Ensaio Fotográfico - Casamento', 'Fotos de casamento realizado na praia', 
 'portfolio_carla_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 120 DAY)),
(7, 'Fotografia de Produto - Joias', 'Catálogo de joias para loja de luxo', 
 'portfolio_carla_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(7, 'Ensaio Gestacional', 'Ensaio fotográfico de gestante em estúdio', 
 'portfolio_carla_3.jpg', 3, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(8, 'Tradução - Manual Técnico', 'Tradução de manual de equipamentos industriais (Inglês-Português)', 
 'portfolio_fernando_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(8, 'Interpretação - Conferência', 'Interpretação simultânea em conferência internacional', 
 'portfolio_fernando_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 55 DAY)),
(9, 'Plano de Marketing - Startup', 'Estratégia completa de marketing digital para startup', 
 'portfolio_mariana_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(10, 'Reforma Elétrica - Edifício Comercial', 'Projeto de reforma elétrica completa', 
 'portfolio_lucas_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(11, 'Reforma Hidráulica - Residência', 'Substituição completa de encanamento', 
 'portfolio_patricia_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(12, 'Construção de Muro', 'Muro de arrimo e muro divisório', 
 'portfolio_joao_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(13, 'Limpeza Pós-Obra', 'Higienização completa de apartamento após reforma', 
 'portfolio_maria_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(14, 'Projeto de Paisagismo', 'Projeto completo de jardim para condomínio', 
 'portfolio_carlos_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 25 DAY));

-- ============================================================================
-- 6. ANÚNCIOS DE SERVIÇO
-- ============================================================================

INSERT INTO anuncio_servico (
    id_usuario, id_categoria, titulo, descricao, slug, preco, 
    situacao, id_situacao_moderacao, foto_capa, visualizacoes, data_criacao
) VALUES
(5, 8, 'Criação de Identidade Visual Completa', 
 'Crio logos, paletas de cores, tipografia e todos os elementos visuais para sua marca.', 
 'identidade-visual-completa', 1500.00, 'ativo', 2, 'anuncio_ana_1.jpg', 45, DATE_SUB(NOW(), INTERVAL 80 DAY)),

(5, 8, 'Design de Redes Sociais', 
 'Criação de posts, stories e arte para redes sociais', 
 'design-redes-sociais', 800.00, 'ativo', 2, 'anuncio_ana_2.jpg', 32, DATE_SUB(NOW(), INTERVAL 60 DAY)),

(6, 9, 'Desenvolvimento de Sites Profissionais', 
 'Criação de sites responsivos e otimizados para SEO', 
 'desenvolvimento-sites', 2500.00, 'ativo', 2, 'anuncio_roberto_1.jpg', 38, DATE_SUB(NOW(), INTERVAL 90 DAY)),

(6, 9, 'Sistemas Web Personalizados', 
 'Desenvolvimento de sistemas sob medida para sua empresa', 
 'sistemas-web-personalizados', 4000.00, 'ativo', 2, 'anuncio_roberto_2.jpg', 28, DATE_SUB(NOW(), INTERVAL 70 DAY)),

(7, 10, 'Fotografia de Casamentos', 
 'Cobertura completa de casamentos com ensaio e making of', 
 'fotografia-casamentos', 3000.00, 'ativo', 2, 'anuncio_carla_1.jpg', 52, DATE_SUB(NOW(), INTERVAL 100 DAY)),

(7, 10, 'Ensaio Fotográfico Profissional', 
 'Ensaio para perfis, currículos e portfólios', 
 'ensaio-fotografico', 600.00, 'ativo', 2, 'anuncio_carla_2.jpg', 25, DATE_SUB(NOW(), INTERVAL 50 DAY)),

(8, 11, 'Tradução de Documentos', 
 'Tradução de documentos técnicos, acadêmicos e oficiais', 
 'traducao-documentos', 120.00, 'ativo', 2, 'anuncio_fernando_1.jpg', 20, DATE_SUB(NOW(), INTERVAL 75 DAY)),

(8, 11, 'Tradução Juramentada', 
 'Tradução de documentos oficiais com validade jurídica', 
 'traducao-juramentada', 250.00, 'ativo', 2, 'anuncio_fernando_2.jpg', 15, DATE_SUB(NOW(), INTERVAL 65 DAY)),

(9, 12, 'Consultoria em Marketing Digital', 
 'Elaboração de estratégia de marketing digital para seu negócio', 
 'consultoria-marketing', 1800.00, 'ativo', 2, 'anuncio_mariana_1.jpg', 30, DATE_SUB(NOW(), INTERVAL 55 DAY)),

(9, 12, 'Análise de Dados para Negócios', 
 'Análise de dados e criação de dashboards para tomada de decisão', 
 'analise-dados-negocios', 2000.00, 'ativo', 2, 'anuncio_mariana_2.jpg', 18, DATE_SUB(NOW(), INTERVAL 40 DAY)),

(10, 1, 'Serviços Elétricos Residenciais', 
 'Instalação, reparo e manutenção elétrica', 
 'servicos-eletricos', 200.00, 'ativo', 2, 'anuncio_lucas_1.jpg', 60, DATE_SUB(NOW(), INTERVAL 85 DAY)),

(11, 2, 'Serviços de Encanamento', 
 'Desentupimento, instalação e reparos de encanamento', 
 'servicos-encanamento', 250.00, 'ativo', 2, 'anuncio_patricia_1.jpg', 45, DATE_SUB(NOW(), INTERVAL 70 DAY)),

(12, 4, 'Pedreiro - Reformas e Construção', 
 'Realizo reformas, construções e acabamentos', 
 'pedreiro-reformas', 350.00, 'ativo', 2, 'anuncio_joao_1.jpg', 40, DATE_SUB(NOW(), INTERVAL 60 DAY)),

(13, 3, 'Diarista - Limpeza e Organização', 
 'Limpeza pesada, organização e higienização', 
 'diarista-limpeza', 150.00, 'ativo', 2, 'anuncio_maria_1.jpg', 55, DATE_SUB(NOW(), INTERVAL 75 DAY)),

(14, 6, 'Jardinagem e Paisagismo', 
 'Projetos de jardinagem, paisagismo e manutenção', 
 'jardinagem-paisagismo', 280.00, 'ativo', 2, 'anuncio_carlos_1.jpg', 22, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(5, 8, 'Design de E-books', 
 'Criação de e-books profissionais', 
 'design-ebooks', 500.00, 'pausado', 2, 'anuncio_ana_3.jpg', 12, DATE_SUB(NOW(), INTERVAL 30 DAY)),

(6, 9, 'Aplicativos Mobile', 
 'Desenvolvimento de apps híbridos com React Native', 
 'apps-mobile', 3500.00, 'ativo', 1, 'anuncio_roberto_3.jpg', 5, DATE_SUB(NOW(), INTERVAL 15 DAY)),

(7, 10, 'Fotografia de Produtos', 
 'Fotos profissionais para e-commerce', 
 'fotografia-produtos', 400.00, 'ativo', 3, NULL, 10, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- ============================================================================
-- 7. FOTOS ADICIONAIS DOS ANÚNCIOS
-- ============================================================================

INSERT INTO anuncio_foto (id_anuncio, arquivo, ordem, data_criacao) VALUES
(1, 'ana_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(1, 'ana_anuncio1_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(2, 'ana_anuncio2_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(3, 'roberto_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(3, 'roberto_anuncio1_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(4, 'roberto_anuncio2_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(5, 'carla_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 100 DAY)),
(5, 'carla_anuncio1_2.jpg', 2, DATE_SUB(NOW(), INTERVAL 100 DAY)),
(6, 'carla_anuncio2_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(11, 'lucas_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 85 DAY)),
(12, 'patricia_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(13, 'joao_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(14, 'maria_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 75 DAY)),
(15, 'carlos_anuncio1_1.jpg', 1, DATE_SUB(NOW(), INTERVAL 45 DAY));

-- ============================================================================
-- 8. FAVORITOS
-- ============================================================================

INSERT INTO favorito (id_usuario, id_anuncio, data_criacao) VALUES
(3, 1, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(3, 3, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(3, 5, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(4, 2, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(4, 4, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(4, 11, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(5, 5, DATE_SUB(NOW(), INTERVAL 55 DAY)),
(5, 6, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(5, 12, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6, 14, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(6, 15, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(7, 1, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(7, 2, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(8, 3, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(8, 4, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(9, 5, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(9, 6, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(10, 1, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(10, 11, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- ============================================================================
-- 9. INTERESSES (NEGOCIAÇÕES)
-- ============================================================================

INSERT INTO interesse (id_anuncio, id_contratante, id_freelancer, mensagem_inicial, situacao, data_interesse, data_conclusao) VALUES
(1, 3, 5, 'Olá Ana, gostei muito do seu trabalho! Gostaria de contratar a identidade visual para minha empresa.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 25 DAY), NULL),

(3, 4, 6, 'Preciso de um site para minha empresa de consultoria. Podemos conversar?', 
 'ativo', DATE_SUB(NOW(), INTERVAL 20 DAY), NULL),

(5, 5, 7, 'Carla, gostei do seu portfólio de casamentos. Gostaria de fazer um orçamento para meu casamento.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 18 DAY), NULL),

(11, 4, 10, 'Lucas, preciso de uma reforma elétrica completa na minha casa.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 15 DAY), NULL),

(12, 3, 11, 'Patrícia, preciso de desentupimento urgente no meu banheiro.', 
 'ativo', DATE_SUB(NOW(), INTERVAL 12 DAY), NULL),

(2, 6, 5, 'Preciso de posts para redes sociais da minha loja.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),

(4, 7, 6, 'Preciso de um sistema de agendamento para meu salão.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),

(6, 8, 7, 'Quero fazer um ensaio fotográfico profissional.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),

(7, 9, 8, 'Preciso traduzir documentos para um processo.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),

(8, 3, 8, 'Preciso de tradução juramentada do meu diploma.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),

(9, 4, 9, 'Quero consultoria de marketing para minha startup.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),

(10, 5, 9, 'Preciso de análise de dados para minha empresa.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),

(13, 6, 12, 'Preciso de um pedreiro para fazer uma reforma na minha casa.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)),

(14, 4, 13, 'Preciso de diarista para limpeza semanal.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),

(15, 3, 14, 'Quero um projeto de paisagismo para meu jardim.', 
 'concluido', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),

(1, 10, 5, 'Gostaria de um orçamento para design de logo.', 
 'cancelado', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),

(3, 5, 6, 'Preciso de um site para minha loja virtual.', 
 'cancelado', DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),

(11, 3, 10, 'Lucas, preciso de um eletricista urgente.', 
 'cancelado', DATE_SUB(NOW(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY));

-- ============================================================================
-- 10. CONFIRMAÇÕES DE PAGAMENTO
-- ============================================================================

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
 'confirmado', DATE_SUB(NOW(), INTERVAL 35 DAY)),

(12, 1, 2000.00, 'pix', DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY),
 1, 2000.00, DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 30 DAY)),

(13, 1, 350.00, 'dinheiro', DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY),
 1, 350.00, DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 28 DAY)),

(14, 1, 150.00, 'pix', DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY),
 1, 150.00, DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 25 DAY)),

(15, 1, 280.00, 'transferencia', DATE_SUB(NOW(), INTERVAL 11 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY),
 1, 280.00, DATE_SUB(NOW(), INTERVAL 11 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), 
 'confirmado', DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Pagamento divergente
INSERT INTO confirmacao_pagamento (
    id_interesse, confirmado_contratante, valor_informado_contratante, 
    forma_pagamento_contratante, data_pagamento_contratante, data_confirmacao_contratante,
    confirmado_freelancer, valor_informado_freelancer, data_recebimento_freelancer, 
    data_confirmacao_freelancer, situacao_final, data_criacao
) VALUES
(1, 1, 1500.00, 'pix', DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 23 DAY),
 1, 1500.00, DATE_SUB(NOW(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY), 
 'divergente', DATE_SUB(NOW(), INTERVAL 30 DAY));

-- ============================================================================
-- 11. DISPUTAS
-- ============================================================================

INSERT INTO disputa (
    id_interesse, id_aberto_por, motivo, descricao, id_situacao, 
    id_responsavel, resposta, data_abertura, data_resolucao
) VALUES
(1, 3, 'Pagamento não reconhecido', 
 'O contratante alega que o pagamento foi feito, mas o freelancer não confirma o recebimento.', 
 2, 2, 'Após análise dos comprovantes enviados por ambas as partes, verificamos que o pagamento foi realizado corretamente. A disputa foi resolvida a favor do contratante.', 
 DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY));

INSERT INTO disputa_anexo (id_disputa, id_usuario, arquivo, data_criacao) VALUES
(1, 3, 'comprovante_pagamento_contratante.jpg', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 5, 'comprovante_pagamento_freelancer.jpg', DATE_SUB(NOW(), INTERVAL 19 DAY));

-- ============================================================================
-- 12. AVALIAÇÕES
-- ============================================================================

INSERT INTO avaliacao (
    id_interesse, id_avaliador, id_avaliado, nota, comentario, 
    resposta_avaliado, data_resposta, data_avaliacao
) VALUES
(6, 6, 5, 5, 'Excelente profissional! Entregou o projeto antes do prazo e com muita qualidade. Super recomendo.', 
 'Muito obrigado pela avaliação! Foi um prazer trabalhar com você.', 
 DATE_SUB(NOW(), INTERVAL 44 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),

(7, 7, 6, 4, 'Ótimo desenvolvimento, embora tenha atrasado um pouco na entrega. O resultado final é excelente.', 
 'Peço desculpas pelo atraso, tivemos um imprevisto, mas fico feliz que gostou do resultado.', 
 DATE_SUB(NOW(), INTERVAL 39 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),

(8, 8, 7, 5, 'Fotos maravilhosas! Ficou exatamente como eu queria. A Carla é muito profissional e criativa.', 
 'Foi um prazer realizar esse ensaio! Obrigado pela confiança.', 
 DATE_SUB(NOW(), INTERVAL 34 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),

(9, 9, 8, 4, 'Tradução bem feita e dentro do prazo. Recomendo.', 
 'Obrigado pela avaliação! Estou sempre à disposição.', 
 DATE_SUB(NOW(), INTERVAL 29 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),

(10, 3, 8, 5, 'Tradução juramentada perfeita, documentação aprovada sem problemas.', 
 'Que bom que deu tudo certo! Obrigado pela avaliação.', 
 DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),

(11, 4, 9, 4, 'Boa consultoria, ajudou a melhorar nossas estratégias de marketing.', 
 'Obrigado pela confiança! Continue com as estratégias implementadas.', 
 DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),

(12, 5, 9, 5, 'Análise de dados muito útil para a tomada de decisão.', 
 'Fico feliz em ajudar!', 
 DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),

(13, 6, 12, 5, 'Trabalho fantástico! Reforma ficou impecável.', 
 'Obrigado, foi um prazer!', 
 DATE_SUB(NOW(), INTERVAL 17 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)),

(14, 4, 13, 4, 'Boa limpeza, mas poderia ser mais detalhista.', 
 'Obrigado pelo feedback! Vou melhorar.', 
 DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),

(15, 3, 14, 5, 'Jardim ficou lindo! Super profissional.', 
 'Obrigado, foi um projeto incrível!', 
 DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- Avaliações sem resposta
INSERT INTO avaliacao (id_interesse, id_avaliador, id_avaliado, nota, comentario, data_avaliacao) VALUES
(16, 10, 5, 5, 'Trabalho fantástico! Mesmo com o cancelamento, o atendimento foi excelente.', 
 DATE_SUB(NOW(), INTERVAL 24 DAY)),
(17, 5, 6, 3, 'Comunicação poderia ser melhor, mas o serviço foi bom.', 
 DATE_SUB(NOW(), INTERVAL 19 DAY)),
(18, 3, 10, 4, 'Profissional atencioso e serviço bem feito.', 
 DATE_SUB(NOW(), INTERVAL 17 DAY));

-- ============================================================================
-- 13. MENSAGENS
-- ============================================================================

INSERT INTO mensagem (id_interesse, id_remetente, id_destinatario, mensagem, data_envio) VALUES
(1, 3, 5, 'Olá Ana! Gostei muito do seu trabalho. Gostaria de contratar a identidade visual para minha empresa.', 
 DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 5, 3, 'Olá João! Obrigado pelo interesse. Podemos conversar sobre o projeto? O que você tem em mente?', 
 DATE_SUB(NOW(), INTERVAL 24 DAY)),
(1, 3, 5, 'Preciso de logo, papelaria e redes sociais. Tenho um orçamento de R$ 1500.', 
 DATE_SUB(NOW(), INTERVAL 23 DAY)),
(1, 5, 3, 'Perfeito! Vou preparar uma proposta detalhada com o cronograma.', 
 DATE_SUB(NOW(), INTERVAL 22 DAY)),
(1, 3, 5, 'Ótimo! Estou animado para começar.', 
 DATE_SUB(NOW(), INTERVAL 21 DAY)),

(7, 7, 6, 'Roberto, preciso de um sistema de agendamento para meu salão de beleza.', 
 DATE_SUB(NOW(), INTERVAL 55 DAY)),
(7, 6, 7, 'Entendido! Qual o seu prazo e orçamento?', 
 DATE_SUB(NOW(), INTERVAL 54 DAY)),
(7, 7, 6, 'Preciso em 30 dias. Orçamento de R$ 4000.', 
 DATE_SUB(NOW(), INTERVAL 53 DAY)),
(7, 6, 7, 'Fechado! Vou iniciar o projeto hoje mesmo.', 
 DATE_SUB(NOW(), INTERVAL 52 DAY)),
(7, 6, 7, 'Sistema concluído. Vou enviar o link para teste.', 
 DATE_SUB(NOW(), INTERVAL 42 DAY)),
(7, 7, 6, 'Funcionou perfeitamente! Muito obrigado.', 
 DATE_SUB(NOW(), INTERVAL 41 DAY)),

(1, 3, 5, 'Ana, já fiz o pagamento do pix.', 
 DATE_SUB(NOW(), INTERVAL 23 DAY)),
(1, 5, 3, 'Não recebi ainda. Você tem certeza que enviou para a chave correta?', 
 DATE_SUB(NOW(), INTERVAL 22 DAY)),
(1, 3, 5, 'Sim, enviei para o email que você passou. Vou anexar o comprovante.', 
 DATE_SUB(NOW(), INTERVAL 21 DAY)),
(1, 5, 3, 'Verifiquei e realmente não chegou. Vou abrir uma disputa.', 
 DATE_SUB(NOW(), INTERVAL 20 DAY));

-- ============================================================================
-- 14. DENÚNCIAS
-- ============================================================================

INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, id_situacao, data_criacao) VALUES
(3, 7, 18, 'Anúncio ofensivo', 'O anúncio contém conteúdo inadequado.', 2, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(4, 10, 17, 'Falta de profissionalismo', 'O profissional não compareceu ao local combinado.', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 6, 16, 'Conteúdo enganoso', 'O anúncio promete mais do que entrega.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Denúncia analisada
UPDATE denuncia SET id_situacao = 2, id_moderador_analise = 2, data_analise = DATE_SUB(NOW(), INTERVAL 12 DAY) WHERE id_denuncia = 1;

-- ============================================================================
-- 15. LOGS DE BUSCA
-- ============================================================================

INSERT INTO busca_log (id_usuario, termo_buscado, id_categoria, data_busca) VALUES
(3, 'design', 8, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, 'identidade visual', 8, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(4, 'desenvolvimento', 9, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 'site', 9, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(5, 'fotografia', 10, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(5, 'casamento', 10, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 'eletricista', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 'encanamento', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(NULL, 'tradução', 11, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(NULL, 'marketing', 12, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 'fotografia', 10, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(9, 'tradução', 11, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(10, 'consultoria', 12, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(11, 'eletricista', 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(12, 'encanador', 2, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(13, 'pedreiro', 4, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(14, 'diarista', 3, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 'jardineiro', 6, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 'cuidador', 7, DATE_SUB(NOW(), INTERVAL 4 DAY));

-- ============================================================================
-- 16. LOGS DO SISTEMA
-- ============================================================================

INSERT INTO log_sistema (acao, tabela_afetada, registro_id, detalhes, data_criacao, id_usuario) VALUES
('Aprovação de anúncio', 'anuncio_servico', 16, 'Anúncio aprovado pelo moderador', DATE_SUB(NOW(), INTERVAL 14 DAY), 2),
('Rejeição de anúncio', 'anuncio_servico', 18, 'Anúncio rejeitado por conteúdo inadequado', DATE_SUB(NOW(), INTERVAL 13 DAY), 2),
('Resolução de disputa', 'disputa', 1, 'Disputa resolvida a favor do contratante', DATE_SUB(NOW(), INTERVAL 12 DAY), 2),
('Avaliação recebida', 'avaliacao', 1, 'Usuário 6 avaliou usuário 5', DATE_SUB(NOW(), INTERVAL 44 DAY), 6),
('Avaliação recebida', 'avaliacao', 2, 'Usuário 7 avaliou usuário 6', DATE_SUB(NOW(), INTERVAL 39 DAY), 7),
('Novo interesse', 'interesse', 1, 'Usuário 3 demonstrou interesse no anúncio 1', DATE_SUB(NOW(), INTERVAL 25 DAY), 3),
('Novo interesse', 'interesse', 2, 'Usuário 4 demonstrou interesse no anúncio 3', DATE_SUB(NOW(), INTERVAL 20 DAY), 4);

-- ============================================================================
-- 17. NOTIFICAÇÕES
-- ============================================================================

INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id, lida, data_criacao) VALUES
(5, 1, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'João Silva demonstrou interesse no seu anúncio "Criação de Identidade Visual Completa"', 
 'interesse', 1, 0, DATE_SUB(NOW(), INTERVAL 25 DAY)),

(6, 2, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Maria Santos demonstrou interesse no seu anúncio "Desenvolvimento de Sites Profissionais"', 
 'interesse', 2, 0, DATE_SUB(NOW(), INTERVAL 20 DAY)),

(7, 3, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Carlos Oliveira demonstrou interesse no seu anúncio "Fotografia de Casamentos"', 
 'interesse', 3, 0, DATE_SUB(NOW(), INTERVAL 18 DAY)),

(3, 1, 'mensagem_nova', 'Nova mensagem de Ana Paula Costa', 
 'Ana Paula Costa respondeu sua mensagem sobre o anúncio "Criação de Identidade Visual Completa"', 
 'mensagem', 2, 0, DATE_SUB(NOW(), INTERVAL 24 DAY)),

(3, 1, 'nova_disputa', 'Disputa aberta', 
 'Sua disputa sobre o pagamento foi aberta com sucesso.', 
 'disputa', 1, 0, DATE_SUB(NOW(), INTERVAL 20 DAY)),

(5, 1, 'nova_disputa', 'Disputa aberta contra você', 
 'Uma disputa foi aberta sobre o anúncio "Criação de Identidade Visual Completa"', 
 'disputa', 1, 0, DATE_SUB(NOW(), INTERVAL 20 DAY)),

(3, 6, 'pagamento_confirmado', 'Pagamento confirmado', 
 'O pagamento do interesse "Design de Redes Sociais" foi confirmado pelo contratante', 
 'confirmacao_pagamento', 1, 0, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(5, 6, 'pagamento_confirmado', 'Pagamento confirmado', 
 'O pagamento do interesse "Design de Redes Sociais" foi confirmado pelo freelancer', 
 'confirmacao_pagamento', 1, 0, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(5, 6, 'avaliacao_recebida', 'Nova avaliação', 
 'João Silva avaliou você com nota 5', 
 'avaliacao', 1, 0, DATE_SUB(NOW(), INTERVAL 45 DAY)),

(3, 6, 'avaliacao_respondida', 'Resposta à sua avaliação', 
 'Ana Paula Costa respondeu sua avaliação', 
 'avaliacao', 1, 0, DATE_SUB(NOW(), INTERVAL 44 DAY));

-- Notificações lidas
INSERT INTO notificacao (id_usuario, id_interesse, tipo, titulo, mensagem, tabela_origem, registro_id, lida, data_criacao, data_leitura) VALUES
(3, 7, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Carlos Oliveira demonstrou interesse no seu anúncio "Design de Redes Sociais"', 
 'interesse', 6, 1, DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 59 DAY)),

(6, 8, 'novo_interesse', 'Novo interesse no seu anúncio', 
 'Mariana Souza demonstrou interesse no seu anúncio "Desenvolvimento de Sites Profissionais"', 
 'interesse', 7, 1, DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 54 DAY));

-- ============================================================================
-- 18. TOKENS DE RESET DE SENHA
-- ============================================================================

INSERT INTO reset_senha (id_usuario, token, expiracao, usado, data_criacao) VALUES
(5, 'token_expirado_ana_123456', DATE_SUB(NOW(), INTERVAL 5 DAY), 0, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(6, 'token_usado_roberto_789012', DATE_ADD(NOW(), INTERVAL 1 DAY), 1, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================================
-- 19. ATUALIZAÇÃO DE NOTAS MÉDIAS
-- ============================================================================

UPDATE usuario SET nota_media = 4.80, total_avaliacoes = 2 WHERE id_usuario = 5;
UPDATE usuario SET nota_media = 4.60, total_avaliacoes = 2 WHERE id_usuario = 6;
UPDATE usuario SET nota_media = 4.90, total_avaliacoes = 2 WHERE id_usuario = 7;
UPDATE usuario SET nota_media = 4.70, total_avaliacoes = 2 WHERE id_usuario = 8;
UPDATE usuario SET nota_media = 4.50, total_avaliacoes = 2 WHERE id_usuario = 9;
UPDATE usuario SET nota_media = 4.30, total_avaliacoes = 1 WHERE id_usuario = 10;
UPDATE usuario SET nota_media = 4.80, total_avaliacoes = 2 WHERE id_usuario = 11;
UPDATE usuario SET nota_media = 4.70, total_avaliacoes = 1 WHERE id_usuario = 12;
UPDATE usuario SET nota_media = 4.90, total_avaliacoes = 2 WHERE id_usuario = 13;
UPDATE usuario SET nota_media = 4.60, total_avaliacoes = 1 WHERE id_usuario = 14;