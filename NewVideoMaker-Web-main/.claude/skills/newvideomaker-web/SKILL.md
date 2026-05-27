---
name: newvideomaker-web
description: >-
  Use sempre que Elivan ou Nícolas (TCC UNIVATES) mencionarem o NewVideoMaker-Web,
  ou trabalharem no projeto Laravel/Blade que orquestra o pipeline Python de geração
  de vídeo via interface web. Cobre arquitetura (Laravel 12 + Blade + Tailwind v4 +
  SQLite, sem Inertia/Vue ativo), padrões do projeto (AppConfig service em vez de
  config()/.env direto, jobs em filas separadas video-generation/youtube/default),
  estrutura de pastas, integração YouTube Shorts (OAuth 2.0 + upload resumível +
  agendamento via publishAt), a tela /config que substitui edição manual de .env,
  e o launcher PowerShell em launcher/. Acione mesmo quando o usuário não nomear o
  projeto, mas estiver mexendo em VideoController, YoutubeShortsController,
  YoutubeAuthController, SettingsController, HealthController, nos jobs GerarVideo
  ou PublicarYoutubeShort, nos services AppConfig ou YoutubeService, em Blades de
  resources/views/{videos,shorts,pages,health,components}/, em rotas web.php, em
  migrations de videos/youtube_*/app_settings, ou nos arquivos do launcher
  (start.ps1, start.bat, launcher.config.json). Também vale para próximas etapas
  planejadas: empacotamento Inno Setup, sistema de licença, conversão do launcher.
---

# NewVideoMaker-Web

Backend Laravel + interface web que orquestra o pipeline Python local de geração de
vídeos curtos. É a camada de UI/API do TCC de Elivan e Nícolas (UNIVATES) — o
processamento pesado de IA fica no projeto Python separado (veja a seção final).

## Contexto e localização

- Caminho do projeto: `C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\`.
- **O repositório git fica um nível ACIMA** — em `NewVideoMaker-Web-integrado-tailwind-blade\.git`. A pasta `NewVideoMaker-Web-main\` é um subdiretório do repo, não a raiz git. Comandos git precisam considerar isso (`git -C` apontando para o nível de cima).
- Não criar git worktrees dentro de `.claude/worktrees/` a menos que pedido — eles geram pastas duplicadas confusas. Trabalhe direto no projeto.

## Stack

- **Laravel 12**, PHP 8.2+ (ambiente de dev tem PHP 8.3).
- **Blade + Tailwind v4** para a interface. O projeto começou com Inertia.js + Vue 3
  mas a interface principal foi convertida para Blade — Vue/Inertia não são mais o
  caminho ativo. Ao criar telas novas, use Blade, não Vue.
- **SQLite** como banco (`database/database.sqlite`).
- **Fila `database`** para jobs assíncronos.
- Pipeline Python disparado via `Symfony\Component\Process\Process`.
- Ícones via Lucide (CDN, `data-lucide="..."`).

## Mapa do projeto

```
app/
  Http/Controllers/
    VideoController.php        — CRUD de vídeos + dashboard + downloads
    HealthController.php       — status dos serviços Python + caminhos do pipeline
    SettingsController.php     — tela /config: salva credenciais e caminhos
    YoutubeShortsController.php— CRUD de publicações no YouTube
    YoutubeAuthController.php  — fluxo OAuth 2.0 (redirect/callback/disconnect)
  Jobs/
    GerarVideo.php             — dispara pipeline.py, fila "video-generation"
    PublicarYoutubeShort.php   — upload pro YouTube, fila "youtube"
  Models/
    Video.php, YoutubeAccount.php, YoutubeUpload.php, AppSetting.php, User.php
  Services/
    AppConfig.php              — configuração unificada (DB + fallback .env)
    YoutubeService.php         — wrapper da YouTube Data API v3 + OAuth
resources/views/
  layouts/app.blade.php        — layout base; banner global de config incompleta
  components/sidebar.blade.php — menu lateral (navItems)
  videos/                      — create, index, status, show
  shorts/                      — index, create, show (YouTube Shorts)
  pages/                       — dashboard, pipeline, config, sobre
  health/index.blade.php       — status dos serviços
routes/web.php                 — todas as rotas web
launcher/                      — launcher PowerShell (ver seção própria)
config/services.php            — bloco "youtube"
config/videogen.php            — caminhos do pipeline (fallback)
database/migrations/           — videos, youtube_accounts, youtube_uploads, app_settings
```

## Padrão central: AppConfig em vez de config()/.env

O projeto vai ser distribuído para clientes finais que **não devem editar `.env`**.
Por isso existe o service `App\Services\AppConfig`:

- `AppConfig::get('chave', $default)` — lê primeiro a tabela `app_settings` (salva via
  UI), com fallback automático para `.env`/`config()` quando o valor não foi salvo.
- `AppConfig::set('chave', $valor)` e `setMany([...])` — persistem no DB e invalidam cache.
- Chaves conhecidas: `youtube.client_id`, `youtube.client_secret` (secreta, encriptada),
  `youtube.redirect_uri`, `videogen.python_path`, `videogen.pipeline_path`,
  `videogen.output_dir`.
- `pendencies()` lista o que falta configurar — usado pelo banner global no layout.

**Ao escrever código novo que precise de configuração, use `AppConfig`, nunca
`config()` ou `env()` direto.** O motivo: qualquer config nova precisa ser editável
pela tela `/config` sem mexer em arquivo. Injete `AppConfig` no construtor.

Detalhe importante: `AppConfig` faz cache de 10 min (`Cache`). Em testes ou após
mudanças diretas no DB, chame `AppConfig::flush()`.

## Filas

Existem três filas e o worker precisa escutar todas:

- `video-generation` — job `GerarVideo`. **1 vídeo por vez** (VRAM é gargalo), `tries=1`.
- `youtube` — job `PublicarYoutubeShort` (upload de vídeo para o YouTube).
- `default` — fila padrão do Laravel.

O comando correto do worker (já refletido no `composer run dev` e no launcher):

```
php artisan queue:work --queue=video-generation,youtube,default --timeout=1500
```

Se um job ficar preso em `pending`, a causa quase sempre é o worker não escutar a
fila certa. Cheque a tabela `jobs` (`where queue = ...`).

## Integração YouTube Shorts

Fluxo OAuth 2.0 sem SDK do Google (usa o HTTP client do Laravel direto, em
`YoutubeService`):

1. Cliente configura `client_id`/`client_secret` em `/config` (cada cliente cria o
   próprio projeto Google Cloud — a quota da YouTube Data API é 10.000 unidades/dia,
   ~6 uploads/dia, e é por projeto).
2. `/shorts/youtube/connect` → consentimento Google → `/shorts/youtube/callback`
   troca o code por tokens. `access_type=offline` + `prompt=consent` garantem o
   `refresh_token`.
3. Tokens ficam na tabela `youtube_accounts` com cast `encrypted`.
4. Publicação: o job `PublicarYoutubeShort` faz **upload resumível**.

**Agendamento — ponto crítico:** não usar `delay()` na fila para segurar o job. O
upload acontece **imediatamente**; o agendamento é responsabilidade do YouTube via
`publishAt` (ISO 8601 UTC). Quando há `publishAt`, o `YoutubeService` força
`privacyStatus=private` — o YouTube torna o vídeo público sozinho no horário. Isso
evita depender do worker estar de pé na hora marcada. Status do upload: `pending` →
`uploading` → `scheduled` (se agendado) ou `published`.

`category_id` padrão 22 (People & Blogs). Limites do YouTube validados no
controller: título 100 chars, descrição 5000, tags 500 chars no total. Rate limit
local: 10 uploads/hora por IP.

## Tela de Configurações (/config)

`SettingsController` + `resources/views/pages/config.blade.php`. Substituiu a página
mock antiga. Tem seções: estado geral, Pipeline Python (caminhos), serviços locais
(status), credenciais YouTube (com tutorial OAuth embutido em `<details>`). O
`layouts/app.blade.php` injeta `AppConfig` e mostra um banner vermelho de
"configuração incompleta" em toda página exceto `/config`.

## Launcher PowerShell

Pasta `launcher/` — sobe tudo com um clique, sem o usuário abrir terminais:

- `start.bat` — entry point (clique duplo, sem console visível).
- `start.ps1` — sobe serviços externos + `php artisan serve` + worker; espera as
  portas; abre o navegador; mostra tray icon com menu Abrir/Logs/Reiniciar/Sair.
- `stop.bat` — encerramento de emergência.
- `launcher.config.json` — define o que iniciar (cada serviço tem flag `enabled`).
- `logs/` — stdout/stderr de cada serviço.

Restrições do launcher: alvo é **Windows PowerShell 5.1** (o que vem por padrão no
Windows). Não use sintaxe de PowerShell 7+ (`?.`, `??`, ternário). Mata processos
com `taskkill /T` para pegar filhos (o `php artisan serve` spawna o servidor PHP
nativo como subprocesso).

## Como rodar e testar

Setup inicial:
```
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Rodar: ou `launcher\start.bat` (recomendado), ou `composer run dev`, ou os processos
separados. Acesso em `http://localhost:8000`. `/config`, `/health`, `/shorts`.

Após editar PHP, valide a sintaxe com `php -l <arquivo>`. Após editar `start.ps1`,
valide com o parser: `[System.Management.Automation.Language.Parser]::ParseFile(...)`.

## Convenções de código

- Comentários e textos de UI em **português (PT-BR)**.
- Mensagens de erro voltadas ao cliente final devem ser acionáveis ("Vá em CONFIG →
  YouTube e cole o Client ID"), não técnicas demais.
- Telas Blade seguem o visual existente: classes utilitárias `card`, `btn-primary`,
  `btn-outline`, `form-control`, `form-label`, `section-title` (definidas em
  `resources/css/app.css`); fonte display Space Grotesk.
- Ao adicionar item no menu, editar o array `$navItems` em `sidebar.blade.php`.
- Migrations novas seguem o padrão de data já usado (`2026_05_14_*`).

## Distribuição (roadmap)

O objetivo é vender o produto como licença única para dois públicos (estúdios com
GPU e criadores). Fases planejadas:

1. **Auto-configuração no app** — feito: `AppConfig`, tela `/config`, banner.
2. **Launcher** — feito: `launcher/` em PowerShell.
3. **Instalador** — pendente: Inno Setup com PHP/Python embarcados, modelos baixados
   no first-run.
4. **Sistema de licença** — pendente: chave RSA assinada, validação offline.

## Relação com o pipeline Python (NewVideoMaker)

Este projeto **não gera vídeo** — ele dispara o `pipeline.py` do projeto Python
separado (skill `new-videomaker`), que roda FLUX (ComfyUI :8188), ACE-Step (:7860),
Kokoro TTS, Whisper e Gemma via Ollama (:11434). Quando o trabalho for sobre os
scripts Python de geração (`gerar_roteiro.py`, `gerar_imagem.py`, etc.), é a outra
skill que se aplica. Esta aqui é só a camada web/orquestração em Laravel.
