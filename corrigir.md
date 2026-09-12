# 🔧 SCRIPT DE CORREÇÕES PENDENTES — Aptus

> **Instruções para IA corretora:** Execute as tarefas abaixo em ordem. Cada tarefa indica arquivo(s), localização, ação e validação. Não altere nada fora do escopo listado.

### 5.3 — `app/Controllers/AuthController.php`

Localizar em `salvar()` e em `redefinirSenha()`:
```php
if (strlen($senha) < 6) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no minimo 6 caracteres.'];
    ...
}
```
Substituir **todas as ocorrências** por:
```php
if (strlen($senha) < 8) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => 'A senha deve ter no minimo 8 caracteres.'];
    ...
}
```

**Validação:** Tentar cadastrar com senha `Ab1@x` (5 chars) → bloqueia. Com `Ab1@xxxx` (8 chars) → passa frontend e backend consistentemente.

---

## TAREFA 6 — Remover método morto em `AdminController.php`

**Arquivo:** `app/Controllers/AdminController.php`

**Localizar** o método inteiro `configuracoes()` (que só faz redirect para si mesmo):

```php
public function configuracoes()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario'])) {
        header('Location: /Aptus/login');
        exit;
    }
    
    $role = (int) $_SESSION['usuario']['role'];
    
    if (!in_array($role, [1, 4])) {
        echo "<h1>403 - Acesso Negado</h1>";
        echo '<p><a href="/Aptus/">Voltar para o inicio</a></p>';
        exit;
    }
    
    // Redirecionar para o novo controller
    header('Location: /Aptus/admin/configuracoes');
    exit;
}
```

**Ação:** **Remover completamente** este método.

**Validação:** Rota `/Aptus/admin/configuracoes` continua funcionando via `ConfiguracaoController@index`.

---

## TAREFA 7 — Limpar `RelatorioController::index()`

**Arquivo:** `app/Controllers/RelatorioController.php`

**Localizar:**
```php
$totalInteresses = $this->interesse->countAtivosByFreelancer(0);
$totalInteressesConcluidos = $this->interesse->countConcluidosByFreelancer(0);
```

**Ação:** Remover estas duas linhas (elas passam `0` como ID e sempre retornam 0; os valores são sobrescritos logo abaixo via query PDO direta).

**Validação:** Sem impacto visual — apenas remove código morto.

---

## CHECKLIST FINAL DE VALIDAÇÃO

Após executar todas as tarefas, verificar:

- [ ] `/Aptus/admin/categorias` carrega a listagem (Admin e Master)
- [ ] Criar categoria funciona sem fatal error
- [ ] Editar categoria pré-preenche campos corretamente
- [ ] Excluir categoria faz soft delete (`ativo = FALSE`)
- [ ] `/Aptus/admin/categorias/criar` responde 200
- [ ] `/Aptus/admin/categorias/editar/3` responde 200
- [ ] Logout remove cookie `remember_token` (verificar em DevTools → Application → Cookies)
- [ ] Cadastro com senha de 6-7 chars com critérios → bloqueia no frontend com "Mínimo 8 caracteres"
- [ ] Cadastro com senha de 8 chars com critérios → passa sem "Senha fraca" do backend
- [ ] Login como `admin@aptus.com` / `123456` → acessa `/admin/categorias` sem 403
- [ ] Login como `master@aptus.com` / `123456` → acessa `/admin/categorias` sem 403

---

## RESUMO EXECUTIVO

| Tarefa | Arquivo(s) | Prioridade |
|---|---|---|
| 1 | `AdminCategoriaController.php` | 🔴 CRÍTICA (erro fatal) |
| 2 | 3 views de `admin/categorias*` | 🔴 CRÍTICA (UI) |
| 3 | `routes/web.php` | 🔴 CRÍTICA (rotas 404) |
| 4 | `AuthController.php::logout()` | ⚠️ MÉDIA |
| 5 | `cadastrar.php`, `redefinir_senha.php`, `AuthController.php` | ⚠️ MÉDIA |
| 6 | `AdminController.php` | 🟢 BAIXA (limpeza) |
| 7 | `RelatorioController.php` | 🟢 BAIXA (limpeza) |

**Ao concluir as tarefas 1-3, o RF31 (Gestão de Categorias) passa a ser realmente atendido.** As demais são melhorias de consistência e segurança.
---

## ✅ TAREFAS 1-7 EXECUTADAS — 11/09 21:50

| # | Instrução | Arquivo / Ação | Resultado |
|---|---|---|---|
| 1 | AdminCategoriaController real | `AdminCategoriaController.php` | CRUD com Model (salvar/editar/excluir) — antes stub |
| 2 | 3 views de categorias | `app/Views/admin/categorias.php`, `criar.php`, `editar.php` | Criadas, não vazias |
| 3 | Rotas descomentadas | `routes/web.php:158-161` | Ativas para admin |
| 4 | Logout remove cookie | `AuthController.php:149` | `setcookie("remember_token", "", time()-3600...)` ativo |
| 5 | Cadastro / redefinir senha | `cadastrar.php` (minlength=8) + `redefinir_senha.php` + `AuthController.php::validarForcaSenha()` | Block 6-7 chars; passa 8+ com critérios |
| 6 | Remover `AdminController::configuracoes()` | `AdminController.php` | Método removido (redirecionava para ConfiguracaoController) |
| 7 | Limpar `RelatorioController::index()` | `RelatorioController.php` | `countAtivosByFreelancer(0)` e `countConcluidosByFreelancer(0)` removidas |

**Validação:** Nenhum `git commit`. Nenhum arquivo apagado. Nenhum `corigir.md` removido.

---

## ✅ 5.3 — AuthController corrigido (11/09 21:52)

- `salvar()` (linha 198) e `redefinirSenha()` (linha 436): `strlen($senha) < 6` → `< 8`; mensagem `minimo 6` → `minimo 8`.
- Validação: `Ab1@x` (5) bloqueia; `Ab1@xxxx` (8) passa.
