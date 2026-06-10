# payload/ — componentes pré-montados do instalador offline

Esta pasta é onde **você (desenvolvedor)** coloca, **uma única vez**, os componentes pesados que serão empacotados dentro do instalador `.exe`. O cliente final recebe tudo pronto — não baixa nem instala nada do pipeline.

> O `build.ps1` copia o conteúdo daqui para `staging/components/`, e o Inno Setup empacota tudo no `.exe`. No PC do cliente isso vira `{InstalaçãoDir}\components\`, e o `postinstall.ps1` roda `php artisan setup:autoconfigure` que detecta e configura tudo automaticamente.

**Importante:** estas pastas são `.gitignore`d (exceto este README) — os componentes são grandes demais para versionar.

---

## O que colocar em cada pasta

### `ComfyUI/`

A instalação do ComfyUI **já funcionando**, com os modelos FLUX dentro.

Estrutura esperada:

```
payload/ComfyUI/
├── main.py                       <- OBRIGATÓRIO (é como o autoconfigure detecta)
├── python_embeded/python.exe     <- Python embarcado (ComfyUI portable) ...
│   ou .venv/Scripts/python.exe   <- ... ou venv, qualquer um dos dois
├── models/
│   ├── unet/  (ou checkpoints/)  <- modelo FLUX.1 Schnell
│   ├── clip/
│   └── vae/
└── (resto do ComfyUI)
```

**Como montar:** baixe o *ComfyUI Windows Portable* (https://github.com/comfyanonymous/ComfyUI/releases — arquivo `ComfyUI_windows_portable_nvidia.7z`), extraia, e copie:
- a pasta interna `ComfyUI/` inteira para `payload/ComfyUI/`
- a pasta `python_embeded/` para dentro de `payload/ComfyUI/python_embeded/`

Baixe os modelos FLUX e coloque em `payload/ComfyUI/models/` conforme a doc do ComfyUI.

Teste antes de empacotar: `python_embeded\python.exe -s main.py` deve subir em `http://127.0.0.1:8188`.

### `ACE-Step/`

A instalação do ACE-Step já com o venv montado e dependências instaladas.

Estrutura esperada:

```
payload/ACE-Step/
├── app.py                        <- OBRIGATÓRIO (ou gradio_app.py)
├── .venv/Scripts/python.exe      <- venv com PyTorch+CUDA já instalado
└── (resto do ACE-Step)
```

**Como montar:** clone https://github.com/ace-step/ACE-Step, crie o venv, instale as dependências (`pip install -e .` + PyTorch com CUDA), baixe os modelos. Teste que `.venv\Scripts\python.exe app.py` sobe em `http://127.0.0.1:7860`.

> Se o comando de iniciar o ACE-Step não for `python app.py`, ajuste os `args` em `launcher/launcher.config.json` depois — o `setup:autoconfigure` assume `app.py` por padrão.

### `NewVideoMaker/`

O projeto Python **NewVideoMaker** (o motor — `pipeline.py` + scripts de geração).

Estrutura esperada:

```
payload/NewVideoMaker/
├── pipeline.py                   <- OBRIGATÓRIO
├── .venv/Scripts/python.exe      <- venv do projeto
├── gerar_roteiro.py, gerar_imagem.py, ... (scripts)
└── output/                       <- pasta de saída (criada se não existir)
```

O `setup:autoconfigure` vai gravar automaticamente em `app_settings`:
- `videogen.pipeline_path` → `components\NewVideoMaker\pipeline.py`
- `videogen.python_path`   → `components\NewVideoMaker\.venv\Scripts\python.exe`
- `videogen.output_dir`    → `components\NewVideoMaker\output`

### `ollama/` (opcional)

Coloque o `OllamaSetup.exe` aqui se quiser que o instalador instale o Ollama offline (sem internet). Se a pasta ficar vazia, o wizard `/setup` baixa o Ollama da internet quando o cliente clicar.

```
payload/ollama/OllamaSetup.exe
```

Baixe de https://ollama.com/download/OllamaSetup.exe. Lembre de também incluir o modelo Gemma — rode `ollama pull gemma2:2b` na máquina de montagem e o blob fica em `%USERPROFILE%\.ollama\models` (copiar isso é avançado; mais simples deixar o cliente puxar pelo wizard).

---

## Build com vs sem payload

- **Com payload preenchido** → `build.ps1` gera o instalador completo (~vários GB, offline). É o que entrega "zero pré-requisito".
- **Payload vazio** → `build.ps1` gera só o instalador da app web (~40 MB). O cliente terá que configurar os componentes manualmente em `/setup`.

O `build.ps1` detecta automaticamente e avisa qual modo está usando.

## Tamanho e tempo

Com tudo dentro, o instalador final fica entre **15 e 35 GB** dependendo dos modelos. O Inno Setup divide em arquivos `.bin` de 2 GB (disk spanning) — você distribui a pasta `installer/dist/` inteira (pendrive, HD externo). Comprimir esse volume leva tempo; o `build.ps1` usa `nocompression` nos componentes (modelos já são binários comprimidos) para acelerar.
