# Relatório Consolidado de Requisitos

Este documento consolida os requisitos funcionais e não funcionais do sistema, incluindo o status de atendimento e as observações relevantes organizadas por módulos.

# Parte I — Requisitos Funcionais

# Relatório de Requisitos

Este documento apresenta a lista de requisitos funcionais, seu status de atendimento e observações relevantes, organizados por módulos.

## Módulo 1: Gestão de Acessos (Autenticação)

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF01 — Cadastro de Usuário Único:** O sistema deve permitir que qualquer pessoa se cadastre informando nome, e-mail e senha. | S | O sistema possui cadastro com validação de e-mail, critérios de senha segura (6 caracteres, maiúscula, minúscula, número e caractere especial) e envio de verificação por e-mail. |
| **RF02 — Login Unificado:** O sistema deve permitir o acesso via e-mail e senha, mantendo a sessão ativa durante a navegação. | S | O sistema possui login unificado com sessão segura, proteção CSRF e feedback com SweetAlert2. |
| **RF03 — Verificação de E-mail:** O sistema deve enviar um e-mail de confirmação após o cadastro para ativar a conta. | S | O sistema envia e-mail de verificação com link válido por 24 horas e permite reenvio. |
| **RF04 — Recuperação de Senha:** O sistema deve oferecer uma opção para o usuário redefinir sua senha caso a esqueça. | S | O sistema envia token de redefinição por e-mail com validade de 1 hora. |
| **RF05 — Perfil do Usuário:** O sistema deve permitir que o usuário edite suas informações pessoais, foto de perfil e contatos. | S | O sistema permite edição completa do perfil com upload de foto e preview. |
| **RF06 — Lembrar-me:** O sistema deve oferecer a opção “Lembrar-me” para manter o usuário logado por mais tempo. | N | Funcionalidade não implementada. Pode ser adicionada futuramente. |
| **RF07 — Limite de Tentativas:** O sistema deve limitar o número de tentativas de login para prevenir ataques de força bruta. | N | Funcionalidade não implementada. Pode ser adicionada futuramente. |

## Módulo 2: Funcionalidades de Freelancer (Prestador)

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF08 — Anúncio de Serviço:** O sistema deve permitir que o freelancer cadastre serviços com título, descrição, preço, categoria e foto. | S | O sistema possui formulário completo com upload de foto de capa e preview, além de geração automática de slug. |
| **RF09 — Gerenciamento de Anúncios:** O freelancer deve poder editar, pausar, ativar ou excluir seus serviços cadastrados. | S | O sistema permite editar, pausar, ativar e excluir anúncios com confirmação. |
| **RF10 — Portfólio de Trabalho:** O sistema deve permitir o upload de fotos de trabalhos anteriores para exibição no perfil. | S | O sistema possui portfólio com upload de imagens, validação e preview. |
| **RF11 — Resposta a Interessados:** O sistema deve fornecer um chat para o freelancer responder a quem demonstrou interesse. | S | O sistema possui chat em tempo real com polling e notificações. |
| **RF12 — Visualização de Propostas:** O freelancer deve poder visualizar, aceitar ou recusar propostas de serviço. | S | O sistema possui página de propostas pendentes com botões de aceitar e recusar. |
| **RF13 — Dashboard do Freelancer:** O sistema deve fornecer um dashboard com métricas de serviços e interesses. | S | O sistema possui dashboard com KPIs, gráficos e listagens de serviços e interesses. |

## Módulo 3: Funcionalidades de Contratante (Cliente)

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF14 — Busca de Serviços:** O sistema deve permitir a pesquisa de serviços por palavras-chave. | S | O sistema possui busca na página inicial e busca avançada com filtros. |
| **RF15 — Filtro por Categorias:** O sistema deve permitir filtrar os resultados da busca por categorias. | S | O sistema possui filtro por categorias na busca avançada. |
| **RF16 — Visualização de Detalhes:** O sistema deve exibir página completa do serviço com descrição, preço, fotos e reputação. | S | O sistema possui página de detalhes com todas as informações do anúncio. |
| **RF17 — Sistema de Favoritos:** O cliente deve poder salvar serviços de interesse para consultar depois. | S | O sistema permite adicionar e remover favoritos via AJAX, com contador. |
| **RF18 — Demonstração de Interesse:** O sistema deve ter um botão para o cliente iniciar o contato com o freelancer. | S | O sistema possui o botão “Tenho Interesse”, que envia uma proposta para o freelancer. |
| **RF19 — Dashboard do Cliente:** O sistema deve fornecer um dashboard com métricas de interesses e favoritos. | S | O sistema possui dashboard com KPIs, interesses e favoritos. |

## Módulo 4: Interação e Confirmação

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF20 — Chat em Tempo Real:** O sistema deve fornecer um chat entre cliente e freelancer para comunicação. | S | O sistema possui chat com polling, envio via AJAX, mensagens em tempo real e notificações. |
| **RF21 — Confirmação de Execução:** O sistema deve permitir que ambas as partes confirmem a execução do serviço. | S | O sistema possui confirmação de execução em via de mão dupla. |
| **RF22 — Confirmação de Pagamento:** O sistema deve permitir que contratante e freelancer confirmem o pagamento. | S | O sistema possui confirmação de pagamento com conciliação de valores. |
| **RF23 — Sistema de Avaliação:** O cliente deve poder avaliar o freelancer com nota de 1 a 5 estrelas e comentário. | S | O sistema permite avaliar com estrelas e comentário após o serviço. |
| **RF24 — Avaliação Mútua:** O freelancer também deve poder avaliar o cliente antes da confirmação final. | S | O sistema exige que ambos avaliem antes de confirmar a execução. |
| **RF25 — Média de Avaliações:** O sistema deve calcular e exibir automaticamente a média de estrelas de cada freelancer. | S | O sistema recalcula automaticamente a nota média a cada nova avaliação. |
| **RF26 — Resposta à Avaliação:** O freelancer deve poder responder publicamente à avaliação recebida. | S | O sistema permite resposta pública do freelancer à avaliação. |

## Módulo 5: Segurança e Moderação

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF27 — Denúncia de Conteúdo:** O sistema deve permitir que qualquer usuário denuncie anúncios ou perfis suspeitos. | S | O sistema permite denunciar anúncios e perfis com motivos predefinidos. |
| **RF28 — Análise de Denúncias:** O moderador deve poder visualizar, aprovar ou rejeitar denúncias. | S | O sistema possui painel para o moderador analisar denúncias. |
| **RF29 — Sistema de Disputas:** O sistema deve permitir a abertura de disputas em caso de divergência no pagamento. | S | O sistema permite abrir disputas quando há divergência de pagamento, com análise do moderador. |
| **RF30 — Painel de Moderação:** O moderador deve poder listar, suspender ou banir usuários e gerenciar conteúdo. | S | O sistema possui painel completo de moderação. |
| **RF31 — Gestão de Categorias:** O administrador deve poder criar, editar ou remover as categorias de serviços. | S | O sistema permite CRUD completo de categorias. |
| **RF32 — Relatórios e Métricas:** O administrador deve ter acesso a relatórios com gráficos e métricas do sistema. | S | O sistema possui relatórios com gráficos, KPIs e exportação para PDF. |
| **RF33 — Configurações do Sistema:** O administrador deve poder configurar parâmetros do sistema. | S | O sistema possui página de configurações para upload, moderação, segurança, e-mail e manutenção. |

## Módulo 6: Notificações e Comunicação

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF34 — Notificações em Tempo Real:** O sistema deve notificar o usuário sobre eventos importantes. | S | O sistema possui notificações para todos os eventos, incluindo interesses, propostas, avaliações e disputas. |
| **RF35 — Badge de Notificações:** O sistema deve exibir um contador de notificações não lidas no ícone do sino. | S | O sistema possui badge com contador atualizado via AJAX. |
| **RF36 — E-mail de Verificação:** O sistema deve enviar e-mail de verificação de conta. | S | O sistema envia e-mail com link de verificação válido por 24 horas. |
| **RF37 — E-mail de Redefinição:** O sistema deve enviar e-mail com token para redefinição de senha. | S | O sistema envia e-mail com token válido por 1 hora. |
| **RF38 — E-mail de Confirmação:** O sistema deve enviar e-mail de confirmação quando um cliente demonstrar interesse. | S | O sistema envia e-mail para o freelancer quando recebe um novo interesse. |

## Módulo 7: Experiência do Usuário (UX)

| Requisito funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RF39 — Upload de Imagens:** O sistema deve permitir upload de imagens com preview, validação e redimensionamento. | S | O sistema possui `UploadHelper` com preview, validação de tipo e tamanho e redimensionamento. |
| **RF40 — Feedback Visual:** O sistema deve fornecer feedback visual para ações do usuário. | S | O sistema utiliza SweetAlert2, estados de carregamento e animações. |
| **RF41 — Responsividade:** O sistema deve ser totalmente responsivo para mobile, tablet e desktop. | S | O sistema possui CSS responsivo para todos os tamanhos de tela. |
| **RF42 — Página 404 Personalizada:** O sistema deve exibir uma página de erro 404 personalizada. | S | O sistema possui página 404 com design atrativo e botões de navegação. |
| **RF43 — Termos de Uso:** O sistema deve disponibilizar página com Termos de Uso e Política de Privacidade. | S | O sistema possui página completa de Termos de Uso e Política de Privacidade. |
| **RF44 — Busca Avançada:** O sistema deve oferecer busca avançada com filtros por categoria, avaliação e preço, além de ordenação. | S | O sistema possui busca avançada com todos os filtros e ordenação. |

### Legenda

- **S:** Atendido.
- **N:** Não atendido.


---

# Parte II — Requisitos Não Funcionais

# Relatório de Requisitos Não Funcionais

Este documento apresenta a lista de requisitos não funcionais, seu status de atendimento e observações relevantes, organizados por módulos.

## Módulo 1: Desempenho e Escalabilidade

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF01 — Tempo de Resposta:** O sistema deve carregar as páginas em até 3 segundos em conexão de banda larga. | S | O sistema utiliza cache de consultas, compressão GZIP e otimização de assets para garantir carregamento rápido. |
| **RNF02 — Capacidade de Usuários:** O sistema deve suportar até 1.000 usuários simultâneos. | N | Não testado em produção. É necessário realizar testes de carga. |
| **RNF03 — Disponibilidade:** O sistema deve estar disponível 99% do tempo. | N | Sistema em ambiente de desenvolvimento local. Para produção, é necessário utilizar hospedagem com SLA garantido. |
| **RNF04 — Cache de Dados:** O sistema deve utilizar cache para reduzir consultas repetidas ao banco de dados. | S | O sistema possui cache de queries no Database e cache de rotas no Router. |
| **RNF05 — Otimização de Assets:** O sistema deve utilizar compressão e minificação de CSS e JavaScript. | S | O sistema utiliza compressão GZIP via `.htaccess` e CSS combinado em um único arquivo. |

## Módulo 2: Segurança

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF06 — Criptografia de Senhas:** As senhas dos usuários devem ser armazenadas de forma segura. | S | O sistema utiliza `password_hash()` com algoritmo Bcrypt para armazenamento de senhas. |
| **RNF07 — Proteção CSRF:** O sistema deve proteger formulários contra ataques de Cross-Site Request Forgery. | S | O sistema possui `CsrfMiddleware`, que gera e valida tokens CSRF em formulários. |
| **RNF08 — Proteção XSS:** O sistema deve prevenir ataques de Cross-Site Scripting. | S | O sistema utiliza `htmlspecialchars()` em todas as saídas de dados para prevenir XSS. |
| **RNF09 — Proteção contra SQL Injection:** O sistema deve prevenir ataques de injeção SQL. | S | O sistema utiliza PDO com prepared statements para todas as consultas ao banco de dados. |
| **RNF10 — Sessão Segura:** As sessões devem ser gerenciadas de forma segura. | S | O sistema utiliza cookies com `HttpOnly`, `Secure` e `SameSite`, além de regenerar o ID da sessão periodicamente. |
| **RNF11 — Log de Auditoria:** O sistema deve registrar ações importantes dos usuários. | S | O sistema possui log de auditoria para login, logout, uploads e ações administrativas. |
| **RNF12 — Validação de Upload:** O sistema deve validar arquivos enviados pelos usuários. | S | O sistema valida tipo, tamanho e MIME real dos arquivos, com redimensionamento de imagens. |

## Módulo 3: Usabilidade e Experiência

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF13 — Interface Intuitiva:** O sistema deve ter interface clara e de fácil navegação. | S | O sistema possui layout moderno, menus organizados e feedback visual para ações. |
| **RNF14 — Feedback ao Usuário:** O sistema deve fornecer feedback imediato para ações do usuário. | S | O sistema utiliza SweetAlert2, mensagens flash, estados de carregamento e animações. |
| **RNF15 — Responsividade:** O sistema deve ser acessível em dispositivos mobile, tablet e desktop. | S | O sistema possui CSS responsivo para todos os tamanhos de tela. |
| **RNF16 — Acessibilidade:** O sistema deve atender a diretrizes básicas de acessibilidade. | P | O sistema possui navegação por teclado, labels em formulários e contraste adequado. Melhorias adicionais podem ser feitas. |
| **RNF17 — Design Consistente:** O sistema deve manter um padrão visual consistente em todas as páginas. | S | O sistema utiliza cores, fontes e componentes consistentes em toda a aplicação. |

## Módulo 4: Manutenibilidade

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF18 — Código Organizado:** O código deve seguir padrões MVC e boas práticas de programação. | S | O sistema utiliza arquitetura MVC com separação clara de responsabilidades. |
| **RNF19 — Documentação:** O sistema deve possuir documentação básica do código. | P | O sistema possui comentários nos métodos principais. A documentação completa está pendente. |
| **RNF20 — Logs de Erro:** O sistema deve registrar erros para facilitar a depuração. | S | O sistema utiliza `error_log()` para registrar erros e auditoria. |
| **RNF21 — Modo de Manutenção:** O sistema deve permitir ativar o modo de manutenção. | S | O sistema possui modo de manutenção configurável nas configurações do sistema. |
| **RNF22 — Backup de Dados:** O sistema deve permitir backup do banco de dados. | N | Funcionalidade não implementada. Recomenda-se backup manual ou agendado. |

## Módulo 5: Compatibilidade

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF23 — Navegadores:** O sistema deve funcionar nos principais navegadores (Chrome, Firefox, Edge e Safari). | S | O sistema utiliza HTML5, CSS3 e JavaScript Vanilla, sendo compatível com navegadores modernos. |
| **RNF24 — Versão do PHP:** O sistema deve ser compatível com PHP 7.4 ou superior. | S | O sistema utiliza PHP 8.x com recursos modernos da linguagem. |
| **RNF25 — Banco de Dados:** O sistema deve ser compatível com MySQL 5.7 ou superior. | S | O sistema utiliza MySQL com PDO, compatível com versões 5.7 e superiores. |
| **RNF26 — Mobile First:** O sistema deve priorizar a experiência em dispositivos móveis. | S | O sistema possui layout responsivo com abordagem mobile-first. |

## Módulo 6: Portabilidade

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF27 — Instalação Fácil:** O sistema deve ser fácil de instalar e configurar. | P | O sistema requer configuração manual do `.env` e importação do banco de dados. O script de instalação está pendente. |
| **RNF28 — Configurações via Arquivo:** As configurações devem ser centralizadas em um único local. | S | O sistema utiliza o arquivo `.env` para configurações de ambiente. |
| **RNF29 — Migrações de Banco:** O sistema deve possuir scripts para criação do banco de dados. | S | O sistema possui `aptus_bd.sql` e `seed_dados_simulados.sql` para criação e população do banco. |

## Módulo 7: Confiabilidade

| Requisito não funcional | Atendido (S/N) | Observações |
|---|:---:|---|
| **RNF30 — Tratamento de Erros:** O sistema deve tratar erros de forma graciosa. | S | O sistema possui tratamento de exceções com mensagens amigáveis e página 404 personalizada. |
| **RNF31 — Validação de Dados:** O sistema deve validar todos os dados de entrada. | S | O sistema possui validação no frontend e backend para todos os formulários. |
| **RNF32 — Prevenção de Duplicidade:** O sistema deve prevenir a inserção de dados duplicados. | S | O sistema possui validações para evitar e-mails duplicados, interesses duplicados etc. |
| **RNF33 — Integridade Referencial:** O sistema deve manter a integridade dos dados com chaves estrangeiras. | S | O sistema utiliza chaves estrangeiras no banco de dados para manter a integridade referencial. |
| **RNF34 — Transações no Banco:** O sistema deve utilizar transações para operações que envolvem múltiplas tabelas. | S | O sistema utiliza transações PDO para garantir atomicidade e consistência. |

### Legenda

- **S:** Atendido.
- **P:** Parcialmente atendido.
- **N:** Não atendido.
