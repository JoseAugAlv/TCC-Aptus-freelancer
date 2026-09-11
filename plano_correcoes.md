# Plano de Correções — Aptus

## 1. Design
- [ ] Verificar consistência visual (cabeçalhos, rodapés, botões, tipografia, cores)
- [ ] Padronizar responsividade em todas as páginas
- [ ] Corrigir layout quebrado (se encontrado)

## 2. Lógica / PHP
- [ ] Verificar rotas duplicadas/inválidas (ex: `/anuncios/criar` com roles errados)
- [ ] Corrigir falhas de autenticação e sessão
- [ ] Verificar validações de formulários
- [ ] Corrigir erro de redirecionamento (`/Aptus/` vs `/`)
- [ ] Verificar segurança (CSRF, SQL Injection, XSS)

## 3. CSS
- [ ] Verificar arquivos CSS duplicados/inconsistentes
- [ ] Corrigir responsividade quebrada
- [ ] Padronizar cores e fontes

## 4. Requisitos Funcionais (Requisitos.md)
- [ ] RF06 — Lembrar-me (N) — confirmar se precisa implementar
- [ ] RF07 — Limite de Tentativas (N) — confirmar se precisa implementar
- [ ] RNF16 — Acessibilidade (P) — melhorar se necessário
- [ ] RNF19 — Documentação (P) — documentar
- [ ] RNF02, RNF03, RNF27 (N / P) — documentar status
- [ ] Confirmar todos os requisitos atendidos (S) estão funcionando

## 5. Cookies / Privacidade / LGPD
- [ ] Criar banner de consentimento de cookies
- [ ] Criar página de Política de Privacidade e LGPD
- [ ] Criar página de Termos de Uso (se não existir completa)
- [ ] Implementar aceitação obrigatória antes de navegar (se solicitado)
- [ ] Salvar consentimento do usuário (cookie/sessão)

## 6. Arquivos a serem criados/modificados
- `plano_correcoes.md`
- `public/css/cookies.css`
- `public/css/lgpd.css`
- `public/js/cookies.js`
- `public/js/lgpd.js`
- `app/Views/layouts/cookie_banner.php`
- `app/Controllers/CookieController.php` (se necessário)
- Modificações em `public/index.php`, `routes/web.php`, `app/Controllers/*`
