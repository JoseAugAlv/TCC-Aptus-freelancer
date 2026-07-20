<?php
// routes/web.php

/*
   PÁGINAS PÚBLICAS
*/
$router->get('/', 'HomeController@index');
$router->get('/buscar', 'HomeController@buscar');
$router->get('/sobre', 'SobreController@index');

// Contato - GET exibe o formulário, POST processa o envio
$router->get('/contato', 'ContatoController@index');
$router->post('/contato', 'ContatoController@index');

// ... resto das rotas
/*
   ANÚNCIOS (Público)
*/
$router->get('/anuncios', 'AnuncioController@index');
$router->get('/anuncios/criar', 'AnuncioController@criar', [3, 2, 1, 4]);
$router->get('/anuncios/{slug}', 'AnuncioController@show');

/*
   AUTENTICAÇÃO
*/
$router->get('/login', 'AuthController@index');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/login/cadastrar', 'AuthController@cadastrar');
$router->post('/login/salvar', 'AuthController@salvar');

$router->get('/auth/verificar', 'AuthController@verificar');
$router->get('/auth/esqueci-senha', 'AuthController@esqueciSenha');
$router->post('/auth/enviar-token', 'AuthController@enviarToken');
$router->get('/auth/redefinir', 'AuthController@redefinir');
$router->post('/auth/redefinir-senha', 'AuthController@redefinirSenha');

/*
   PERFIL DO USUÁRIO (Usuário logado)
*/
// routes/web.php
$router->get('/perfil', 'PerfilController@index', [3, 2, 1, 4]);
$router->get('/perfil/editar', 'PerfilController@editar', [3, 2, 1, 4]);
$router->post('/perfil/atualizar', 'PerfilController@atualizar', [3, 2, 1, 4]);
$router->get('/perfil/portfolio', 'PerfilController@portfolio', [3, 2, 1, 4]);
$router->get('/perfil/publico/{id}', 'PerfilController@publico');
/*
   PORTFÓLIO
*/
$router->get('/perfil/portfolio', 'PortfolioController@index', [3, 2, 1, 4]);
$router->get('/perfil/portfolio/criar', 'PortfolioController@criar', [3, 2, 1, 4]);
$router->post('/perfil/portfolio/salvar', 'PortfolioController@salvar', [3, 2, 1, 4]);
$router->get('/perfil/portfolio/editar/{id}', 'PortfolioController@editar', [3, 2, 1, 4]);
$router->post('/perfil/portfolio/atualizar', 'PortfolioController@atualizar', [3, 2, 1, 4]);
$router->get('/perfil/portfolio/excluir/{id}', 'PortfolioController@excluir', [3, 2, 1, 4]);

/*
   ANÚNCIOS (Usuário logado)
*/
$router->get('/anuncios', 'AnuncioController@index');
$router->get('/anuncios/{slug}', 'AnuncioController@show');

// Anúncios - CRUD (usuário logado)
$router->get('/anuncios/meus', 'AnuncioController@meus', [3, 2, 1, 4]);
$router->get('/anuncios/criar', 'AnuncioController@criar', [3, 2, 1, 4]);
$router->post('/anuncios/salvar', 'AnuncioController@salvar', [3, 2, 1, 4]);
$router->get('/anuncios/editar/{id}', 'AnuncioController@editar', [3, 2, 1, 4]);
$router->post('/anuncios/atualizar', 'AnuncioController@atualizar', [3, 2, 1, 4]);
$router->post('/anuncios/pausar', 'AnuncioController@pausar', [3, 2, 1, 4]);
$router->post('/anuncios/ativar', 'AnuncioController@ativar', [3, 2, 1, 4]);
$router->get('/anuncios/excluir/{id}', 'AnuncioController@excluir', [3, 2, 1, 4]);

/*
   INTERESSES (Contratar)
*/
$router->post('/interesses/criar', 'InteresseController@criar', [3, 2, 1, 4]);
$router->post('/interesses/aceitar', 'InteresseController@aceitar', [3, 2, 1, 4]);
$router->post('/interesses/recusar', 'InteresseController@recusar', [3, 2, 1, 4]);
$router->get('/interesses/pendentes', 'InteresseController@pendentes', [3, 2, 1, 4]);
$router->get('/interesses/ativos', 'InteresseController@ativos', [3, 2, 1, 4]);
$router->post('/interesses/confirmar-execucao', 'InteresseController@confirmarExecucao', [3, 2, 1, 4]);
$router->get('/interesses/detalhes/{id}', 'InteresseController@detalhes', [3, 2, 1, 4]);
$router->post('/interesses/cancelar', 'InteresseController@cancelar', [3, 2, 1, 4]);
$router->get('/interesses/meus', 'InteresseController@meus', [3, 2, 1, 4]);
$router->get('/interesses/recebidos', 'InteresseController@recebidos', [3, 2, 1, 4]);

/*
   CONFIRMAÇÃO DE PAGAMENTO
*/
$router->get('/pagamentos/confirmar', 'PagamentoController@confirmar', [3, 2, 1, 4]);
$router->post('/pagamentos/confirmar-contratante', 'PagamentoController@confirmarContratante', [3, 2, 1, 4]);
$router->post('/pagamentos/confirmar-freelancer', 'PagamentoController@confirmarFreelancer', [3, 2, 1, 4]);
$router->get('/pagamentos', 'PagamentoController@index', [3, 2, 1, 4]);

/*
   DISPUTAS
*/
$router->get('/disputas/criar', 'DisputaController@criar', [3, 2, 1, 4]);
$router->post('/disputas/salvar', 'DisputaController@salvar', [3, 2, 1, 4]);
$router->get('/disputas/detalhes/{id}', 'DisputaController@detalhes', [3, 2, 1, 4]);

// Moderador - Disputas
$router->get('/moderator/disputas', 'DisputaController@listar', [1, 2, 4]);
$router->post('/moderator/disputas/aprovar', 'DisputaController@aprovar', [1, 2, 4]);
$router->post('/moderator/disputas/rejeitar', 'DisputaController@rejeitar', [1, 2, 4]);
/*
   AVALIAÇÕES
*/
$router->get('/avaliacoes/criar', 'AvaliacaoController@criar', [3, 2, 1, 4]);
$router->post('/avaliacoes/salvar', 'AvaliacaoController@salvar', [3, 2, 1, 4]);
$router->post('/avaliacoes/responder', 'AvaliacaoController@responder', [3, 2, 1, 4]);
/*
   DENÚNCIAS
*/
$router->get('/denuncias/criar', 'DenunciaController@criar', [3, 2, 1, 4]);
$router->post('/denuncias/salvar', 'DenunciaController@salvar', [3, 2, 1, 4]);

/*
   NOTIFICAÇÕES
*/
$router->get('/notificacoes', 'NotificacaoController@index', [3, 2, 1, 4]);
$router->post('/notificacoes/marcar-lida', 'NotificacaoController@marcarLida', [3, 2, 1, 4]);
$router->post('/notificacoes/marcar-todas-lidas', 'NotificacaoController@marcarTodasLidas', [3, 2, 1, 4]);
$router->get('/notificacoes/contador', 'NotificacaoController@contador', [3, 2, 1, 4]);

/*
   ADMIN (Perfil 1 - Admin)
*/
$router->get('/admin', 'AdminController@index', [1, 4]);
$router->get('/admin/dashboard', 'AdminController@dashboard', [1, 4]);

// Admin - Usuários
$router->get('/admin/usuarios', 'AdminUsuarioController@index', [1, 4]);
$router->get('/admin/usuarios/criar', 'AdminUsuarioController@criar', [1, 4]);
$router->post('/admin/usuarios/salvar', 'AdminUsuarioController@salvar', [1, 4]);
$router->get('/admin/usuarios/editar', 'AdminUsuarioController@editar', [1, 4]);
$router->post('/admin/usuarios/atualizar', 'AdminUsuarioController@atualizar', [1, 4]);
$router->get('/admin/usuarios/excluir', 'AdminUsuarioController@excluir', [1, 4]);
$router->post('/admin/usuarios/banir', 'AdminUsuarioController@banir', [1, 4]);
$router->post('/admin/usuarios/desbanir', 'AdminUsuarioController@desbanir', [1, 4]);

// Admin - Anúncios
$router->get('/admin/anuncios', 'AdminAnuncioController@index', [1, 4]);
$router->get('/admin/anuncios/editar', 'AdminAnuncioController@editar', [1, 4]);
$router->post('/admin/anuncios/atualizar', 'AdminAnuncioController@atualizar', [1, 4]);
$router->get('/admin/anuncios/excluir', 'AdminAnuncioController@excluir', [1, 4]);

// Admin - Denúncias
$router->get('/admin/denuncias', 'AdminDenunciaController@index', [1, 4]);
$router->get('/admin/denuncias/detalhes', 'AdminDenunciaController@detalhes', [1, 4]);
$router->post('/admin/denuncias/analisar', 'AdminDenunciaController@analisar', [1, 4]);

// Admin - Disputas
$router->get('/admin/disputas', 'AdminDisputaController@index', [1, 4]);
$router->get('/admin/disputas/detalhes', 'AdminDisputaController@detalhes', [1, 4]);
$router->post('/admin/disputas/resolver', 'AdminDisputaController@resolver', [1, 4]);

// Admin - Categorias
$router->get('/admin/categorias', 'AdminCategoriaController@index', [1, 4]);
$router->post('/admin/categorias/salvar', 'AdminCategoriaController@salvar', [1, 4]);
$router->post('/admin/categorias/atualizar', 'AdminCategoriaController@atualizar', [1, 4]);
$router->get('/admin/categorias/excluir', 'AdminCategoriaController@excluir', [1, 4]);

// Admin - Logs
$router->get('/admin/logs', 'AdminLogController@index', [1, 4]);

/*
   MODERADOR (Perfil 2 - Moderador)
*/
$router->get('/moderator', 'ModeradorController@index', [1, 2, 4]);
$router->get('/moderator/anuncios', 'ModeradorController@anuncios', [1, 2, 4]);
$router->get('/moderator/disputas', 'ModeradorController@disputas', [1, 2, 4]);
$router->get('/moderator/usuarios', 'ModeradorController@usuarios', [1, 2, 4]);
$router->get('/moderator/categorias', 'ModeradorController@categorias', [1, 2, 4]);

/*
   MODERADOR - DENUNCIAS
*/
$router->get('/moderator/denuncias', 'ModeradorController@denuncias', [1, 2, 4]);
$router->get('/moderator/denuncias/visualizar/{id}', 'DenunciaController@visualizar', [1, 2, 4]);
$router->post('/moderator/denuncias/aprovar', 'DenunciaController@aprovar', [1, 2, 4]);
$router->post('/moderator/denuncias/rejeitar', 'DenunciaController@rejeitar', [1, 2, 4]);


// Freelancer
$router->get('/freelancer', 'FreelancerController@dashboard', [2, 3, 4]);

/*
   LOGS
*/
$router->get('/logs', 'LogController@index', [1, 2, 4]);

/*
   TERMOS
*/
$router->get('/termos', 'TermosController@index');

// Cliente
$router->get('/cliente', 'ClienteController@dashboard', [2, 3, 4]);

// Avaliações
$router->get('/avaliacoes/criar/{id}', 'AvaliacaoController@criar', [3, 2, 1, 4]);
$router->post('/avaliacoes/salvar', 'AvaliacaoController@salvar', [3, 2, 1, 4]);
$router->get('/avaliacoes/responder/{id}', 'AvaliacaoController@responder', [3, 2, 1, 4]);
$router->post('/avaliacoes/salvar-resposta', 'AvaliacaoController@salvarResposta', [3, 2, 1, 4]);

// routes/web.php

/*
   FAVORITOS
*/
$router->get('/favoritos', 'FavoritoController@index', [3, 2, 1, 4]);
$router->post('/favoritos/adicionar', 'FavoritoController@adicionar', [3, 2, 1, 4]);
$router->post('/favoritos/remover', 'FavoritoController@remover', [3, 2, 1, 4]);
$router->get('/favoritos/verificar', 'FavoritoController@verificar', [3, 2, 1, 4]);


// CHAT
$router->get('/chat', 'ChatController@index', [3, 2, 1, 4]);
$router->get('/chat/{id}', 'ChatController@conversa', [3, 2, 1, 4]);
$router->post('/chat/enviar', 'ChatController@enviar', [3, 2, 1, 4]);
$router->get('/chat/mensagens', 'ChatController@mensagens', [3, 2, 1, 4]);
$router->post('/chat/marcar-lida', 'ChatController@marcarLida', [3, 2, 1, 4]);
