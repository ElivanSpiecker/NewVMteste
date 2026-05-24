# preuninstall.ps1 -- chamado pelo Inno Setup ANTES de remover arquivos.
# Garante que o launcher e o worker estao parados pra nao travar a remocao
# de arquivos (Windows segura arquivos em uso por processos abertos).

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$InstallDir
)

$ErrorActionPreference = 'SilentlyContinue'

$PhpExe = Join-Path $InstallDir 'php\php.exe'
$logDir = Join-Path $InstallDir 'app\storage\logs'
if (-not (Test-Path -LiteralPath $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }
$LogFile = Join-Path $logDir 'preuninstall.log'

function Write-Step {
    param([string]$Msg)
    $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -LiteralPath $LogFile -Value "[$ts] $Msg" -Encoding utf8
}

Write-Step "=== preuninstall iniciado (InstallDir=$InstallDir) ==="

# 1) Mata o tray do launcher (PowerShell rodando start.ps1) -- busca por commandline
try {
    $procs = Get-CimInstance Win32_Process -ErrorAction SilentlyContinue |
        Where-Object {
            $_.Name -eq 'powershell.exe' -and
            $_.CommandLine -and
            $_.CommandLine -like "*$InstallDir*start.ps1*"
        }
    foreach ($p in $procs) {
        Write-Step "kill launcher pid=$($p.ProcessId)"
        Stop-Process -Id $p.ProcessId -Force -ErrorAction SilentlyContinue
    }
} catch { Write-Step "erro buscando launcher: $_" }

# 2) Mata processos PHP que estao no diretorio de instalacao (servidor + worker)
try {
    $procs = Get-CimInstance Win32_Process -ErrorAction SilentlyContinue |
        Where-Object {
            $_.Name -eq 'php.exe' -and
            $_.ExecutablePath -and
            $_.ExecutablePath.ToLower().StartsWith($InstallDir.ToLower())
        }
    foreach ($p in $procs) {
        Write-Step "kill php pid=$($p.ProcessId) ($($p.ExecutablePath))"
        # taskkill /T pega filhos (php artisan serve gera processos filho)
        Start-Process -FilePath 'taskkill.exe' `
            -ArgumentList @('/PID', $p.ProcessId, '/T', '/F') `
            -Wait -WindowStyle Hidden -ErrorAction SilentlyContinue | Out-Null
    }
} catch { Write-Step "erro buscando php: $_" }

# 3) Da um respiro pra OS liberar handles
Start-Sleep -Milliseconds 800

Write-Step "=== preuninstall concluido ==="
exit 0
