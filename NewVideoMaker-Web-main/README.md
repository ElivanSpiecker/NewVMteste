# New VideoMaker — Web

Backend Laravel + API REST que orquestra o pipeline Python local de geração de vídeos (FLUX, ACE-Step, Kokoro, Whisper, Gemma 4 via Ollama, MoviePy).

Parte do TCC de Elivan e Nícolas (UNIVATES).

## Stack

- **Laravel 12** (PHP 8.2) com fila `database`
- **Inertia.js + Vue 3 + Tailwind** (interface web)
- **API REST** em `/api/*` com CORS configurado para frontend externo (ex: Lovable)
- **Job único** que dispara `pipeline.py` via `Symfony\Process` e monitora progresso

## Como subir

Pré-requisitos: PHP 8.2+, Composer, Node 22+, e os 3 servidores Python (ComfyUI :8188, ACE-Step :7860, Ollama :11434).

Setup inicial (uma vez):

```powershell
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

### Modo recomendado: launcher (1 clique, tray icon)

Para uso real — incluindo entrega ao cliente final — use o launcher PowerShell em `launcher/`:

```
launcher\start.bat       # sobe tudo + tray icon + abre browser
launcher\stop.bat        # forca o encerramento se algo travar
```

Configura quais serviços iniciar em `launcher/launcher.config.json`. Detalhes completos em [launcher/README.md](launcher/README.md).

### Modo dev: terminais separados

Útil quando se está debugando alguma parte específica:

```powershell
# 1. Servidor web
php artisan serve

# 2. Worker da fila (vídeos + uploads do YouTube)
php artisan queue:work --queue=video-generation,youtube,default --timeout=1500

# 3. Vite (só em dev — assets já buildados não precisam dele)
npm run dev
```

Ou tudo numa linha só com `composer run dev`.

### Acesso

- Web: `http://localhost:8000`
- API: `http://localhost:8000/api`
- **Wizard de primeira execução**: `http://localhost:8000/setup` (redirecionamento automático na primeira vez)
- Configurações (caminhos do pipeline + credenciais YouTube): `http://localhost:8000/config`
- Health check: `http://localhost:8000/health`

### Distribuir para cliente final (instalador .exe)

Para empacotar tudo num único `.exe` que instala PHP + app + launcher de uma vez:

```powershell
.\installer\build.ps1
```

Saída em `installer\dist\NewVideoMaker-Setup-*.exe`. Instruções completas, requisitos
(Inno Setup 6) e o que o cliente ainda precisa instalar separadamente (Ollama,
ComfyUI, ACE-Step) em [installer/README.md](installer/README.md).

## API REST

| Método | Endpoint | Descrição |
|---|---|---|
| `GET`    | `/api/health` | Status dos 3 serviços Python |
| `GET`    | `/api/videos` | Lista todos os vídeos |
| `POST`   | `/api/videos` | Cria + dispara job (`{tema, duracao}`) → 201 |
| `GET`    | `/api/videos/{id}` | Status + progresso (use para polling) |
| `DELETE` | `/api/videos/{id}` | Remove registro + arquivos |
| `GET`    | `/api/videos/{id}/download` | Baixa MP4 |
| `GET`    | `/api/videos/{id}/subtitles` | Baixa SRT |

## Configuração do pipeline

A partir do primeiro start, configure tudo pela interface em `http://localhost:8000/config`:

- **Pipeline Python**: caminho do Python do venv, do `pipeline.py` e da pasta de saída.
- **Credenciais YouTube**: Client ID e Secret do Google Cloud Console (passo a passo na própria tela).

Os valores ficam salvos no banco (`app_settings`, com tokens encriptados) e têm fallback automático para `.env` se ainda não foram salvos via UI. Cliente final nunca precisa editar `.env`.

### Variáveis legadas em `.env` (opcional, fallback)

```
VIDEOGEN_PYTHON=C:\Users\...\NewVideoMaker\.venv\Scripts\python.exe
VIDEOGEN_PIPELINE=C:\Users\...\NewVideoMaker\pipeline.py
VIDEOGEN_OUTPUT_DIR=C:\Users\...\NewVideoMaker\output
YOUTUBE_CLIENT_ID=...
YOUTUBE_CLIENT_SECRET=...
YOUTUBE_REDIRECT_URI=http://localhost:8000/shorts/youtube/callback
```

## Licença

Todos direitos reservados — projeto comercial em desenvolvimento.
