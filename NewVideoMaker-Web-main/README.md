# New VideoMaker — Web

Backend Laravel + API REST que orquestra o pipeline Python local de geração de vídeos (FLUX, ACE-Step, Kokoro, Whisper, Gemma 4 via Ollama, MoviePy).

Parte do TCC de Elivan e Nícolas (UNIVATES).

## Stack

- **Laravel 12** (PHP 8.2) com fila `database`
- **Inertia.js + Vue 3 + Tailwind** (interface web)
- **API REST** em `/api/*` com CORS configurado para frontend externo (ex: Lovable)
- **Job único** que dispara `pipeline.py` via `Symfony\Process` e monitora progresso

## Como subir

Pré-requisitos: PHP 8.2+, Composer, Node 22+, e os 3 servidores Python rodando (ComfyUI :8188, ACE-Step :7860, Ollama :11434).

```powershell
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Depois precisa de 3 terminais:

```powershell
# 1. Servidor web
php artisan serve

# 2. Worker da fila (1 vídeo por vez — VRAM é gargalo)
php artisan queue:work --queue=video-generation --timeout=1200

# 3. Vite (só em dev)
npm run dev
```

Acesso:
- Web: `http://localhost:8000`
- API: `http://localhost:8000/api`
- Health check: `http://localhost:8000/health`

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

`.env`:

```
VIDEOGEN_PYTHON=C:\Users\...\NewVideoMaker\.venv\Scripts\python.exe
VIDEOGEN_PIPELINE=C:\Users\...\NewVideoMaker\pipeline.py
VIDEOGEN_OUTPUT_DIR=C:\Users\...\NewVideoMaker\output
```

## Licença

Todos direitos reservados — projeto comercial em desenvolvimento.
