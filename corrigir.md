# Auditoria Técnica — Sistema Aptus
**Marketplace de freelancers · PHP puro (MVC) + MySQL · TCC**


### 1.3 — CSRF: toda a infraestrutura existe, mas **não é usada em lugar nenhum**
**Onde:** `app/Middleware/CsrfMiddleware.php`, `app/Helpers/SecurityHelper.php` (`gerarCsrfToken`/`verificarCsrfToken`)

**Problema:** Você implementou corretamente um sistema de token CSRF (`CsrfMiddleware::validate()`, `CsrfMiddleware::field()`, `SecurityHelper::verificarCsrfToken()`). Fui conferir onde isso é chamado:

```
grep -rl "CsrfMiddleware::validate" app/Controllers/   → 0 resultados (de 23 controllers)
grep -rl "_csrf_token"              app/Views/          → 0 resultados (de 28 formulários POST)
grep -rn "verificarCsrfToken"       app/                → só aparece dentro do próprio SecurityHelper.php
```

Ou seja: **nenhum dos 28 formulários POST do site envia o token**, e **nenhum controller valida ele**. O código do CSRF existe mas está 100% morto.

**Por que é um problema:** Sem CSRF, qualquer site malicioso pode montar um formulário escondido que, se sua vítima estiver logada no Aptus, dispara ações em nome dela sem ela saber — mandar mensagem, criar interesse, editar anúncio, excluir anúncio (`GET /anuncios/excluir/{id}` — pior ainda, é via `GET`, então basta uma `<img src="...">` maliciosa), até ações de admin/moderador.

**Impacto:** Usuário — perda de controle da conta; Negócio — anúncios apagados, disputas fraudulentas, confiança destruída (crítico considerando o público-alvo, pessoas mais velhas, mais vulneráveis a esse tipo de engenharia social); Segurança — classe inteira de vulnerabilidade (OWASP A01).

**Gravidade:** 🔴 Crítica

**Como corrigir (você já tem 90% pronto, só falta ligar):**

Em toda view com `<form method="POST">`, adicionar dentro do form:
```php
<?= CsrfMiddleware::field() ?>
```

No topo de cada método de controller que processa POST (ex: `AnuncioController::salvar()`, `InteresseController::criar()`, todo `ModeradorController`/`AdminController` que grava algo):
```php
public function salvar()
{
    require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
    CsrfMiddleware::validate(); // interrompe a requisição se o token for inválido

    // ... resto do método
}
```
Para não esquecer em nenhum lugar (esse é o tipo de coisa que volta a acontecer se depender de lembrar em 23 arquivos), o ideal é fazer isso **uma vez só**, no `Router::dispatch()`, para todo método `POST`:
```php
// dentro de Router::dispatch(), logo depois de resolver $action/$roles, antes de instanciar o controller
if ($method === 'POST') {
    require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
    CsrfMiddleware::validate();
}
```
Assim, todo POST do sistema fica protegido automaticamente, inclusive rotas futuras, sem depender de cada controller lembrar de chamar.

---

### 1.4 — Bloqueio de força bruta no login é baseado em `$_SESSION` — burlável em segundos
**Onde:** `app/Helpers/LoginAttempt.php`

**Problema:** O contador de tentativas de login fica em `$_SESSION['login_attempt_' . md5($email)]`. Sessão é por cookie do navegador. Um atacante automatizando tentativas de senha só precisa **não enviar cookie** (ou apagar o cookie a cada tentativa) — o servidor cria uma sessão nova, zerada, a cada requisição, e o limite de 5 tentativas nunca é atingido de verdade.

**Por que é um problema:** É a proteção que existe hoje contra *credential stuffing*/força bruta em contas de clientes e freelancers, e ela não funciona contra o cenário que ela foi feita para evitar (um script tentando senhas).

**Impacto:** Segurança — contas podem ser atacadas por força bruta sem limite real; Negócio — especialmente grave com um público mais velho, que tende a usar senhas mais simples/repetidas.

**Gravidade:** 🔴 Crítica

**Como corrigir:** O contador precisa viver em algo que o atacante não controla — o banco de dados (ou um cache tipo Redis, mas para o escopo de um TCC em PHP puro, uma tabela é suficiente), indexado por **e-mail + IP**, não pela sessão:

```php
class LoginAttempt
{
    public static function check(string $email, string $ip): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM login_tentativa
             WHERE (email = ? OR ip = ?) AND criado_em > (NOW() - INTERVAL 15 MINUTE)"
        );
        $stmt->execute([$email, $ip]);
        return (int) $stmt->fetchColumn() < 5;
    }

    public static function increment(string $email, string $ip): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("INSERT INTO login_tentativa (email, ip, criado_em) VALUES (?, ?, NOW())")
            ->execute([$email, $ip]);
    }
}
```
```sql
CREATE TABLE login_tentativa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    criado_em DATETIME NOT NULL,
    INDEX idx_email_data (email, criado_em),
    INDEX idx_ip_data (ip, criado_em)
);
```
E chamar com `LoginAttempt::check($email, $_SERVER['REMOTE_ADDR'])` no `AuthController::login()`.

---

### 1.5 — `AuthMiddleware` referencia um arquivo que não existe (e nunca é usado)
**Onde:** `app/Middleware/AuthMiddleware.php:2`

```php
require_once __DIR__ . '/../Helpers/Auth.php';   // ❌ esse arquivo não existe
```
A classe `Auth` de verdade está em `app/Core/Auth.php`, não em `app/Helpers/`. Além disso:
```
grep -rn "AuthMiddleware::handle\|Auth::role" app/ routes/   → 0 resultados
```
Ou seja: essa middleware **nunca é chamada por nenhuma rota**, e se fosse chamada, quebraria com fatal error (`require` de arquivo inexistente). É código morto e quebrado ao mesmo tempo.

Além disso, mesmo consertando o `require`, `Auth::role()` lê `$_SESSION['usuario']['perfil']`, mas o `AuthController::login()` grava a chave como `$_SESSION['usuario']['role']` (veja `AuthController.php` por volta da linha 108). Então `Auth::role()` sempre retornaria `null`, mesmo corrigindo o caminho do arquivo.

**Por que é um problema:** Sugere que a proteção de rota "de verdade" (que hoje é feita manualmente, verificação por verificação, dentro de cada método de controller — e funciona, conferi vários exemplos) foi uma segunda tentativa depois que essa primeira (`AuthMiddleware`/`Auth::role()`) não funcionou, e ela ficou esquecida no projeto. Isso é um risco de manutenção: se algum dia alguém (inclusive você, daqui 6 meses) tentar usar `AuthMiddleware` achando que ela funciona, terá uma falsa sensação de proteção.

**Gravidade:** 🔴 Crítica (é autenticação/autorização quebrada, mesmo que não usada hoje)

**Como corrigir:** Ou conserta e padroniza (melhor) ou remove para não confundir.
```php
// app/Middleware/AuthMiddleware.php
class AuthMiddleware
{
    public static function handle(array $rolesPermitidos = [])
    {
        if (!Auth::check()) {
            header('Location: /Aptus/login');
            exit;
        }
        if (!empty($rolesPermitidos) && !in_array(Auth::role(), $rolesPermitidos, true)) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }
}
```
```php
// app/Core/Auth.php — corrigir a chave para bater com o que o login realmente grava
public static function role()
{
    return $_SESSION['usuario']['role'] ?? null;
}
```
E aí sim usar essa middleware centralizada dentro do `Router::dispatch()` no lugar da checagem de `roles` que já existe lá (que, essa sim, funciona — ver 1.6).

---

### 1.6 — Roteador sobrescreve rotas duplicadas silenciosamente → uma tela fica inacessível
**Onde:** `app/Core/Router.php:9-15` + `routes/web.php`

**Problema:** `Router::get()`/`post()` guardam a rota num array indexado pela própria URI:
```php
$this->routes['GET'][$uri] = ['action' => $action, 'roles' => $roles];
```
Se a mesma URI + método é registrada duas vezes, a segunda **substitui** a primeira sem aviso nenhum. Em `routes/web.php` isso acontece de verdade:
```php
$router->get('/perfil/portfolio', 'PerfilController@portfolio', [3, 2, 1, 4]);   // linha ~39
...
$router->get('/perfil/portfolio', 'PortfolioController@index',  [3, 2, 1, 4]);   // linha ~44
```
Resultado: `PerfilController::portfolio()` **nunca é executado** — quem acessa `/perfil/portfolio` sempre cai em `PortfolioController::index()`. Se esses dois métodos fazem coisas diferentes (parecem fazer, um é do "perfil" outro é a listagem de portfólio dedicada), você tem uma tela morta que talvez nem saiba que está morta.

Há também duplicações "inofensivas" (mesmo controller nas duas vezes) em `/anuncios`, `/anuncios/{slug}` e `/anuncios/criar`, que não quebram nada mas indicam que o arquivo de rotas cresceu sem organização e é fácil essa mesma falha acontecer de novo (como já aconteceu com `/perfil/portfolio`).

**Impacto:** Funcionalidade planejada e codificada fica inacessível para o usuário final, sem erro nenhum — o tipo de bug que passa despercebido em teste manual porque a URL "funciona", só que com a tela errada.

**Gravidade:** 🔴 Crítica (perda silenciosa de funcionalidade)

**Como corrigir:**
1. Decida qual dos dois controllers é o certo para `/perfil/portfolio` e apague a rota duplicada.
2. Adicione uma trava no próprio `Router` para nunca mais isso passar despercebido:
```php
public function get($uri, $action, $roles = [])
{
    if (isset($this->routes['GET'][$uri])) {
        throw new \RuntimeException("Rota GET duplicada: {$uri}");
    }
    $this->routes['GET'][$uri] = ['action' => $action, 'roles' => $roles];
}
```
Isso faz o app quebrar já no ambiente de desenvolvimento (`php -S` ou XAMPP local) na hora em que você reintroduzir uma duplicata, em vez de descobrir em produção.

---

### 1.7 — Referências a `/RecycleWays/` em vez de `/Aptus/` (copiado de outro projeto)
**Onde:** `app/Controllers/LogController.php` (2x), `app/Middleware/AuthMiddleware.php` (1x), `app/Views/auth/verificar.php` (6x), `app/Views/logs/index.php` (4x) — 13 ocorrências no total.

**Problema:** Esses arquivos foram claramente copiados de um outro projeto seu (RecycleWays) e os caminhos não foram todos trocados. Exemplo concreto e reproduzível, `LogController::index()`:
```php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['role'] != 5) {
    header('Location: /RecycleWays/');   // ❌ path errado, projeto é /Aptus/
    exit;
}
```
Qualquer usuário sem permissão de Master que tentar acessar `/Aptus/logs` é redirecionado para uma URL que não existe no ambiente do Aptus (404), em vez de voltar para a home do próprio sistema.

**Impacto:** Experiência quebrada (usuário cai em página inexistente) em pelo menos as telas de log, verificação de e-mail e o middleware de auth.

**Gravidade:** 🔴 Crítica pela quantidade de ocorrências e por afetar fluxo de verificação de e-mail, que é crítico para o cadastro funcionar de ponta a ponta — vale conferir `auth/verificar.php` com atenção, tem 6 ocorrências sozinho.

**Como corrigir:** Busca e troca global, e depois um teste manual em cada uma das 4 telas:
```bash
grep -rl "RecycleWays" app/ | xargs sed -i 's#/RecycleWays#/Aptus#g'
```

---

## 2. Achados de gravidade ALTA

### 2.1 — Política de senha fraca no cadastro (a validação forte existe, mas não é chamada)
**Onde:** `AuthController::salvar()` vs. `SecurityHelper::validarForcaSenha()`

O cadastro só exige `strlen($senha) < 6` — nada de maiúscula, número ou caractere especial. Só que existe, pronta, uma função `SecurityHelper::validarForcaSenha()` que checa tudo isso e **nunca é chamada em lugar nenhum** (confirmei com grep). Para o público-alvo do TCC (pessoas mais velhas, provavelmente reusando senhas simples de outros sites), isso facilita muito ataques de dicionário/credential stuffing.

**Correção:**
```php
// AuthController::salvar(), antes de criar o usuário
require_once __DIR__ . '/../Helpers/SecurityHelper.php';
$forca = SecurityHelper::validarForcaSenha($senha);
if (!$forca['valida']) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'Senha fraca: ' . implode(', ', $forca['erros'])];
    header('Location: /Aptus/login/cadastrar');
    exit;
}
```

### 2.2 — Enumeração de e-mail em cadastro e "esqueci senha"
**Onde:** `AuthController::salvar()` ("Este e-mail já está cadastrado") e `AuthController::enviarToken()` ("E-mail não encontrado").

Mensagens diferentes para "existe"/"não existe" permitem descobrir quais e-mails têm conta no Aptus, útil para phishing direcionado depois.

**Correção:** No "esqueci senha", sempre responder a mesma mensagem genérica ("Se esse e-mail estiver cadastrado, você vai receber instruções"), independente do resultado. No cadastro é mais difícil evitar completamente (o usuário precisa saber que já existe conta para tentar login), mas pelo menos no reset de senha dá para fechar esse vazamento.

### 2.3 — "Lembrar-me" gera token que nunca é usado (funcionalidade morta) e vaza em HTTP
**Onde:** `AuthController::login()`, linhas do bloco `if ($lembrar) { ... setcookie('remember_token', ...) }`

Você grava um `remember_token` no banco e manda um cookie de 30 dias — mas **nenhum lugar do código lê `$_COOKIE['remember_token']`** para logar o usuário automaticamente (confirmei: zero ocorrências de `$_COOKIE` em todo o projeto). Ou seja, a caixinha "Lembrar-me" não lembra nada; ela só cria um token de longa duração, sem uso, guardado à toa no banco e no navegador do usuário — e pior, o cookie é criado com `secure` fixo em `false`:
```php
setcookie('remember_token', $token, time() + 30*24*3600, '/Aptus', '', false, true);
//                                                                      ^^^^^ secure=false sempre
```
mesmo em produção com HTTPS, esse cookie sairia sem a flag `Secure`.

**Correção:** Ou implementa de fato o auto-login lendo o cookie no bootstrap (`public/index.php`, antes do dispatch), comparando o hash do token salvo no banco (não texto puro — hash igual senha), regenerando o token a cada uso; ou remove a funcionalidade inteira até ter tempo de fazer direito. Não deixar "pela metade" — hoje ela só cria risco (token de longa duração parado) sem entregar benefício nenhum ao usuário.

### 2.4 — Debug ligado em produção no envio de mensagens do chat
**Onde:** `ChatController::enviar()`, início do método:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
...
error_log("POST: " . print_r($_POST, true));
error_log("RAW INPUT: " . file_get_contents('php://input'));
```
Isso liga exibição de erro PHP (que pode vazar caminho de servidor, versão de PHP, stack trace, para quem estiver com o DevTools aberto) **só nessa rota**, e ainda loga o corpo bruto de toda mensagem de chat no log de erro do PHP — se duas pessoas trocarem informação sensível no chat (telefone, endereço, dado de pagamento combinado fora da plataforma), isso fica em texto puro no log do servidor indefinidamente.

**Correção:** Remover essas 4 linhas antes de qualquer deploy. Se for realmente necessário depurar, usar uma flag de config (`Config::get('APP_ENV') === 'development'`) em vez de código fixo, e nunca logar `$_POST`/`php://input` crus de rotas que carregam conteúdo de usuário.

---

## 3. Achados de gravidade MÉDIA

| # | Problema | Onde | Impacto | Correção |
|---|---|---|---|---|
| 3.1 | Mensagem 403 expõe o valor do perfil (role) do usuário logado em texto: `"Seu perfil: " . $role` | `AdminController::dashboard()` (e padrão repetido em outros controllers admin) | Vazamento de informação interna, baixo risco isolado mas ajuda reconhecimento em um ataque em cadeia | Trocar por mensagem genérica "Você não tem permissão para acessar esta página." |
| 3.2 | Dashboard do admin dispara ~10 queries de agregação separadas a cada load, sem cache | `Dashboard.php` (`getTotalUsuarios`, `getTotalAnuncios`, `getAnunciosPendentes`, etc., chamadas em sequência por `AdminController::dashboard()`) | Não trava com poucos dados, mas não escala — cada refresh do admin é 10 round-trips ao MySQL | Combinar em 1-2 queries com `SUM(CASE WHEN ...)` ou cachear os totais por alguns minutos (arquivo/tabela `dashboard_cache`) |
| 3.3 | Só 1 de 18 tags `<img>` em todas as views tem atributo `alt` | Views em geral (ex: fotos de perfil/anúncio em `anuncios/show.php`, `perfil/publico.php`, `chat/conversa.php`) | Falha de acessibilidade (WCAG 1.1.1) — grave dado que o público-alvo declarado é pessoas mais velhas, parcela relevante usa ferramentas de acessibilidade | Adicionar `alt` descritivo em toda `<img>`: `alt="Foto de perfil de <?= htmlspecialchars($nome) ?>"`, e `alt=""` só em imagens puramente decorativas |
| 3.4 | Comparação de token CSRF usa `!==`/`===` normal em vez de `hash_equals()` | `SecurityHelper::verificarCsrfToken()`, `CsrfMiddleware::validate()` | Timing attack teórico (risco prático baixo, mas é boa prática padrão) | `if (!hash_equals($tokenSessao, $tokenRequisicao)) { ... }` |
| 3.5 | Rotas GET duplicadas "inofensivas" (mesmo destino nas duas vezes) para `/anuncios`, `/anuncios/{slug}`, `/anuncios/criar` | `routes/web.php` | Nenhum bug funcional hoje, mas é o mesmo padrão que causou o bug crítico 1.6 — sinal de arquivo de rotas desorganizado | Remover as duplicatas e organizar `routes/web.php` por controller, um bloco só por recurso |

---

## 4. Achados de gravidade BAIXA

- **`SessionConfig::configure()`** usa `@session_start()` (arroba suprime erros) — mascara falhas reais de sessão (ex: headers já enviados) sem log nenhum. Trocar por `session_start()` sem `@` e tratar o erro explicitamente, ou pelo menos logar se falhar.
- **`mkdir($sessionPath, 0777, true)`** em `SessionConfig.php` cria a pasta de sessões com permissão `0777` (leitura/escrita para todo mundo no servidor). Em produção Linux isso é desnecessariamente permissivo; `0750` já resolve.
- Foi encontrado um **backup de banco versionado dentro do próprio projeto**, em `app/Views/backups/backup_2026-07-06_03-43-35.sql`. Mesmo não sendo uma "view" de verdade, é estranho estruturalmente estar dentro de `Views/`, e reforça o risco da Seção 1.1: se esse caminho algum dia acabar acessível via web, é o banco inteiro exposto com nome de arquivo previsível. Mover para fora da árvore do projeto (ou pelo menos para fora de qualquer pasta que algum dia possa ficar pública) e não versionar backups no Git.

---

## 5. O que está bem feito (vale reconhecer)

Pontos positivos reais que encontrei e que mostram cuidado:
- **SQL Injection:** não encontrei nenhuma concatenação de entrada de usuário em query SQL. Todo o acesso a banco usa PDO com **prepared statements** (`?` + `execute([...])`), inclusive em filtros dinâmicos (`ORDER BY`/`LIMIT` são concatenados como *texto fixo* escolhido por `switch`/`if`, nunca a partir de input direto do usuário) — isso é o jeito certo de fazer.
- **XSS:** a grande maioria das views escapa corretamente saída de dados de usuário com `htmlspecialchars()` (inclusive em `chat/conversa.php`, `anuncios/show.php`, `perfil/publico.php`, exatamente onde mais importa — texto livre digitado por outro usuário).
- **Senha:** hash com `password_hash(..., PASSWORD_DEFAULT)` e verificação com `password_verify()` — correto, nada de MD5/SHA1.
- **Upload de arquivo:** valida extensão por whitelist, tamanho máximo, **e** tipo MIME real via `finfo`/`getimagesize` (não confia só na extensão do nome do arquivo), gera nome aleatório com `random_bytes()` e redimensiona a imagem — é um upload bem feito para um projeto sem framework.
- Cookie de sessão configurado com `httponly`, `samesite=Lax`, e `secure` condicionado a HTTPS em produção.

---

## 6. Visual / UI / UX — o que pude e não pude avaliar

Como explicado na Seção 0, **os arquivos `.css` não vieram no material enviado** (só aparecem na árvore de pastas: `public/css/login.css`, `admin.css`, `chat.css`, etc.), então não posso avaliar cor, contraste, tipografia, espaçamento, alinhamento ou estados de hover/focus reais — seria inventar dados, e você pediu explicitamente para eu não fazer isso.

O que dá para observar só pelo HTML das views:
- Existe `<meta name="viewport" content="width=device-width, initial-scale=1.0">` no `layouts/header.php` — pré-requisito básico para responsividade está presente.
- Estrutura de formulários com `<label>` está consistente em quantidade com os `<input>` (112 labels para 107 inputs), o que é um bom sinal, mas eu não conferi se todo `for=` bate com o `id=` correspondente (precisaria rodar no navegador ou fazer essa checagem par a par, que não fiz por tempo — se quiser, posso fazer numa próxima rodada, view por view).
- Falta de `alt` em imagens (já listado em 3.3).

**Para eu conseguir avaliar de verdade a Seção 1 do seu pedido original** (hierarquia visual, contraste, espaçamento, responsividade real, estados de hover/foco/loading), o que eu precisaria:
1. Os arquivos `.css` (`public/css/*.css`), **e/ou**
2. Prints de tela / gravação de tela nas resoluções desktop, tablet e mobile, **e/ou**
3. O site rodando (posso analisar via navegador se você subir localmente e eu tiver como acessar, ou me mandar a URL se estiver publicado).

Se quiser, me envie os `.css` num próximo upload e eu faço a Seção 1 completa (visual/UI) com o mesmo nível de detalhe desta auditoria.

---

## 7. Prioridade recomendada de correção

1. **Hoje:** rotacionar a senha de app do Gmail exposta (1.2) e confirmar/corrigir o `.htaccess`/DocumentRoot (1.1).
2. **Esta semana, antes de qualquer entrega/demonstração:** ligar CSRF (1.3) — é o maior buraco de segurança real do sistema e o mais barato de fechar, já que a infra existe; corrigir rate limit de login (1.4); corrigir rotas duplicadas (1.6) e os `/RecycleWays/` (1.7).
3. **Antes da defesa do TCC:** política de senha (2.1), remember-me morto/inseguro (2.3), debug ligado no chat (2.4), enumeração de e-mail (2.2).
4. **Se sobrar tempo:** itens de gravidade média/baixa (Seções 3 e 4) e me mandar os `.css` para eu completar a análise visual.