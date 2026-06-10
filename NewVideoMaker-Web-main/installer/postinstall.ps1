# postinstall.ps1 -- executado pelo Inno Setup logo apos copiar os arquivos.
# Roda no PC do cliente. Faz o setup que normalmente seria manual:
#   - cria .env a partir de .env.example
#   - gera APP_KEY
#   - cria database\database.sqlite vazio
#   - roda migrations
#
# Idempotente: pode rodar varias vezes (em reinstalacao/atualizacao) sem quebrar.

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$InstallDir
)

$ErrorActionPreference = 'Stop'

$AppDir   = Join-Path $InstallDir 'app'
$PhpExe   = Join-Path $InstallDir 'php\php.exe'
$EnvFile  = Join-Path $AppDir '.env'
$EnvSample= Join-Path $AppDir '.env.example'
$DbFile   = Join-Path $AppDir 'database\database.sqlite'
$LogFile  = Join-Path $AppDir 'storage\logs\postinstall.log'

# Garante pasta de logs
$logDir = Split-Path -Parent $LogFile
if (-not (Test-Path -LiteralPath $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }

function Write-Step {
    param([string]$Msg)
    $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -LiteralPath $LogFile -Value "[$ts] $Msg" -Encoding utf8
}

function Invoke-Php {
    param([string[]]$Arguments)
    Write-Step "php $($Arguments -join ' ')"
    $proc = Start-Process -FilePath $PhpExe -ArgumentList $Arguments -WorkingDirectory $AppDir `
        -WindowStyle Hidden -Wait -PassThru `
        -RedirectStandardOutput (Join-Path $logDir 'postinstall.php.out.log') `
        -RedirectStandardError  (Join-Path $logDir 'postinstall.php.err.log')
    if ($proc.ExitCode -ne 0) {
        Write-Step "FALHA (exit $($proc.ExitCode))"
    }
    return $proc.ExitCode
}

Write-Step "=== Iniciando postinstall ==="
Write-Step "InstallDir = $InstallDir"
Write-Step "PHP        = $PhpExe"

# 1) .env
if (-not (Test-Path -LiteralPath $EnvFile)) {
    if (Test-Path -LiteralPath $EnvSample) {
        Copy-Item -LiteralPath $EnvSample -Destination $EnvFile -Force
        Write-Step ".env criado a partir de .env.example"
    } else {
        # .env minimo de emergencia caso .env.example nao esteja no pacote
        $minimal = @"
APP_NAME="NEW VideoMaker"
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
"@
        Set-Content -LiteralPath $EnvFile -Value $minimal -Encoding utf8
        Write-Step ".env minimo criado (nao havia .env.example)"
    }
} else {
    Write-Step ".env ja existe -- preservando"
}

# 2) APP_KEY (so gera se ainda nao tem)
$envContent = Get-Content -LiteralPath $EnvFile -Raw
if ($envContent -notmatch '(?m)^APP_KEY=base64:') {
    Invoke-Php -Arguments @('artisan','key:generate','--force','--no-ansi') | Out-Null
} else {
    Write-Step "APP_KEY ja definido -- pulando"
}

# 3) SQLite vazio
if (-not (Test-Path -LiteralPath $DbFile)) {
    New-Item -ItemType File -Path $DbFile -Force | Out-Null
    Write-Step "database.sqlite criado"
}

# 4) Migrations
Invoke-Php -Arguments @('artisan','migrate','--force','--no-ansi') | Out-Null

# 5) Instalar Ollama silenciosamente (se veio no payload offline)
$ComponentsDir = Join-Path $InstallDir 'components'
$OllamaSetup = Join-Path $ComponentsDir 'ollama\OllamaSetup.exe'
if (Test-Path -LiteralPath $OllamaSetup) {
    # So instala se ainda nao houver Ollama respondendo
    $ollamaUp = $false
    try {
        $c = [System.Net.Sockets.TcpClient]::new()
        $iar = $c.BeginConnect('127.0.0.1', 11434, $null, $null)
        if ($iar.AsyncWaitHandle.WaitOne(500, $false) -and $c.Connected) { $ollamaUp = $true }
        $c.Close()
    } catch { }

    if ($ollamaUp) {
        Write-Step "Ollama ja esta rodando -- pulando instalacao."
    } else {
        Write-Step "Instalando Ollama silenciosamente a partir do payload..."
        try {
            $p = Start-Process -FilePath $OllamaSetup -ArgumentList @('/SILENT','/NORESTART') `
                -Wait -PassThru -WindowStyle Hidden
            Write-Step "Ollama installer terminou (exit $($p.ExitCode))."
        } catch {
            Write-Step "Falha ao instalar Ollama: $_"
        }
    }
} else {
    Write-Step "Sem OllamaSetup.exe no payload -- cliente instala via wizard /setup se quiser."
}

# 6) Autoconfigura componentes pre-montados (ComfyUI, ACE-Step, pipeline NewVideoMaker)
if (Test-Path -LiteralPath $ComponentsDir) {
    Write-Step "Autoconfigurando componentes de $ComponentsDir ..."
    Invoke-Php -Arguments @('artisan','setup:autoconfigure',"--components-dir=$ComponentsDir",'--no-ansi') | Out-Null
} else {
    Write-Step "Sem pasta components\ -- instalacao so da app web (cliente configura em /setup)."
}

# 7) Cache de config/route/view (otimiza primeira requisicao)
Invoke-Php -Arguments @('artisan','config:cache','--no-ansi')       | Out-Null
Invoke-Php -Arguments @('artisan','route:cache','--no-ansi')        | Out-Null
Invoke-Php -Arguments @('artisan','view:cache','--no-ansi')         | Out-Null

# 8) Limpa cache da aplicacao (AppConfig reconstroi na primeira request)
Invoke-Php -Arguments @('artisan','cache:clear','--no-ansi')        | Out-Null

Write-Step "=== Postinstall concluido ==="
exit 0
