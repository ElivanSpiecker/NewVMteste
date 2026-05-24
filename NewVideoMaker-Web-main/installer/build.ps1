# Build do instalador NEW VideoMaker.
#
# Prepara installer\staging\ contendo o PHP embarcado + o app Laravel pronto
# para producao, ajusta a config do launcher e chama o Inno Setup compiler
# (se disponivel) para gerar o .exe final em installer\dist\.
#
# Idempotente: pode rodar varias vezes; o PHP fica em cache e nao baixa de novo.
#
# Uso:
#   .\installer\build.ps1                  # build padrao
#   .\installer\build.ps1 -SkipPhp         # nao re-extrai o PHP
#   .\installer\build.ps1 -SkipInno        # so prepara staging, nao compila
#   .\installer\build.ps1 -PhpVersion 8.3.14

[CmdletBinding()]
param(
    [string]$PhpVersion  = '8.3.14',
    [string]$AppVersion  = '0.1.0',
    [switch]$SkipPhp,
    [switch]$SkipInno
)

$ErrorActionPreference = 'Stop'

$InstallerDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectDir   = Split-Path -Parent $InstallerDir
$CacheDir     = Join-Path $InstallerDir 'cache'
$StagingDir   = Join-Path $InstallerDir 'staging'
$StagingPhp   = Join-Path $StagingDir 'php'
$StagingApp   = Join-Path $StagingDir 'app'
$DistDir      = Join-Path $InstallerDir 'dist'
$AssetsDir    = Join-Path $InstallerDir 'assets'

foreach ($d in @($CacheDir, $StagingDir, $DistDir)) {
    if (-not (Test-Path -LiteralPath $d)) { New-Item -ItemType Directory -Path $d | Out-Null }
}

# Helper: remove pasta inteira mesmo se contem paths > 260 chars (clube phpunit/...).
# Usa robocopy /MIR contra uma pasta vazia temporaria -- truque classico do Windows.
function Remove-PathLong {
    param([string]$Path)
    if (-not (Test-Path -LiteralPath $Path)) { return }
    $empty = Join-Path $env:TEMP ("nvm-empty-" + [guid]::NewGuid().ToString('N'))
    New-Item -ItemType Directory -Path $empty | Out-Null
    try {
        & robocopy $empty $Path /MIR /NFL /NDL /NJH /NJS /NP /R:1 /W:1 | Out-Null
    } finally {
        Remove-Item -LiteralPath $empty -Force -ErrorAction SilentlyContinue
    }
    # Agora a pasta esta vazia; remove com cmd que aceita prefixo \\?\
    $long = "\\?\" + ($Path -replace '/', '\')
    & cmd /c "rmdir /s /q `"$long`"" 2>$null | Out-Null
}

Write-Host ""
Write-Host "==> NEW VideoMaker -- build do instalador" -ForegroundColor Cyan
Write-Host "    PHP $PhpVersion · App $AppVersion"
Write-Host "    Projeto: $ProjectDir"
Write-Host ""

# ---------- 1) PHP embarcado ----------

$PhpZipName = "php-$PhpVersion-nts-Win32-vs16-x64.zip"
$PhpZipPath = Join-Path $CacheDir $PhpZipName
$PhpUrl     = "https://windows.php.net/downloads/releases/$PhpZipName"
$PhpArchive = "https://windows.php.net/downloads/releases/archives/$PhpZipName"

if (-not $SkipPhp) {
    if (-not (Test-Path -LiteralPath $PhpZipPath)) {
        Write-Host "[php] baixando $PhpZipName..." -ForegroundColor Yellow
        try {
            Invoke-WebRequest -Uri $PhpUrl -OutFile $PhpZipPath -UseBasicParsing
        } catch {
            Write-Host "[php] tentando archive..." -ForegroundColor Yellow
            Invoke-WebRequest -Uri $PhpArchive -OutFile $PhpZipPath -UseBasicParsing
        }
    } else {
        Write-Host "[php] usando cache: $PhpZipPath"
    }

    if (Test-Path -LiteralPath $StagingPhp) { Remove-PathLong $StagingPhp }
    New-Item -ItemType Directory -Path $StagingPhp | Out-Null

    Write-Host "[php] extraindo..."
    Expand-Archive -LiteralPath $PhpZipPath -DestinationPath $StagingPhp -Force

    # Copia nosso php.ini sobre o que veio no zip
    Copy-Item -LiteralPath (Join-Path $AssetsDir 'php.ini') -Destination (Join-Path $StagingPhp 'php.ini') -Force
    Write-Host "[php] OK ($StagingPhp\php.exe)"
} else {
    Write-Host "[php] pulado (-SkipPhp)"
}

# ---------- 2) App Laravel ----------

if (Test-Path -LiteralPath $StagingApp) { Remove-PathLong $StagingApp }
New-Item -ItemType Directory -Path $StagingApp | Out-Null

# Padroes de exclusao na copia do projeto.
# - ExcludeDirs sao varridos com /XD do robocopy (qualquer ocorrencia em qualquer profundidade)
# - ExcludeFiles sao varridos com /XF (idem)
# Nota: '.claude\worktrees' fica como nome simples para o /XD pegar em qualquer profundidade,
# pois robocopy /XD aceita tanto path absoluto quanto basename. .claude\skills/ e mantido propositalmente.
$ExcludeDirs = @(
    'node_modules', '.git', '.idea', '.vscode', '.cache',
    'installer', 'tests',
    'worktrees',                         # .claude\worktrees (lixo de sessoes Claude)
    'logs',                              # storage\logs
    'youtube-uploads', 'uploads'         # storage\app\{uploads,youtube-uploads}
    # NAO incluir 'cache', 'sessions', 'views' aqui -- /XD pega QUALQUER pasta com
    # esse basename em qualquer profundidade, e isso quebra:
    #   - bootstrap\cache (obrigatorio do Laravel)
    #   - vendor\laravel\framework\src\Illuminate\Console\resources\views (templates de output)
    #   - varias pastas /resources/views internas de pacotes
    # O cleanup de storage\framework\{cache,sessions,views} acontece logo abaixo,
    # apos a copia, usando paths absolutos.
)
$ExcludeFiles = @(
    '.env', '.env.backup', '.env.production',
    'database.sqlite', 'database.sqlite-journal',
    'settings.local.json',               # .claude\settings.local.json
    '.phpunit.result.cache'
)

Write-Host "[app] copiando projeto -> staging\app\"

# Usa robocopy: muito mais rapido que Copy-Item -Recurse no Windows.
# /XD e /XF aceitam basename, entao pegam ocorrencias em qualquer profundidade.
$xdArgs = @(); foreach ($d in $ExcludeDirs)  { $xdArgs += @('/XD', $d) }
$xfArgs = @(); foreach ($f in $ExcludeFiles) { $xfArgs += @('/XF', $f) }

$rcLog = Join-Path $InstallerDir 'cache\robocopy.log'
& robocopy $ProjectDir $StagingApp /E /NFL /NDL /NJH /NJS /NP /R:1 /W:1 @xdArgs @xfArgs /LOG:$rcLog | Out-Null

# robocopy retorna 0-7 em sucessos parciais; >=8 e erro real
if ($LASTEXITCODE -ge 8) {
    Write-Host "[app] robocopy falhou (codigo $LASTEXITCODE). Veja $rcLog" -ForegroundColor Red
    exit 1
}

# Limpa caches/sessions/views compilados que vieram do projeto de dev.
# Cada um vira pasta vazia (Laravel exige existirem).
foreach ($sub in @('framework\cache','framework\sessions','framework\views','logs')) {
    $p = Join-Path $StagingApp "storage\$sub"
    if (Test-Path -LiteralPath $p) {
        Get-ChildItem -LiteralPath $p -Recurse -Force -ErrorAction SilentlyContinue |
            Sort-Object -Property FullName -Descending |
            Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
    } else {
        New-Item -ItemType Directory -Path $p -Force | Out-Null
    }
}

# Garante subpastas vazias que o Laravel exige existir
foreach ($sub in @('framework\cache\data','framework\sessions','framework\views','app\public','app\uploads','app\youtube-uploads','logs')) {
    $p = Join-Path $StagingApp "storage\$sub"
    if (-not (Test-Path -LiteralPath $p)) { New-Item -ItemType Directory -Path $p -Force | Out-Null }
}

# Garante bootstrap\cache (Laravel exige existir e ser gravavel; e onde config:cache grava)
$bootCache = Join-Path $StagingApp 'bootstrap\cache'
if (-not (Test-Path -LiteralPath $bootCache)) {
    New-Item -ItemType Directory -Path $bootCache -Force | Out-Null
} else {
    # Limpa qualquer cache de providers/services/etc que veio do projeto de dev
    # (pode listar dev deps como laravel/pail que nao estarao no autoload final)
    Get-ChildItem -LiteralPath $bootCache -Filter '*.php' -ErrorAction SilentlyContinue |
        Remove-Item -Force -ErrorAction SilentlyContinue
}

# Garante database/ existente (sem o .sqlite -- sera criado no postinstall)
$dbDir = Join-Path $StagingApp 'database'
if (-not (Test-Path -LiteralPath $dbDir)) { New-Item -ItemType Directory -Path $dbDir | Out-Null }

# Helper: roda um native exe com saida em arquivo de log (evita o NativeCommandError do PS 5.1
# que ocorre quando '2>&1' enrola stderr em ErrorRecord e o $ErrorActionPreference=Stop aborta).
function Invoke-Native {
    param(
        [string]$FilePath,
        [string[]]$Arguments,
        [string]$WorkingDirectory,
        [string]$LogFile
    )
    $proc = Start-Process -FilePath $FilePath -ArgumentList $Arguments `
        -WorkingDirectory $WorkingDirectory -NoNewWindow -Wait -PassThru `
        -RedirectStandardOutput $LogFile -RedirectStandardError "$LogFile.err"
    return $proc.ExitCode
}

# Composer install --no-dev (REMOVE pacotes dev do vendor/ e regenera autoload).
# Por que install em vez de dump-autoload:
#   dump-autoload so atualiza o autoload, mas vendor/laravel/pail (dev) continua em disco.
#   Quando o Laravel boot e nao acha bootstrap/cache/packages.php, chama package:discover
#   que le vendor/composer/installed.json -- esse JSON tem o Pail listado e provider
#   discovery tenta carrega-lo. Como dump-autoload --no-dev tirou a classe do autoload,
#   o autoload da fatal. Solucao: install --no-dev de fato remove os arquivos.
#
# --no-scripts evita o post-install rodar 'artisan package:discover' no build (que falharia
# tambem porque .env nao existe ainda); o postinstall.ps1 do Inno roda artisan no PC do
# cliente quando tudo ja esta no lugar.
$composer = Get-Command composer -ErrorAction SilentlyContinue
if ($composer) {
    $composerLog = Join-Path $InstallerDir 'cache\composer.log'
    Write-Host "[app] composer install --no-dev --optimize --no-scripts (log: $composerLog)"
    $exit = Invoke-Native -FilePath 'composer.bat' `
        -Arguments @('install','--no-dev','--optimize-autoloader','--no-scripts','--no-interaction','--no-progress') `
        -WorkingDirectory $StagingApp -LogFile $composerLog
    if ($exit -ne 0) {
        Write-Host "[app] composer falhou (exit $exit). Veja $composerLog e $composerLog.err" -ForegroundColor Red
        exit 1
    }
} else {
    # Sem Composer: so da pra continuar se o vendor/ ja veio junto (copiado do projeto).
    # vendor/ normalmente esta no .gitignore, entao apos um 'git clone' ele NAO existe.
    if (-not (Test-Path -LiteralPath (Join-Path $StagingApp 'vendor\autoload.php'))) {
        Write-Host ""
        Write-Host "[app] ERRO: Composer nao esta instalado E a pasta vendor/ nao existe." -ForegroundColor Red
        Write-Host "       O instalador ficaria sem as dependencias PHP (app nao abriria)." -ForegroundColor Red
        Write-Host "       Instale o Composer: https://getcomposer.org/download/" -ForegroundColor Yellow
        Write-Host "       (o Composer tambem exige PHP 8.2+ instalado)" -ForegroundColor Yellow
        exit 1
    }
    Write-Host "[app] composer ausente -- usando vendor/ ja existente" -ForegroundColor Yellow
}

# Garante assets buildados -- so roda npm build se a pasta nao existir
$buildDir = Join-Path $StagingApp 'public\build'
if (-not (Test-Path -LiteralPath $buildDir)) {
    $npm = Get-Command npm.cmd -ErrorAction SilentlyContinue
    if ($npm) {
        $npmLog = Join-Path $InstallerDir 'cache\npm.log'
        Write-Host "[app] npm install + npm run build (assets ausentes; log: $npmLog)"
        $exit = Invoke-Native -FilePath 'npm.cmd' `
            -Arguments @('install','--no-audit','--no-fund') `
            -WorkingDirectory $StagingApp -LogFile "$npmLog.install"
        if ($exit -eq 0) {
            $exit = Invoke-Native -FilePath 'npm.cmd' `
                -Arguments @('run','build') `
                -WorkingDirectory $StagingApp -LogFile "$npmLog.build"
        }
        if ($exit -ne 0) {
            Write-Host "[app] npm falhou (exit $exit). Veja $npmLog.*" -ForegroundColor Red
            exit 1
        }
    } else {
        # Sem Node e sem assets compilados: o app abriria sem CSS/JS. Erro fatal.
        Write-Host ""
        Write-Host "[app] ERRO: assets public\build ausentes E Node.js nao esta instalado." -ForegroundColor Red
        Write-Host "       O instalador ficaria sem CSS/JS (interface quebrada)." -ForegroundColor Red
        Write-Host "       Instale o Node.js 22+: https://nodejs.org/" -ForegroundColor Yellow
        exit 1
    }
} else {
    Write-Host "[app] public\build OK"
}

# ---------- 3) Ajustar launcher.config.json apontando pro PHP embarcado ----------

$launcherCfg = Join-Path $StagingApp 'launcher\launcher.config.json'
if (Test-Path -LiteralPath $launcherCfg) {
    Write-Host "[cfg] ajustando launcher.config.json -> php embarcado"
    $cfg = Get-Content -LiteralPath $launcherCfg -Raw | ConvertFrom-Json
    $cfg.php.executable = '..\\..\\php\\php.exe'   # relativo ao launcher\
    $cfg.php.project_dir = '..'                    # launcher\.. = app\
    $cfg | ConvertTo-Json -Depth 10 | Set-Content -LiteralPath $launcherCfg -Encoding utf8
}

# ---------- 4) Payload de componentes (ComfyUI, ACE-Step, NewVideoMaker, ollama) ----------

$PayloadDir   = Join-Path $InstallerDir 'payload'
$StagingComps = Join-Path $StagingDir 'components'
$withComponents = $false

if (Test-Path -LiteralPath $StagingComps) { Remove-PathLong $StagingComps }

# Considera "tem payload" se alguma subpasta tem conteudo real (alem de .gitkeep / README)
$payloadReal = @()
if (Test-Path -LiteralPath $PayloadDir) {
    foreach ($sub in @('ComfyUI','ACE-Step','NewVideoMaker','ollama')) {
        $p = Join-Path $PayloadDir $sub
        if (Test-Path -LiteralPath $p) {
            $conteudo = Get-ChildItem -LiteralPath $p -Force -Recurse -File -ErrorAction SilentlyContinue |
                Where-Object { $_.Name -ne '.gitkeep' }
            if ($conteudo) { $payloadReal += $sub }
        }
    }
}

if ($payloadReal.Count -gt 0) {
    $withComponents = $true
    Write-Host "[payload] componentes encontrados: $($payloadReal -join ', ')"
    Write-Host "[payload] copiando para staging\components\ (pode demorar -- arquivos grandes)..."
    New-Item -ItemType Directory -Path $StagingComps -Force | Out-Null
    foreach ($sub in $payloadReal) {
        $src = Join-Path $PayloadDir $sub
        $dst = Join-Path $StagingComps $sub
        & robocopy $src $dst /E /NFL /NDL /NJH /NJS /NP /R:1 /W:1 /XF '.gitkeep' /MT:8 | Out-Null
        if ($LASTEXITCODE -ge 8) {
            Write-Host "[payload] robocopy de $sub falhou (codigo $LASTEXITCODE)" -ForegroundColor Red
            exit 1
        }
        $szGB = (Get-ChildItem -LiteralPath $dst -Recurse -File -ErrorAction SilentlyContinue | Measure-Object Length -Sum).Sum / 1GB
        Write-Host ("[payload]   {0}: {1:N2} GB" -f $sub, $szGB)
    }
} else {
    Write-Host "[payload] nenhum componente em installer\payload\ -- gerando instalador SO da app web." -ForegroundColor Yellow
    Write-Host "[payload] (cliente tera que configurar o pipeline manualmente em /setup)" -ForegroundColor Yellow
}

# ---------- 5) Inno Setup ----------

if ($SkipInno) {
    Write-Host ""
    Write-Host "Staging pronto em: $StagingDir" -ForegroundColor Green
    Write-Host ("Modo: " + $(if ($withComponents) { 'COMPLETO (com componentes)' } else { 'so app web' })) -ForegroundColor Cyan
    Write-Host "Pulei o Inno Setup compiler (-SkipInno)." -ForegroundColor Yellow
    exit 0
}

$iscc = Get-Command iscc.exe -ErrorAction SilentlyContinue
if (-not $iscc) {
    $candidates = @(
        "$env:ProgramFiles\Inno Setup 6\ISCC.exe",
        "${env:ProgramFiles(x86)}\Inno Setup 6\ISCC.exe"
    )
    foreach ($c in $candidates) { if (Test-Path -LiteralPath $c) { $iscc = $c; break } }
}

if (-not $iscc) {
    Write-Host ""
    Write-Host "Staging pronto em: $StagingDir" -ForegroundColor Green
    Write-Host "Inno Setup nao encontrado. Instale em https://jrsoftware.org/isinfo.php" -ForegroundColor Yellow
    Write-Host "Depois rode novamente: .\installer\build.ps1" -ForegroundColor Yellow
    exit 0
}

$isccPath = if ($iscc -is [System.Management.Automation.CommandInfo]) { $iscc.Source } else { $iscc }
Write-Host "[inno] compilando setup.iss com $isccPath"
if ($withComponents) {
    Write-Host "[inno] modo COMPLETO -- empacotando componentes (isso pode levar bastante tempo)" -ForegroundColor Cyan
}

$issPath = Join-Path $InstallerDir 'setup.iss'
$isccArgs = @("/DAppVersion=$AppVersion", "/O$DistDir")
if ($withComponents) { $isccArgs += '/DWithComponents=1' }
$isccArgs += $issPath
& $isccPath @isccArgs

if ($LASTEXITCODE -ne 0) {
    Write-Host "[inno] FALHOU (exit $LASTEXITCODE)" -ForegroundColor Red
    exit $LASTEXITCODE
}

Write-Host ""
Write-Host "==> Instalador gerado em: $DistDir" -ForegroundColor Green
Get-ChildItem -LiteralPath $DistDir | ForEach-Object { Write-Host "    $($_.Name)  ($([math]::Round($_.Length/1MB,1)) MB)" }
