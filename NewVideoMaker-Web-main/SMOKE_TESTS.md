# Smoke tests — NEW VideoMaker Web

Checklist manual para validar que tudo continua funcionando antes de fechar uma versão / gerar instalador para cliente. Cada teste tem o comando pra rodar e o que esperar.

> **Quando rodar:** depois de qualquer mudança grande (jobs, OAuth, build, migrations). Para mudanças pequenas, rodar pelo menos os blocos **1** e **2**.

---

## 1) Sintaxe e bootstrap (~ 30 s)

### 1.1 PHP — todos os arquivos do projeto compilam

```powershell
Get-ChildItem app, routes, config, database\migrations, bootstrap -Recurse -Filter *.php |
    ForEach-Object { php -l $_.FullName } |
    Select-String -NotMatch 'No syntax errors'
```

Esperado: **nenhuma linha de saída** (saída vazia = todos passam).

### 1.2 PowerShell — scripts do launcher e installer compilam

```powershell
$files = Get-ChildItem launcher, installer -Recurse -Filter *.ps1
foreach ($f in $files) {
    $errors = $null
    [System.Management.Automation.Language.Parser]::ParseFile($f.FullName, [ref]$null, [ref]$errors) | Out-Null
    if ($errors) { Write-Host "ERR $($f.Name)" -ForegroundColor Red; $errors }
    else { Write-Host "OK  $($f.Name)" -ForegroundColor Green }
}
```

Esperado: **OK** em build.ps1, postinstall.ps1, preuninstall.ps1, start.ps1.

### 1.3 Laravel bootstrap não dá erro

```powershell
php artisan --version
```

Esperado: `Laravel Framework 12.xx.x`.

---

## 2) Migrations e DB (~ 10 s)

### 2.1 Migrate roda do zero sem erro

```powershell
# CUIDADO: apaga o DB atual. Só faça em dev.
Remove-Item database\database.sqlite -ErrorAction SilentlyContinue
New-Item database\database.sqlite -ItemType File | Out-Null
php artisan migrate --force
```

Esperado: todas as migrations rodam, incluindo `videos`, `youtube_accounts`, `youtube_uploads`, `app_settings`, `setup_tasks`.

### 2.2 Tabelas presentes

```powershell
php artisan db:show
```

Esperado: 11+ tabelas listadas, incluindo nossas 5.

---

## 3) Wizard /setup (~ 1 min)

Sobe o servidor:

```powershell
php artisan serve --port=8000
```

### 3.1 Primeira execução redireciona para /setup

Com DB recém criado (sem `setup.completed`):

- Abrir `http://localhost:8000/` no navegador.
- Esperado: redireciona automaticamente para `http://localhost:8000/setup`.

### 3.2 Wizard mostra estado real do sistema

Na tela `/setup`:

- 5 cards: Ollama, ComfyUI, ACE-Step, Pipeline Python, YouTube.
- Cada um tem badge (Pronto / Pendente).
- Auto-refresh a cada 3,5 s (abre DevTools → Network → veja `/setup/status` pingando).

### 3.3 Salvar path de pipeline.py manualmente

- Card "Pipeline Python": cola um caminho existente e clica "Salvar".
- Esperado: badge muda para "Configurado" sem refresh manual (≤ 4 s).
- Cola um caminho que **não** existe: esperado validação rejeita.

### 3.4 Rotas de whitelist não fazem loop

```powershell
curl -s -o NUL -w "%{http_code}`n" http://localhost:8000/setup    # 200
curl -s -o NUL -w "%{http_code}`n" http://localhost:8000/config   # 200
curl -s -o NUL -w "%{http_code}`n" http://localhost:8000/health   # 200
curl -s -o NUL -w "%{http_code}`n" http://localhost:8000/         # 302
curl -s -o NUL -w "%{http_code}`n" http://localhost:8000/shorts   # 302
```

---

## 4) /config — fluxo de salvar credenciais (~ 1 min)

### 4.1 Salvar credenciais YouTube via UI

- Abrir `http://localhost:8000/config`.
- Preencher Client ID e Client Secret de teste (qualquer string).
- Clicar "Salvar credenciais".
- Esperado: mensagem verde "Credenciais salvas". Badge YouTube vira "Configurado".

### 4.2 Secret fica encriptado no DB

```powershell
php artisan tinker --execute='$v = App\Models\AppSetting::where("key","youtube.client_secret")->first(); echo "value (bruto): " . $v->getAttributes()["value"] . "\n"; echo "value (decifrado): " . $v->value . "\n";'
```

Esperado: o bruto começa com `eyJpdiI6` (base64 Laravel encrypted), e o decifrado mostra o secret original.

### 4.3 Limpar setup.completed e ver banner

```powershell
php artisan tinker --execute='App\Models\AppSetting::where("key","setup.completed")->delete(); app(App\Services\AppConfig::class)->flush();'
```

- Abrir `http://localhost:8000/dashboard`.
- Esperado: banner vermelho "Configuração incompleta — falta: …" aparece no topo.
- Em `/setup` e `/config` o banner **não** aparece (são telas onde a configuração é feita).

---

## 5) YouTube Shorts (~ 30 s — só sintaxe sem credencial real)

Sem credenciais Google reais não dá pra rodar OAuth ponta a ponta, mas:

### 5.1 Listar Shorts mesmo sem canal conectado

- Acessar `http://localhost:8000/shorts`.
- Esperado: tela com "Nenhum canal do YouTube conectado" + botão "Conectar canal".

### 5.2 Clicar em "Conectar canal" sem credenciais configuradas

- Esperado: mensagem de erro "Credenciais do YouTube não configuradas. Vá em CONFIG → YouTube e cole o Client ID."

### 5.3 Job PublicarYoutubeShort fila correta

```powershell
php artisan tinker --execute='echo \\App\\Jobs\\PublicarYoutubeShort::class;'
```

Confere que a classe existe. Para teste ponta a ponta com OAuth real, ver SHORTS_E2E_CHECKLIST (não incluso).

---

## 5b) Autoconfigure de componentes (~ 1 min)

Valida o comando que o instalador offline roda no `postinstall` para configurar ComfyUI/ACE-Step/pipeline sem o cliente tocar em nada.

```powershell
# Cria componentes fake em uma pasta temporária
$fake = "$env:TEMP\nvm-comp-test"
Remove-Item $fake -Recurse -Force -ErrorAction SilentlyContinue
mkdir "$fake\ComfyUI\python_embeded","$fake\ACE-Step\.venv\Scripts","$fake\NewVideoMaker\.venv\Scripts" -Force | Out-Null
"x" | Set-Content "$fake\ComfyUI\main.py"
"x" | Set-Content "$fake\ComfyUI\python_embeded\python.exe"
"x" | Set-Content "$fake\ACE-Step\app.py"
"x" | Set-Content "$fake\ACE-Step\.venv\Scripts\python.exe"
"x" | Set-Content "$fake\NewVideoMaker\pipeline.py"
"x" | Set-Content "$fake\NewVideoMaker\.venv\Scripts\python.exe"

php artisan setup:autoconfigure --components-dir="$fake"
```

Esperado: o resumo lista ComfyUI / ACE-Step / Pipeline com os caminhos, e "Configurado: pipeline.py, Python do pipeline, pasta de saída, ComfyUI no launcher, ACE-Step no launcher".

Depois **reverta** (o teste suja o `launcher.config.json` e `app_settings`):

```powershell
git checkout launcher/launcher.config.json
php artisan tinker --execute='App\Models\AppSetting::whereIn("key",["videogen.pipeline_path","videogen.python_path","videogen.output_dir","setup.source","setup.completed"])->delete(); app(App\Services\AppConfig::class)->flush();'
Remove-Item "$env:TEMP\nvm-comp-test" -Recurse -Force
```

---

## 6) Launcher PowerShell (~ 2 min)

Em outro terminal:

```powershell
.\launcher\start.bat
```

Esperado:

- Janela de console não aparece (`/WindowStyle Hidden` no `.bat`).
- ~5 segundos depois, navegador abre em `http://localhost:8000`.
- Ícone "NEW VideoMaker" aparece na bandeja do Windows (perto do relógio).
- Clique direito no ícone: vê menu **Abrir interface / Pasta de logs / Reiniciar tudo / Sair**.
- "Pasta de logs" abre `launcher\logs\` no Explorer com arquivos `web.out.log`, `queue.out.log`, `launcher.log`.

Para parar:

- Menu "Sair" no tray. Ou se travou: `.\launcher\stop.bat`.

---

## 7) Build do instalador (~ 3-5 min na 1ª vez, ~ 30 s nas seguintes)

### 7.1 Build do staging (sem Inno)

```powershell
.\installer\build.ps1 -SkipInno
```

Esperado:

- Baixa PHP NTS x64 (~32 MB) na primeira vez. Próximas reutilizam o cache.
- Termina com: `Staging pronto em: …\installer\staging\`.
- `installer\staging\app\` tem ~100 MB (sem dev deps).
- `installer\staging\php\php.exe` existe.

### 7.2 PHP embarcado funciona com o app

```powershell
$php = "installer\staging\php\php.exe"
$app = "installer\staging\app"
& $php "$app\artisan" --version
& $php -m | Select-String 'curl|pdo_sqlite|mbstring|openssl|sodium' | Measure-Object | % Count
```

Esperado: `Laravel Framework 12.xx.x` + contagem de extensões >= 5.

### 7.3 Simular instalação no cliente (manual, opcional)

```powershell
$tmp = "$env:TEMP\nvm-install-test"
Remove-Item $tmp -Recurse -Force -ErrorAction SilentlyContinue
robocopy installer\staging\php "$tmp\php" /E /NFL /NDL /NJH /NJS /NP | Out-Null
robocopy installer\staging\app "$tmp\app" /E /NFL /NDL /NJH /NJS /NP | Out-Null
New-Item "$tmp\installer" -ItemType Directory | Out-Null
Copy-Item installer\postinstall.ps1 "$tmp\installer\"
& powershell -NoProfile -ExecutionPolicy Bypass -File "$tmp\installer\postinstall.ps1" -InstallDir $tmp
Get-Content "$tmp\app\storage\logs\postinstall.log"
```

Esperado no log: nenhuma linha "FALHA". Banco SQLite criado com tabelas. `bootstrap/cache/{config,packages,routes-v7}.php` gerados.

### 7.4 Build do .exe (com Inno instalado)

```powershell
.\installer\build.ps1 -AppVersion 0.1.0
```

Esperado: `installer\dist\NewVideoMaker-Setup-0.1.0.exe` com ~30-50 MB.

---

## 8) Pré-uninstall mata processos (manual)

Com o launcher rodando + queue worker + servidor:

```powershell
# Em outro terminal:
.\installer\preuninstall.ps1 -InstallDir (Get-Location).Path
```

Esperado: launcher e PHP encerram. Logs em `app\storage\logs\preuninstall.log`.

---

## Erros conhecidos e workarounds

| Sintoma | Causa | Fix |
|---|---|---|
| `Class "Laravel\Pail\PailServiceProvider" not found` no build | Faltou `composer install --no-dev` (só rodou dump-autoload) | já corrigido no build.ps1 |
| `include(...resources/views/components/line.php): Failed to open stream` | `/XD views` do robocopy excluiu pasta interna do framework | já corrigido: cleanup específico de storage/framework/{cache,sessions,views} |
| `pendencies()` retorna vazio mas paths não existem | fallback do `config/videogen.php` tinha defaults absolutos | já corrigido: defaults vazios; `pendencies()` valida `is_file()`/`is_dir()` |
| `Token '\php.exe' inesperado` no PowerShell 5.1 | em-dashes (`—`) em arquivo sem BOM | substituir por `--` ASCII ou salvar com BOM UTF-8 |
| Job preso em `pending` | worker não escuta a fila | rodar `queue:work --queue=video-generation,youtube,default` |
| `Remove-Item -Recurse` falha em vendor/phpunit | path > 260 chars no Windows | usar prefixo `\\?\` (`Remove-PathLong` no build.ps1) |
