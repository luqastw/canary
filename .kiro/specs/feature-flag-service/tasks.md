# Implementation Plan - Feature Flag Service

## Overview
Implementação de serviço multi-tenant de feature flags seguindo o padrão Laravel: **Migration → Model → Repository → Service → Request → Controller → Resource → Route → Test** para cada módulo.

## Execution Strategy
- Tasks marcadas com `(P)` podem ser executadas em paralelo
- Tasks sem marcação devem ser executadas sequencialmente
- Coverage mínimo de testes: 80%
- Seguir ordem dos grupos para manter dependências

---

## Grupo 1: Infraestrutura Base e Multi-Tenancy

### 1. Setup Inicial do Projeto
- [ ] 1.1 Configurar banco de dados e conexões (P)
  - Configurar `.env` com credenciais de MySQL/PostgreSQL
  - Configurar Redis para cache
  - Testar conexões via `php artisan migrate:status`
  - _Requirements: 7, 8_

- [ ] 1.2 Instalar e configurar Laravel Sanctum (P)
  - `composer require laravel/sanctum`
  - Publicar configuração: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
  - Executar migration: `php artisan migrate`
  - _Requirements: 1_

- [ ] 1.3 Configurar estrutura de diretórios
  - Criar `app/Repositories` e `app/Services`
  - Criar `app/Http/Resources`
  - Criar `app/Models/Scopes`
  - Criar contratos: `app/Contracts/Repositories` e `app/Contracts/Services`
  - _Requirements: 8_

### 2. Migrations - Estrutura de Dados
- [ ] 2.1 Migration: Tenants (P)
  - Tabela `tenants` com campos: id, name, email, status, created_at, updated_at
  - Índices: status, email (unique)
  - Enum `status`: active, suspended, deleted
  - _Requirements: 1, 7_

- [ ] 2.2 Migration: Flags (P)
  - Tabela `flags` com campos: id, tenant_id, key, name, description, is_enabled, created_at, updated_at, deleted_at
  - Índices: (tenant_id, key) unique, (tenant_id, is_enabled), deleted_at
  - Foreign key: tenant_id → tenants(id) CASCADE
  - _Requirements: 2, 7_

- [ ] 2.3 Migration: Groups (P)
  - Tabela `groups` com campos: id, tenant_id, identifier, name, description, created_at
  - Índices: (tenant_id, identifier) unique, tenant_id
  - Foreign key: tenant_id → tenants(id) CASCADE
  - _Requirements: 3, 7_

- [ ] 2.4 Migration: Flag Targeting (P)
  - Tabela `flag_targeting` com campos: id, flag_id, group_id, created_at, deleted_at
  - Índices: (flag_id, group_id) unique, flag_id, group_id, deleted_at
  - Foreign keys: flag_id → flags(id) CASCADE, group_id → groups(id) CASCADE
  - _Requirements: 4, 7_

### 3. Models - Domain Layer
- [ ] 3.1 Model: Tenant
  - Fillable: name, email, status
  - Casts: status → TenantStatus enum, created_at → datetime
  - Relationships: apiTokens (HasMany), flags (HasMany), groups (HasMany)
  - Enum TenantStatus: ACTIVE, SUSPENDED, DELETED
  - _Requirements: 1, 7_

- [ ] 3.2 Model: Flag
  - Fillable: tenant_id, key, name, description, is_enabled
  - Casts: is_enabled → boolean
  - SoftDeletes trait
  - Global Scope: TenantScope
  - Relationships: tenant (BelongsTo), targetingRules (HasMany)
  - Method: hasTargeting(): bool
  - _Requirements: 2, 5, 7_

- [ ] 3.3 Model: Group
  - Fillable: tenant_id, identifier, name, description
  - Global Scope: TenantScope
  - Relationships: tenant (BelongsTo), targetingRules (HasMany)
  - _Requirements: 3, 7_

- [ ] 3.4 Model: Targeting
  - Table: flag_targeting
  - Fillable: flag_id, group_id
  - SoftDeletes trait
  - Global Scope: TenantScope (via relationships)
  - Relationships: flag (BelongsTo), group (BelongsTo)
  - _Requirements: 4, 7_

- [ ] 3.5 Global Scope: TenantScope
  - Implementar interface `Scope`
  - Aplicar filtro `where('tenant_id', auth()->user()->tenant_id)` automaticamente
  - Verificar autenticação antes de aplicar
  - _Requirements: 7_

---

## Grupo 2: Auth Module (Autenticação e Tenants)

### 4. Repositories - Auth
- [ ] 4.1 TenantRepository e contrato
  - Interface: `TenantRepositoryInterface`
  - Métodos: findById, findByEmail, create, update, generateApiToken
  - Implementação com queries otimizadas
  - _Requirements: 1_

### 5. Services - Auth
- [ ] 5.1 AuthService e contrato
  - Interface: `AuthServiceInterface`
  - Métodos: registerTenant, generateApiToken, validateToken, revokeToken
  - Orquestrar TenantRepository + Sanctum
  - Validação de unicidade de email
  - _Requirements: 1_

### 6. Requests - Auth
- [ ] 6.1 RegisterTenantRequest (P)
  - Validação: name (required, max:255), email (required, email, unique:tenants), password (required, min:8)
  - _Requirements: 1_

- [ ] 6.2 LoginRequest (P)
  - Validação: email (required, email), password (required)
  - _Requirements: 1_

### 7. Controllers - Auth
- [ ] 7.1 AuthController (API)
  - POST /api/auth/register → registerTenant
  - POST /api/auth/login → login (retorna token)
  - POST /api/auth/logout → revokeToken
  - Delegates para AuthService
  - _Requirements: 1_

- [ ] 7.2 WebAuthController (Web - Blade)
  - GET /login → showLoginForm
  - POST /login → authenticate (session-based)
  - POST /logout → logout
  - GET /register → showRegistrationForm
  - POST /register → register
  - _Requirements: 1, 6_

### 8. Resources - Auth
- [ ] 8.1 TenantResource (P)
  - Campos: id, name, email, status, created_at
  - Omitir token de API em response (apenas na criação)
  - _Requirements: 1_

- [ ] 8.2 ApiTokenResource (P)
  - Campos: token (apenas na criação), name, expires_at, created_at
  - _Requirements: 1_

### 9. Routes - Auth
- [ ] 9.1 API Routes - Auth
  - POST /api/v1/auth/register
  - POST /api/v1/auth/login
  - POST /api/v1/auth/logout (middleware: auth:sanctum)
  - Throttle: 60 req/min por IP
  - _Requirements: 1_

- [ ] 9.2 Web Routes - Auth
  - GET/POST /login
  - GET/POST /register
  - POST /logout
  - Middleware: guest (para login/register), auth (para logout)
  - _Requirements: 1, 6_

### 10. Tests - Auth Module
- [ ] 10.1 Unit Tests - AuthService (P)
  - Teste: registerTenant cria tenant e gera API token
  - Teste: registerTenant lança exception para email duplicado
  - Teste: validateToken retorna tenant correto
  - Teste: revokeToken invalida token
  - _Requirements: 1_

- [ ] 10.2 Integration Tests - Auth API (P)
  - Teste: POST /api/auth/register com dados válidos retorna 201 + token
  - Teste: POST /api/auth/register com email duplicado retorna 422
  - Teste: POST /api/auth/login com credenciais válidas retorna token
  - Teste: POST /api/auth/login com credenciais inválidas retorna 401
  - Teste: POST /api/auth/logout invalida token
  - _Requirements: 1_

- [ ] 10.3 E2E Tests - Web Auth (P)
  - Teste: Usuário não autenticado acessa /dashboard → redirect para /login
  - Teste: Registro via formulário web cria tenant e faz login
  - Teste: Login via formulário web autentica e redireciona para dashboard
  - Teste: Logout invalida sessão
  - _Requirements: 1, 6_

---

## Grupo 3: Flags Module (Feature Flags)

### 11. Repositories - Flags
- [ ] 11.1 FlagRepository e contrato
  - Interface: `FlagRepositoryInterface`
  - Métodos: findById, findByKey, findByKeyWithTargeting, getAllForTenant, create, update, delete
  - Eager loading: targetingRules.group
  - Índices otimizados: (tenant_id, key)
  - _Requirements: 2, 5_

### 12. Services - Flags
- [ ] 12.1 FlagService e contrato
  - Interface: `FlagServiceInterface`
  - Métodos: getAll, getById, create, update, toggle, delete
  - Validação: key único por tenant, formato slug
  - Orquestrar cache invalidation após updates
  - _Requirements: 2_

### 13. Requests - Flags
- [ ] 13.1 CreateFlagRequest (P)
  - Validação: key (required, slug, max:100, unique:flags,key,tenant_id), name (required, max:255), description (nullable), is_enabled (boolean)
  - Custom rule: validar formato slug para key
  - _Requirements: 2_

- [ ] 13.2 UpdateFlagRequest (P)
  - Validação: name (max:255), description (nullable), is_enabled (boolean)
  - Key não pode ser alterado após criação
  - _Requirements: 2_

### 14. Controllers - Flags
- [ ] 14.1 FlagController (API)
  - GET /api/v1/flags → index (listar flags do tenant)
  - POST /api/v1/flags → store
  - GET /api/v1/flags/{id} → show
  - PUT /api/v1/flags/{id} → update
  - DELETE /api/v1/flags/{id} → destroy
  - PATCH /api/v1/flags/{id}/toggle → toggle is_enabled
  - Middleware: auth:sanctum
  - _Requirements: 2_

- [ ] 14.2 WebFlagController (Web)
  - GET /flags → index (listagem com tabela)
  - GET /flags/create → create (formulário)
  - POST /flags → store
  - GET /flags/{id} → show (detalhes + targeting)
  - GET /flags/{id}/edit → edit (formulário)
  - PUT /flags/{id} → update
  - DELETE /flags/{id} → destroy
  - Middleware: auth (session)
  - _Requirements: 2, 6_

### 15. Resources - Flags
- [ ] 15.1 FlagResource (P)
  - Campos: id, key, name, description, is_enabled, has_targeting (computed), created_at, updated_at
  - Conditional: incluir targeting_rules quando loaded
  - _Requirements: 2_

- [ ] 15.2 FlagCollection (P)
  - Wraps FlagResource array
  - Metadata: total, filters applied
  - _Requirements: 2_

### 16. Routes - Flags
- [ ] 16.1 API Routes - Flags
  - Resourceful: apiResource('flags', FlagController::class)
  - Custom: PATCH /flags/{id}/toggle
  - Middleware: auth:sanctum, throttle:1000,1
  - _Requirements: 2_

- [ ] 16.2 Web Routes - Flags
  - Resourceful: resource('flags', WebFlagController::class)
  - Middleware: auth
  - _Requirements: 2, 6_

### 17. Views - Flags (Blade)
- [ ] 17.1 flags/index.blade.php (P)
  - Tabela de flags com colunas: Key, Name, Status (badge), Targeting (yes/no), Actions
  - Botão "Create Flag"
  - Toggle inline com Alpine.js para enable/disable
  - _Requirements: 6_

- [ ] 17.2 flags/create.blade.php (P)
  - Formulário: key (input), name (input), description (textarea), is_enabled (checkbox)
  - Validação client-side com Alpine.js
  - _Requirements: 6_

- [ ] 17.3 flags/edit.blade.php (P)
  - Similar ao create, mas key readonly
  - _Requirements: 6_

- [ ] 17.4 flags/show.blade.php (P)
  - Detalhes da flag
  - Seção de targeting rules (listagem de grupos associados)
  - Botão "Manage Targeting"
  - _Requirements: 6_

### 18. Tests - Flags Module
- [ ] 18.1 Unit Tests - FlagService (P)
  - Teste: create valida unicidade de key por tenant
  - Teste: delete soft deletes flag e cascade deletes targeting rules
  - Teste: toggle alterna is_enabled
  - _Requirements: 2_

- [ ] 18.2 Unit Tests - Flag Model (P)
  - Teste: hasTargeting retorna true quando existem rules
  - Teste: TenantScope filtra flags por tenant
  - _Requirements: 2, 7_

- [ ] 18.3 Integration Tests - Flags API (P)
  - Teste: GET /api/flags retorna apenas flags do tenant autenticado
  - Teste: POST /api/flags cria flag com dados válidos
  - Teste: POST /api/flags com key duplicada retorna 422
  - Teste: PUT /api/flags/{id} atualiza flag
  - Teste: DELETE /api/flags/{id} soft deletes flag
  - Teste: Tenant A não consegue acessar flag do Tenant B (retorna 404)
  - _Requirements: 2, 7_

- [ ] 18.4 E2E Tests - Web Flags (P)
  - Teste: Criar flag via formulário → flag aparece na listagem
  - Teste: Toggle enable/disable persiste após refresh
  - Teste: Deletar flag remove da listagem
  - _Requirements: 2, 6_

---

## Grupo 4: Groups Module (Grupos)

### 19. Repositories - Groups
- [ ] 19.1 GroupRepository e contrato
  - Interface: `GroupRepositoryInterface`
  - Métodos: findById, findByIdentifier, getAllForTenant, create, update, delete
  - Validação: identifier único por tenant
  - _Requirements: 3_

### 20. Services - Groups
- [ ] 20.1 GroupService e contrato
  - Interface: `GroupServiceInterface`
  - Métodos: getAll, getById, create, update, delete
  - Validação: identifier formato slug, unicidade por tenant
  - _Requirements: 3_

### 21. Requests - Groups
- [ ] 21.1 CreateGroupRequest (P)
  - Validação: identifier (required, slug, max:100, unique:groups,identifier,tenant_id), name (required, max:255), description (nullable)
  - _Requirements: 3_

- [ ] 21.2 UpdateGroupRequest (P)
  - Validação: name (max:255), description (nullable)
  - Identifier não pode ser alterado
  - _Requirements: 3_

### 22. Controllers - Groups
- [ ] 22.1 GroupController (API)
  - GET /api/v1/groups → index
  - POST /api/v1/groups → store
  - GET /api/v1/groups/{id} → show
  - PUT /api/v1/groups/{id} → update
  - DELETE /api/v1/groups/{id} → destroy
  - Middleware: auth:sanctum
  - _Requirements: 3_

- [ ] 22.2 WebGroupController (Web)
  - GET /groups → index
  - GET /groups/create → create
  - POST /groups → store
  - GET /groups/{id}/edit → edit
  - PUT /groups/{id} → update
  - DELETE /groups/{id} → destroy
  - Middleware: auth
  - _Requirements: 3, 6_

### 23. Resources - Groups
- [ ] 23.1 GroupResource (P)
  - Campos: id, identifier, name, description, created_at
  - _Requirements: 3_

### 24. Routes - Groups
- [ ] 24.1 API Routes - Groups
  - Resourceful: apiResource('groups', GroupController::class)
  - Middleware: auth:sanctum
  - _Requirements: 3_

- [ ] 24.2 Web Routes - Groups
  - Resourceful: resource('groups', WebGroupController::class)
  - Middleware: auth
  - _Requirements: 3, 6_

### 25. Views - Groups (Blade)
- [ ] 25.1 groups/index.blade.php (P)
  - Tabela de grupos: Identifier, Name, Actions
  - Botão "Create Group"
  - _Requirements: 6_

- [ ] 25.2 groups/create.blade.php (P)
  - Formulário: identifier, name, description
  - _Requirements: 6_

- [ ] 25.3 groups/edit.blade.php (P)
  - Similar ao create, identifier readonly
  - _Requirements: 6_

### 26. Tests - Groups Module
- [ ] 26.1 Unit Tests - GroupService (P)
  - Teste: create valida unicidade de identifier por tenant
  - Teste: delete remove grupo e desassocia targeting rules
  - _Requirements: 3_

- [ ] 26.2 Integration Tests - Groups API (P)
  - Teste: GET /api/groups retorna apenas grupos do tenant
  - Teste: POST /api/groups cria grupo com dados válidos
  - Teste: POST /api/groups com identifier duplicado retorna 422
  - Teste: Tenant A não consegue acessar grupo do Tenant B
  - _Requirements: 3, 7_

- [ ] 26.3 E2E Tests - Web Groups (P)
  - Teste: Criar grupo via formulário → grupo aparece na listagem
  - Teste: Editar grupo persiste mudanças
  - _Requirements: 3, 6_

---

## Grupo 5: Targeting Module (Regras de Targeting)

### 27. Repositories - Targeting
- [ ] 27.1 TargetingRepository e contrato
  - Interface: `TargetingRepositoryInterface`
  - Métodos: createBatch, getRulesForFlag, deleteRulesForFlag, deleteRule, validateOwnership
  - Query otimizada: validateOwnership com subquery única
  - _Requirements: 4_

### 28. Services - Targeting
- [ ] 28.1 TargetingService e contrato
  - Interface: `TargetingServiceInterface`
  - Métodos: createRules, removeRule, replaceRules, getRulesForFlag
  - Validação: ownership de flag e groups
  - Cache invalidation após mudanças
  - DB transactions para atomicidade
  - _Requirements: 4_

### 29. Requests - Targeting
- [ ] 29.1 CreateTargetingRequest (P)
  - Validação: flag_id (required, exists:flags,id), group_ids (required, array, min:1), group_ids.* (exists:groups,id)
  - _Requirements: 4_

- [ ] 29.2 DeleteTargetingRequest (P)
  - Validação: flag_id (required), group_id (required)
  - _Requirements: 4_

### 30. Controllers - Targeting
- [ ] 30.1 TargetingController (API)
  - POST /api/v1/targeting → store (criar regras)
  - GET /api/v1/flags/{flagId}/targeting → index (listar regras de uma flag)
  - DELETE /api/v1/targeting → destroy (remover regra específica)
  - PUT /api/v1/flags/{flagId}/targeting → replace (substituir todas as regras)
  - Middleware: auth:sanctum
  - _Requirements: 4_

- [ ] 30.2 WebTargetingController (Web)
  - GET /flags/{flagId}/targeting → manage (tela de gerenciamento)
  - POST /flags/{flagId}/targeting → store
  - DELETE /flags/{flagId}/targeting/{groupId} → destroy
  - Middleware: auth
  - _Requirements: 4, 6_

### 31. Resources - Targeting
- [ ] 31.1 TargetingResource (P)
  - Campos: id, flag (FlagResource), group (GroupResource), created_at
  - _Requirements: 4_

### 32. Routes - Targeting
- [ ] 32.1 API Routes - Targeting
  - POST /targeting → TargetingController@store
  - GET /flags/{flagId}/targeting → TargetingController@index
  - DELETE /targeting → TargetingController@destroy
  - PUT /flags/{flagId}/targeting → TargetingController@replace
  - Middleware: auth:sanctum
  - _Requirements: 4_

- [ ] 32.2 Web Routes - Targeting
  - GET /flags/{flagId}/targeting → WebTargetingController@manage
  - POST /flags/{flagId}/targeting → WebTargetingController@store
  - DELETE /flags/{flagId}/targeting/{groupId} → WebTargetingController@destroy
  - Middleware: auth
  - _Requirements: 4, 6_

### 33. Views - Targeting (Blade)
- [ ] 33.1 targeting/manage.blade.php (P)
  - Interface com two-pane: grupos disponíveis | grupos associados
  - Drag-and-drop ou checkboxes para associar/desassociar
  - Ajax para operações sem reload
  - _Requirements: 6_

### 34. Tests - Targeting Module
- [ ] 34.1 Unit Tests - TargetingService (P)
  - Teste: createRules valida ownership antes de criar
  - Teste: createRules lança exception quando flag/group de outro tenant
  - Teste: createRules executa rollback em caso de falha
  - Teste: replaceRules remove antigas e cria novas atomicamente
  - _Requirements: 4, 7_

- [ ] 34.2 Integration Tests - Targeting API (P)
  - Teste: POST /api/targeting cria regras com dados válidos
  - Teste: POST /api/targeting com group de outro tenant retorna 404
  - Teste: GET /api/flags/{id}/targeting retorna regras apenas do tenant
  - Teste: DELETE /api/targeting remove regra específica
  - Teste: PUT /api/flags/{id}/targeting substitui todas as regras
  - _Requirements: 4, 7_

- [ ] 34.3 E2E Tests - Web Targeting (P)
  - Teste: Associar grupos a flag via interface → regras criadas
  - Teste: Remover associação → regra deletada
  - _Requirements: 4, 6_

---

## Grupo 6: Evaluation Module (Avaliação de Flags - CRÍTICO)

### 35. Services - Evaluation
- [ ] 35.1 EvaluationService e contrato
  - Interface: `EvaluationServiceInterface`
  - Métodos: evaluate(tenantId, flagKey, context), evaluateBatch(tenantId, flagKeys, context)
  - Classes: EvaluationContext, EvaluationResult
  - Lógica: targeting rules > status global > fail-safe (false)
  - Cache strategy: Redis, TTL 5min, key format `flag:{tenant}:{key}`
  - Performance target: < 50ms p95
  - _Requirements: 5_

### 36. Requests - Evaluation
- [ ] 36.1 EvaluateRequest (P)
  - Validação: flag_key (required, string, max:100), context.user_id (required, string, max:255), context.role (required, string, max:100), context.metadata (nullable, array)
  - _Requirements: 5_

- [ ] 36.2 BatchEvaluateRequest (P)
  - Validação: flag_keys (required, array, min:1, max:50), context (required, object)
  - _Requirements: 5_

### 37. Controllers - Evaluation
- [ ] 37.1 EvaluationController (Public API)
  - POST /api/v1/evaluate → evaluate
  - POST /api/v1/evaluate/batch → evaluateBatch
  - Middleware: auth:sanctum, throttle:1000,1 (1000 req/min)
  - Response time monitoring
  - _Requirements: 5_

### 38. Resources - Evaluation
- [ ] 38.1 EvaluationResource (P)
  - Campos: enabled (boolean), reason (targeting|global|default), variant (null, futuro)
  - _Requirements: 5_

- [ ] 38.2 BatchEvaluationResource (P)
  - Formato: { flag_key: { enabled, reason } }
  - _Requirements: 5_

### 39. Routes - Evaluation
- [ ] 39.1 API Routes - Evaluation
  - POST /api/v1/evaluate → EvaluationController@evaluate
  - POST /api/v1/evaluate/batch → EvaluationController@evaluateBatch
  - Middleware: auth:sanctum, throttle:1000,1
  - Sem rate limit por IP (apenas por token)
  - _Requirements: 5_

### 40. Tests - Evaluation Module (CRÍTICO)
- [ ] 40.1 Unit Tests - EvaluationService (P)
  - Teste: evaluate retorna false para flag inexistente (fail-safe)
  - Teste: evaluate retorna is_enabled quando flag não tem targeting
  - Teste: evaluate retorna true quando context.role está em targeting rules
  - Teste: evaluate retorna false quando context.role NÃO está em targeting rules
  - Teste: Cache hit evita query no DB
  - _Requirements: 5_

- [ ] 40.2 Integration Tests - Evaluation API (P)
  - Teste: POST /api/evaluate com flag sem targeting retorna status global
  - Teste: POST /api/evaluate com flag com targeting retorna true quando role match
  - Teste: POST /api/evaluate com token inválido retorna 401
  - Teste: POST /api/evaluate com flag inexistente retorna {enabled: false, reason: default}
  - Teste: POST /api/evaluate/batch avalia múltiplas flags
  - _Requirements: 5_

- [ ] 40.3 Integration Tests - Cache Behavior (P)
  - Teste: Primeiro evaluate causa cache miss, segundo causa hit
  - Teste: Atualização de targeting rule invalida cache da flag
  - Teste: Cache expira após 300 segundos
  - _Requirements: 5_

- [ ] 40.4 Performance Tests - Evaluation Load (P)
  - Teste: 1000 req/s concorrentes mantém p95 < 50ms
  - Teste: Cache hit rate > 80% após warm-up
  - Teste: Throughput de 5000 req/s sem degradação
  - Usar k6 ou Apache JMeter
  - _Requirements: 5_

---

## Grupo 7: Web Panel (Painel Administrativo)

### 41. Layouts e Components (Blade)
- [ ] 41.1 Layout principal - layouts/app.blade.php (P)
  - Header com logo e menu de navegação
  - Sidebar: Dashboard, Flags, Groups
  - Footer com versão
  - Alpine.js e Tailwind CSS
  - _Requirements: 6_

- [ ] 41.2 Layout guest - layouts/guest.blade.php (P)
  - Para login/registro
  - Design minimalista
  - _Requirements: 6_

- [ ] 41.3 Components Blade (P)
  - components/alert.blade.php (sucesso, erro, warning)
  - components/button.blade.php (primary, secondary, danger)
  - components/input.blade.php (text input com validação)
  - components/table.blade.php (tabela responsiva)
  - _Requirements: 6_

### 42. Dashboard
- [ ] 42.1 dashboard/index.blade.php (P)
  - Cards com métricas: Total Flags, Flags Ativas, Total Grupos
  - Listagem de flags recentemente criadas
  - Link rápido para criar flag
  - _Requirements: 6_

- [ ] 42.2 DashboardController
  - GET /dashboard → index
  - Buscar métricas do tenant
  - Middleware: auth
  - _Requirements: 6_

### 43. Assets e Frontend
- [ ] 43.1 Configurar Vite para build (P)
  - vite.config.js com Laravel plugin
  - app.js: importar Alpine.js
  - app.css: importar Tailwind CSS
  - _Requirements: 6_

- [ ] 43.2 JavaScript - Interatividade (P)
  - Alpine.js para toggles inline
  - Ajax calls para operações sem reload (targeting)
  - Toast notifications
  - _Requirements: 6_

### 44. Tests - Web Panel
- [ ] 44.1 E2E Tests - Dashboard (P)
  - Teste: Dashboard exibe métricas corretas
  - Teste: Links de navegação funcionam
  - _Requirements: 6_

- [ ] 44.2 E2E Tests - Navegação completa (P)
  - Teste: Fluxo completo: Login → Criar Flag → Criar Grupo → Associar Targeting → Logout
  - _Requirements: 6_

---

## Grupo 8: Middleware e Security

### 45. Middleware Customizado
- [ ] 45.1 TenantMiddleware (P)
  - Cache tenant info no request para evitar queries repetidas
  - Verificar status do tenant (apenas 'active' pode acessar)
  - _Requirements: 7_

- [ ] 45.2 ApiResponseMiddleware (P)
  - Adicionar headers: X-Request-Id, X-RateLimit-*
  - Formatar erros consistentemente
  - _Requirements: 5_

### 46. Exception Handling
- [ ] 46.1 Custom Exceptions (P)
  - UnauthorizedTenantException (lança 404 para security)
  - OwnershipValidationException
  - FlagNotFoundException (retorna fail-safe na evaluation)
  - _Requirements: 7_

- [ ] 46.2 Handler - app/Exceptions/Handler.php
  - Tratar exceptions customizadas
  - Log com trace_id para rastreabilidade
  - Redact tokens e emails nos logs
  - _Requirements: 5, 7_

### 47. Rate Limiting
- [ ] 47.1 Configurar throttle customizado (P)
  - RateLimiter::for('api-evaluation', 1000 req/min por token)
  - RateLimiter::for('api-management', 60 req/min por token)
  - Response headers: X-RateLimit-Limit, X-RateLimit-Remaining
  - _Requirements: 5_

---

## Grupo 9: Observability e Monitoring

### 48. Logging e Auditing
- [ ] 48.1 Events - Domain Events (P)
  - FlagCreated, FlagUpdated, FlagDeleted
  - TargetingRulesCreated, TargetingRulesDeleted
  - TenantRegistered
  - _Requirements: 8_

- [ ] 48.2 Listeners - Audit Log (P)
  - LogFlagChanges (listener para flag events)
  - LogTargetingChanges
  - Persistir em tabela `audit_logs` ou log file
  - _Requirements: 8_

### 49. Health Checks
- [ ] 49.1 HealthController (P)
  - GET /health/live → retorna 200 (app is up)
  - GET /health/ready → verifica DB + Redis, retorna 200/503
  - Sem autenticação (para K8s probes)
  - _Requirements: 5_

### 50. Monitoring e Metrics
- [ ] 50.1 Integração com observability (P)
  - Configurar Laravel Telescope (desenvolvimento)
  - Preparar integração com Sentry (produção)
  - Log de slow queries (> 100ms)
  - _Requirements: 5_

---

## Grupo 10: Documentation e Finalization

### 51. API Documentation
- [ ] 51.1 Gerar OpenAPI/Swagger (P)
  - Instalar l5-swagger ou Scramble
  - Documentar todos os endpoints com exemplos
  - Incluir schemas de request/response
  - _Requirements: 8_

### 52. Seeders e Factories
- [ ] 52.1 Factories completas (P)
  - TenantFactory, FlagFactory, GroupFactory, TargetingFactory
  - States: enabled(), withTargeting(Group $group)
  - _Requirements: 8_

- [ ] 52.2 DatabaseSeeder (P)
  - Criar 3 tenants de exemplo
  - Cada tenant com 5 flags, 3 grupos, 10 targeting rules
  - Dados realistas para demonstração
  - _Requirements: 8_

### 53. Testes de Cobertura
- [ ] 53.1 Executar coverage report
  - `php artisan test --coverage --min=80`
  - Gerar HTML report
  - Identificar gaps de cobertura
  - _Requirements: 8_

### 54. README e Setup
- [ ] 54.1 README.md (P)
  - Instruções de instalação
  - Variáveis de ambiente necessárias
  - Comandos de setup: migrate, seed
  - Exemplos de uso da API
  - _Requirements: 8_

- [ ] 54.2 Postman Collection (P)
  - Exportar collection com todos os endpoints
  - Variáveis: {{base_url}}, {{api_token}}
  - Exemplos de cada endpoint
  - _Requirements: 8_

---

## Summary

**Total Tasks**: 54 grupos principais (150+ sub-tasks)
**Estimated Effort**: 80-100 horas de desenvolvimento
**Dependencies**:
- Grupo 1 (Base) → Todos os outros
- Grupo 2 (Auth) → Grupos 3, 4, 5, 6, 7
- Grupos 3, 4 (Flags, Groups) → Grupo 5 (Targeting)
- Grupos 3, 4, 5 (Flags, Groups, Targeting) → Grupo 6 (Evaluation)
- Todos os módulos → Grupo 7 (Web Panel)

**Critical Path**: 
1. Infraestrutura (Grupo 1)
2. Auth (Grupo 2)
3. Flags (Grupo 3)
4. Groups (Grupo 4)
5. Targeting (Grupo 5)
6. Evaluation (Grupo 6) **← CRITICAL**
7. Web Panel (Grupo 7)

**Parallelization Opportunities**:
- Grupos 3 e 4 podem ser desenvolvidos em paralelo após Grupo 2
- Views (Blade) podem ser desenvolvidas em paralelo com controllers/services
- Testes podem ser escritos em paralelo com implementação
