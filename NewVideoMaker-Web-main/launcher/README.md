# NEW VideoMaker — launcher

Launcher PowerShell que sobe **tudo de uma vez** com um clique e mostra um ícone na bandeja do Windows. Substitui o ritual de abrir 3-4 terminais (`php artisan serve`, `queue:work`, ComfyUI, ACE-Step, Ollama).

## Uso (cliente final)

1. Dois cliques em `start.bat`.
2. Espere 5-10 segundos. O navegador abre automaticamente em `http://localhost:8000`.
3. Aparece o ícone "NEW VideoMaker" na bandeja do Windows (perto do relógio).

Menu da bandeja (clique direito):
- **Abrir interface** — reabre o navegador na URL do app.
- **Pasta de logs** — abre a pasta `launcher/logs/` no Explorer.
- **Reiniciar tudo** — derruba todos os processos e sobe de novo.
- **Sair** — encerra o app e todos os subprocessos.

Se algo travar e o tray sumir, dá clique duplo em `stop.bat` para limpar tudo.

## Logs

Cada serviço escreve em `launcher/logs/<nome>.out.log` (stdout) e `<nome>.err.log` (stderr). O próprio launcher loga em `launcher.log`.

Quando algo der errado, o primeiro lugar para olhar:
- `web.err.log` — falhas do `php artisan serve`
- `queue.err.log` — falhas do worker da fila / job
- `ollama.err.log`, `comfyui.err.log`, `acestep.err.log` — falhas dos serviços de IA

## Configuração

Edite `launcher.config.json`. Por padrão:

- **Ollama** vem habilitado e assume `ollama` no PATH.
- **ComfyUI** e **ACE-Step** vêm desabilitados (`"enabled": false`) — habilite e ajuste `executable`/`workdir` para os caminhos da sua instalação.

Cada serviço tem:

| Campo | Significado |
|---|---|
| `enabled` | Se `false`, o launcher pula esse serviço (assume que outra pessoa cuidará dele). |
| `executable` | Caminho do binário (Python do venv, `ollama`, etc.). |
| `args` | Argumentos passados ao executável. |
| `workdir` | Pasta de trabalho. Vazio = usa a do launcher. |
| `wait_port` | Porta TCP para checar se já está rodando antes de subir (evita iniciar 2x). |
| `ready_timeout_seconds` | Quanto esperar a porta abrir antes de desistir (informativo, não bloqueia). |

Exemplo, ComfyUI rodando do `.venv`:

```json
{
    "name": "comfyui",
    "enabled": true,
    "executable": "C:/PycharmProjects/ComfyUI/.venv/Scripts/python.exe",
    "args": ["main.py", "--lowvram"],
    "workdir": "C:/PycharmProjects/ComfyUI",
    "wait_port": 8188
}
```

## Atalho na área de trabalho (opcional)

Para o cliente não precisar abrir a pasta do projeto:

1. Clique direito em `start.bat` → **Enviar para → Área de trabalho (criar atalho)**.
2. Renomeie o atalho para "NEW VideoMaker".
3. Clique direito no atalho → **Propriedades → Alterar ícone…** e escolha um.

## Pré-requisitos

- **Windows 10 ou superior** (PowerShell 5.1+, já vem por padrão).
- **PHP 8.2+** no PATH OU caminho absoluto definido em `php.executable`.
- O projeto Laravel já configurado: `composer install` e `php artisan migrate` rodados pelo menos uma vez.
- Para gerar vídeos, os serviços externos precisam existir nas pastas configuradas (ou o launcher só não inicia eles).

## Limitações conhecidas

- Não roda como Windows Service (não inicia no boot). Para isso, use NSSM ou Task Scheduler — fica para a Fase 3 (instalador).
- Tray icon usa ícone genérico do Windows. Para personalizar, troque a linha `$tray.Icon = ...` por um arquivo `.ico` próprio.
- Se o cliente abrir o `start.bat` duas vezes, vai subir 2 instâncias do tray (mas só uma do servidor — porta ocupada). Para evitar, a Fase 3 vai checar mutex.
