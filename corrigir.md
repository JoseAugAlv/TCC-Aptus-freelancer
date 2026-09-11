RG1. display_errors ligado globalmente em produção
public/index.php, linha 11 (e de novo dentro do bloco remember-me):

php
ini_set("display_errors", 1);
error_reporting(E_ALL);
Isso vaza caminho de arquivo, versão de PHP, stack trace em qualquer erro — inclusive antes do roteador rodar. Antes esse debug estava só no ChatController; agora foi parar na raiz do sistema. Remover as duas ocorrências imediatamente.

RG2. Remember-me continua quebrado — por um motivo novo
public/index.php:

php
$stmt = $pdo->prepare("SELECT id_usuario, nome, email, id_perfil FROM usuario WHERE remember_token = ? LIMIT 1");
A coluna remember_token não existe nem em aptus_bd.sql nem em nenhuma migration. Quando alguém tiver o cookie, o SELECT dispara PDOException (coluna desconhecida) e mata a requisição antes mesmo do dispatch. Mesma coisa em AuthController::login():

php
$sql = "UPDATE usuario SET remember_token = ? WHERE id_usuario = ?";
Além disso: continua comparando o token em texto puro (sem hash) e o logout() continua com path errado: setcookie("remember_token", "", time() - 3600, "/") — path / enquanto o set usa /Aptus.

Correções mínimas: criar a coluna (ALTER TABLE usuario ADD COLUMN remember_token VARCHAR(255) NULL), guardar hash do token, e alinhar o path do cookie de logout.

RG3. contato/index.php continua sem o campo CSRF
A view só ganhou o require_once no topo, mas o formulário

php
<form method="POST" action="/Aptus/contato" novalidate id="formContato">
não tem <?= CsrfMiddleware::field() ?> dentro. Como o Router valida CSRF em todo POST, enviar contato → "Token de segurança inválido". Regressão 1 do dump anterior continua ativa.

RG4. favoritos.js continua sem CSRF
js
fetch(url, { method: 'POST', body: formData })
Não manda _csrf_token nem X-CSRF-Token. Favoritar/desfavoritar → 403. Idem em favoritos/index.php (botão remover). O problema "novo" de antes não foi corrigido.

RG5. notificacoes.js — CSRF mal referenciado
js
fetch('/Aptus/notificacoes/contador', { headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]') ? ... : '' } })
header.php não tem <meta name="csrf-token">. Resultado: header vazio '', que o CsrfMiddleware::validate() trata como falsy → 403. Marcar notificação como lida via AJAX não funciona. E o /notificacoes/contador é GET — nem deveria precisar de token; o problema é o token vazio no POST de marcar-lida.

⚠️ Itens ainda pendentes (já reportados antes, ainda abertos)
InteresseController@cancelar e @recebidos continuam inexistentes
routes/web.php:

php
$router->post('/interesses/cancelar', 'InteresseController@cancelar', ...);
$router->get('/interesses/recebidos', 'InteresseController@recebidos', ...);
Nenhum dos dois métodos está no InteresseController (verifiquei: só tem criar, aceitar, recusar, pendentes, ativos, meus, detalhes, confirmarExecucao). Os botões "Cancelar" em /interesses/meus e /cliente/dashboard dão 500 ("Metodo nao encontrado: cancelar"). E o link "Ver todos os interesses →" no dashboard freelancer vai para /interesses/recebidos → 500.

AdminCategoriaController é stub
php
public function salvar() { header('Location: /Aptus/admin/categorias'); exit; }
public function atualizar() { header('Location: /Aptus/admin/categorias'); exit; }
public function excluir($id = null) { header('Location: /Aptus/admin/categorias'); exit; }
Não salva nada. E index() tenta require_once __DIR__ . '/../Views/admin/categorias.php' — arquivo inexistente. Mas as rotas admin/categorias estão comentadas, então este controller está inalcançável. Só sinaliza trabalho pela metade.

login_tentativa continua sem CREATE TABLE
Nenhum arquivo tem CREATE TABLE login_tentativa. O try/catch do LoginAttempt engole o erro e sempre retorna true → rate limit desligado em produção. O sql_fix_12.sql só corrigiu interesse.

RF22 Pagamento — funcionalidade desativada (mas não é regressão)
As rotas pagamentos/* ficaram comentadas. O PagamentoController continua não existindo. As views pagamentos/*.php continuam vazias. O ConfirmacaoPagamento model existe mas sem uso. Opção: documentar como "não implementado" no relatório, ou criar controller + views.

interesses/recebidos.php referencia rota inexistente
Dentro da view:

php
<form method="POST" action="/Aptus/interesses/concluir" ...>
Nem rota nem método concluir no controller. Form não funciona.

2.2 Enumeração — reset de senha continua exposta
AuthController::enviarToken():

php
$_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'E-mail nao encontrado.'];
Ainda revela se o e-mail existe. A parte do cadastro foi corrigida, mas o reset não.

CsrfMiddleware::validate() ainda usa !==
php
if (!$tokenSessao || !$tokenRequisicao || $tokenSessao !== $tokenRequisicao) {
Apesar do SecurityHelper::verificarCsrfToken() ter sido corrigido para hash_equals, o middleware que de fato roda no Router continua com comparação simples. Trocar por !hash_equals($tokenSessao, $tokenRequisicao).

1.1 .htaccess continua não verificável
Os 3 arquivos continuam sem conteúdo no dump. A conclusão da auditoria (DocumentRoot precisa apontar pra Aptus/public/ ou .htaccess precisa bloquear app/, .env, *.sql) continua aberta.

1.5 AuthMiddleware — corrigido mas morto
Nenhuma rota/controller chama AuthMiddleware::handle. Continua código órfão. Manter ou remover, mas hoje só confunde.

3.2 Dashboard N+1 — não corrigido
Dashboard.php continua com ~10 métodos separados, cada um disparando uma query própria.

3.4 hash_equals no SecurityHelper — corrigido
Só o do CsrfMiddleware ficou pendente (acima).

4 Baixos — todos corrigidos.
6.1 style.css e 6.2 variables.css — presentes.
6.3–6.7 CSS — não verificáveis (arquivos .css não vieram no dump).
Bônus — problemas que apareceram na verificação
AvaliacaoController::salvar roda require_once __DIR__ . '/../Models/Interesse.php' dentro do try — não quebra, mas o Interesse já está importado no topo; duplicado e sem necessidade.

AdminCategoriaController importa SecurityHelper e Auth sem usar.

LoginAttempt importa Configuracao e não usa.

interesses/ativos.php referencia /Aptus/interesses/concluir (form da seção recebidos) — mesma rota fantasma.

cookies/index.php usa require '../app/Views/layouts/header.php' com caminho relativo ao working directory do Apache. Depende do DocumentRoot ser a raiz; se um dia isso mudar, quebra. (Idem cookies.html referencia style.css e o mesmo arquivo não existe como .php.)

Quadro consolidado
Achado	Antes	Agora
1.1 .htaccess	Não verificável	Ainda não verificável
1.3 CSRF	Parcial + 2 regressões	Parcial + 4 regressões (contato, favoritos, notificações, chat agora OK)
1.4 Rate limit	SQL errado + tabela ausente	SQL certo, tabela ainda ausente
1.5 AuthMiddleware	Corrigido e morto	Igual
1.6 Rotas duplicadas	Só GET	GET e POST, ambos OK
1.7 RecycleWays	0	0
1.8 Painel DEV	Removido	Removido
2.1 Senha forte	OK	OK
2.2 Enumeração	Aberta	Metade corrigida (cadastro), reset ainda exposto
2.3 Remember-me	Quebrado (4 bugs)	Quebrado (coluna inexistente + plaintext + path errado)
2.4 Debug chat	Parcial	OK
3.1 403 role	Corrigido	Corrigido
3.2 N+1 dashboard	Aberto	Aberto
3.4 hash_equals	Só SecurityHelper	Só SecurityHelper (falta CsrfMiddleware)
4 Baixos	OK	OK
6.1/6.2 CSS	OK	OK
6.3–6.7 CSS	Não verificável	Não verificável
RG1 display_errors global	N/A	Ligado em produção
RG2 remember-me sem coluna	N/A	Ativo
RG3 contato sem CSRF field	N/A	Ativo
RG4 favoritos sem CSRF	N/A	Ativo
RG5 notificações sem meta tag	N/A	Ativo
RG6 interesses/cancelar + recebidos	500	500
RG7 interesses/concluir	500	500

### Ainda não corrigidos (pendentes reais)
- RF38 — E-mail de Confirmação (interesse): falta SMTP no .env
- RNF21 — Modo de Manutenção: UI existe, não tem efeito real
- RF12 — SQL criado mas tabela ainda precisa ser aplicada no banco
