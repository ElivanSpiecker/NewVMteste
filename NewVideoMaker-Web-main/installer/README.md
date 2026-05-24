# Instalador NEW VideoMaker

Gera um `.exe` que instala o app no PC do cliente — sem que ele precise instalar PHP, Composer, Node ou rodar comandos. Empacota:

- **PHP 8.3 NTS x64 embarcado** (baixado uma vez do `windows.php.net`, fica em cache).
- **Projeto Laravel** com `vendor/` e `public/build/` já incluídos.
- **Launcher PowerShell** (`launcher/start.bat` → tray icon).
- **Script de pós-instalação** que cria `.env`, gera `APP_KEY`, roda `migrate` e auto-configura componentes.
- **Script de pré-desinstalação** que mata os processos antes de remover arquivos.

## Dois modos de build

O `build.ps1` decide o modo automaticamente conforme a pasta `installer/payload/`:

| Modo | Quando | Tamanho | O que o cliente ainda faz |
|---|---|---|---|
| **Só app web** | `payload/` vazio | ~30-50 MB | Instala ComfyUI/ACE-Step e configura no `/setup` |
| **Completo (offline)** | `payload/` preenchido | 15-35 GB (disk spanning) | **Nada** — abre e usa |

No **modo completo**, você monta os componentes (ComfyUI + ACE-Step + projeto NewVideoMaker + Ollama) **uma vez** dentro de `installer/payload/` — veja [payload/README.md](payload/README.md). O instalador empacota tudo; no PC do cliente o `postinstall.ps1` roda `php artisan setup:autoconfigure` que detecta e configura cada componente sem o cliente tocar em nada.

## Pré-requisitos do build

Na máquina que vai gerar o instalador. **Todos obrigatórios** se o projeto veio de um `git clone` — porque `vendor/`, `node_modules/` e `public/build/` estão no `.gitignore` e precisam ser reconstruídos:

- **Windows 10+** com PowerShell 5.1.
- **PHP 8.2+** — necessário para o Composer funcionar.
- **Composer** — https://getcomposer.org/download/ — reconstrói o `vendor/`.
- **Node.js 22+** — https://nodejs.org/ — reconstrói o `public/build/` (CSS/JS).
- **Inno Setup 6** — https://jrsoftware.org/isinfo.php — gera o `.exe`. Sem ele, o `build.ps1` ainda prepara o `staging/` mas não compila o instalador.

> O `build.ps1` roda `composer install` e `npm run build` sozinho — você não digita esses comandos — mas as ferramentas precisam estar instaladas. Se faltar Composer ou Node e os artefatos não existirem, o build **aborta com mensagem clara** (não gera um `.exe` quebrado).

### Quem clona o repo para buildar (ex: um colega)

```powershell
git clone <repo> ; cd NewVideoMaker-Web-main
# instalar uma vez: PHP, Composer, Node, Inno Setup
# montar installer\payload\ conforme installer\payload\README.md  (modo completo)
.\installer\build.ps1
```

Não precisa rodar `composer install` / `npm install` na mão — o `build.ps1` cuida disso.

## Como buildar

```powershell
cd C:\caminho\NewVideoMaker-Web-main
.\installer\build.ps1
```

Saída em `installer\dist\NewVideoMaker-Setup-<versão>.exe`.

Flags úteis:

```powershell
.\installer\build.ps1 -AppVersion 0.2.0      # define versão exibida
.\installer\build.ps1 -SkipPhp               # reaproveita PHP em staging (build rápido)
.\installer\build.ps1 -SkipInno              # só prepara staging, não compila
.\installer\build.ps1 -PhpVersion 8.3.10     # PHP versão específica
```

## Como o cliente instala

1. Recebe `NewVideoMaker-Setup-<versão>.exe` (≈ 80–120 MB).
2. Dois cliques. Wizard tradicional Inno Setup (PT-BR por padrão).
3. Escolhe pasta (default `%LocalAppData%\Programs\NewVideoMaker`) — não pede admin.
4. Inno copia arquivos, roda o `postinstall.ps1` (cria `.env`, `APP_KEY`, `migrate`).
5. Marca "Iniciar NEW VideoMaker agora" — o launcher sobe e o navegador abre em `http://localhost:8000`.

## Estrutura instalada no PC do cliente

```
%LocalAppData%\Programs\NewVideoMaker\        (ou Program Files se rodou como admin)
├── php\                                       (PHP embarcado)
│   ├── php.exe
│   ├── php.ini                                (nosso, com extensoes ja ativadas)
│   └── ext\
├── app\                                       (projeto Laravel)
│   ├── artisan
│   ├── vendor\
│   ├── public\build\
│   ├── launcher\
│   │   ├── start.bat
│   │   ├── start.ps1
│   │   └── launcher.config.json               (ajustado pelo postinstall)
│   ├── database\database.sqlite               (criado no postinstall)
│   ├── .env                                   (criado no postinstall)
│   └── storage\
├── components\                                (SÓ no modo completo)
│   ├── ComfyUI\                                (com modelos FLUX)
│   ├── ACE-Step\                               (com venv montado)
│   ├── NewVideoMaker\                          (pipeline.py + scripts + venv)
│   └── ollama\OllamaSetup.exe
└── installer\
    ├── postinstall.ps1
    └── preuninstall.ps1
```

No modo completo, o `postinstall.ps1` instala o Ollama silenciosamente e roda
`php artisan setup:autoconfigure` — que detecta os componentes em `components\`,
grava os caminhos do pipeline no banco e reescreve `launcher.config.json` para
ligar ComfyUI e ACE-Step. O cliente abre o app já 100% configurado.

## Estrutura do build (no SEU PC)

```
installer\
├── assets\
│   └── php.ini                                (php.ini base que vai pro cliente)
├── cache\                                     (.gitignored — PHP zip baixado fica aqui)
├── staging\                                   (.gitignored — montado pelo build.ps1)
│   ├── php\
│   └── app\
├── dist\                                      (.gitignored — saida do Inno)
│   └── NewVideoMaker-Setup-*.exe
├── build.ps1                                  (script que orquestra tudo)
├── setup.iss                                  (script Inno Setup)
├── postinstall.ps1                            (roda no PC do cliente apos copy)
├── preuninstall.ps1                           (roda no PC do cliente antes de remover)
└── README.md                                  (este arquivo)
```

## O que o cliente faz manualmente após instalar

**Modo completo (com `payload/` preenchido):** praticamente nada. O `postinstall` configura pipeline, ComfyUI, ACE-Step e Ollama automaticamente. O único passo opcional é, para publicar Shorts, colar as credenciais OAuth do YouTube em `/config → YouTube` (10 min).

**Modo só app web (`payload/` vazio):** o cliente ainda precisa instalar ComfyUI e ACE-Step, e apontar os caminhos no wizard `/setup` ou em `/config`.

## Atualizando uma instalação existente

Inno reconhece a instalação anterior pelo `AppId` (GUID fixo em `setup.iss`). Quando o cliente roda um instalador novo:

1. Pede pra fechar o app rodando (`AppMutex`).
2. Sobrescreve arquivos do app (mas preserva `.env`, `database.sqlite`, `storage/`).
3. `postinstall.ps1` roda de novo — é idempotente, só roda `migrate` (que adiciona migrations novas sem perder dados).

## Limitações conhecidas

- **GPU/CUDA**: o instalador não verifica se o PC tem GPU compatível. Cliente sem GPU instala e descobre depois que o pipeline trava. Aviso explícito no README do produto / na landing page.
- **Antivírus**: instaladores Inno não assinados às vezes ativam SmartScreen ("Não executado — origem desconhecida"). Cliente clica "Mais informações → Executar mesmo assim". Para versão comercial: comprar certificado de code signing (≈ US$ 200/ano).
- **PHP NTS vs Apache**: o pacote usa NTS porque rodamos via `php artisan serve`. Se um dia for distribuir com IIS/Apache, trocar pelo TS no `build.ps1`.
- **Tamanho**: ≈ 25 MB de PHP + ≈ 60 MB do `vendor/` + ≈ 5 MB do `public/build/` = ~90 MB descompactado. Comprimido (LZMA2/ultra) ≈ 30 MB.
