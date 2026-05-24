# NEW VideoMaker — launcher PowerShell.
#
# Sobe o servidor web Laravel + worker da fila + (opcionalmente) os serviços
# Python externos (Ollama, ComfyUI, ACE-Step). Mostra um tray icon com menu
# de "Abrir interface", "Ver logs", "Reiniciar" e "Sair". Ao sair, encerra
# todos os subprocessos que iniciou.
#
# Tudo configurável em launcher.config.json (mesma pasta deste script).
# Não exige nada além de PowerShell 5.1+ e PHP no PATH (ou caminho absoluto na config).

[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ConfigPath = Join-Path $ScriptDir 'launcher.config.json'
$LogsDir = Join-Path $ScriptDir 'logs'

if (-not (Test-Path -LiteralPath $ConfigPath)) {
    [System.Windows.Forms.MessageBox]::Show("Arquivo nao encontrado:`n$ConfigPath", "NEW VideoMaker", 'OK', 'Error') | Out-Null
    exit 1
}

if (-not (Test-Path -LiteralPath $LogsDir)) {
    New-Item -ItemType Directory -Path $LogsDir -Force | Out-Null
}

$global:Config = Get-Content -LiteralPath $ConfigPath -Raw | ConvertFrom-Json
$global:Processes = @{}
$global:ShuttingDown = $false

# ---------- Helpers ----------

function Write-LauncherLog {
    param([string]$Message)
    $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $line = "[$ts] $Message"
    Add-Content -LiteralPath (Join-Path $LogsDir 'launcher.log') -Value $line -Encoding utf8
}

function Test-PortOpen {
    param([string]$HostName, [int]$Port, [int]$TimeoutMs = 500)
    try {
        $client = [System.Net.Sockets.TcpClient]::new()
        $iar = $client.BeginConnect($HostName, $Port, $null, $null)
        $ok = $iar.AsyncWaitHandle.WaitOne($TimeoutMs, $false)
        if ($ok -and $client.Connected) { $client.Close(); return $true }
        $client.Close()
    } catch { }
    return $false
}

function Wait-PortReady {
    param([string]$HostName, [int]$Port, [int]$TimeoutSeconds = 30)
    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)
    while ((Get-Date) -lt $deadline) {
        if (Test-PortOpen -HostName $HostName -Port $Port -TimeoutMs 300) { return $true }
        Start-Sleep -Milliseconds 500
    }
    return $false
}

function Resolve-PathAbsolute {
    param([string]$Path)
    if ([string]::IsNullOrWhiteSpace($Path)) { return $null }
    if ([System.IO.Path]::IsPathRooted($Path)) { return $Path }
    $resolved = Resolve-Path -LiteralPath (Join-Path $ScriptDir $Path) -ErrorAction SilentlyContinue
    if ($null -eq $resolved) { return $null }
    return $resolved.Path
}

function Start-ServiceProcess {
    param(
        [string]$Name,
        [string]$Executable,
        [string[]]$Arguments,
        [string]$WorkingDirectory
    )

    $stdoutLog = Join-Path $LogsDir "$Name.out.log"
    $stderrLog = Join-Path $LogsDir "$Name.err.log"

    $startInfo = @{
        FilePath               = $Executable
        ArgumentList           = $Arguments
        WindowStyle            = 'Hidden'
        PassThru               = $true
        RedirectStandardOutput = $stdoutLog
        RedirectStandardError  = $stderrLog
    }

    if (-not [string]::IsNullOrWhiteSpace($WorkingDirectory) -and (Test-Path -LiteralPath $WorkingDirectory)) {
        $startInfo['WorkingDirectory'] = $WorkingDirectory
    }

    $proc = Start-Process @startInfo
    Write-LauncherLog "[$Name] iniciado pid=$($proc.Id) cmd='$Executable $($Arguments -join ' ')'"
    return $proc
}

function Stop-AllProcesses {
    if ($global:ShuttingDown) { return }
    $global:ShuttingDown = $true

    foreach ($name in @($global:Processes.Keys)) {
        $proc = $global:Processes[$name]
        if ($null -ne $proc -and -not $proc.HasExited) {
            try {
                Write-LauncherLog "[$name] parando pid=$($proc.Id)"
                # taskkill /T mata tambem processos filhos (importante para 'php artisan serve' que dispara o servidor PHP nativo)
                Start-Process -FilePath 'taskkill.exe' -ArgumentList @('/PID', $proc.Id, '/T', '/F') -Wait -WindowStyle Hidden | Out-Null
            } catch {
                Write-LauncherLog "[$name] erro ao parar: $_"
            }
        }
    }
    Write-LauncherLog "Launcher encerrado."
}

function Open-AppUrl {
    $url = "http://$($Config.app.host):$($Config.app.port)"
    Start-Process $url | Out-Null
}

# ---------- Subir tudo ----------

Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

Write-LauncherLog "Iniciando launcher do $($Config.app.name)..."

# Resolve caminho do projeto (onde mora o artisan)
$projectDir = Resolve-PathAbsolute $Config.php.project_dir
if (-not $projectDir -or -not (Test-Path -LiteralPath (Join-Path $projectDir 'artisan'))) {
    [System.Windows.Forms.MessageBox]::Show(
        "Pasta do projeto Laravel nao encontrada (artisan ausente).`nConfigure 'php.project_dir' em launcher.config.json.",
        "NEW VideoMaker", 'OK', 'Error') | Out-Null
    exit 1
}
Write-LauncherLog "project_dir = $projectDir"

# 1) Servicos externos primeiro (Ollama, ComfyUI, ACE-Step)
foreach ($svc in $Config.services) {
    if (-not $svc.enabled) {
        Write-LauncherLog "[$($svc.name)] desabilitado em config, pulando."
        continue
    }

    if (Test-PortOpen -HostName $Config.app.host -Port $svc.wait_port) {
        Write-LauncherLog "[$($svc.name)] porta $($svc.wait_port) ja respondendo, assumindo que ja esta rodando."
        continue
    }

    try {
        $proc = Start-ServiceProcess -Name $svc.name -Executable $svc.executable -Arguments $svc.args -WorkingDirectory $svc.workdir
        $global:Processes[$svc.name] = $proc
    } catch {
        Write-LauncherLog "[$($svc.name)] FALHOU ao iniciar: $($_.Exception.Message)"
    }
}

# 2) Servidor web Laravel
$serveArgs = @('artisan', 'serve', "--host=$($Config.app.host)", "--port=$($Config.app.port)")
$global:Processes['web'] = Start-ServiceProcess -Name 'web' -Executable $Config.php.executable -Arguments $serveArgs -WorkingDirectory $projectDir

# 3) Worker da fila
$global:Processes['queue'] = Start-ServiceProcess -Name 'queue' -Executable $Config.php.executable -Arguments $Config.php.queue_args -WorkingDirectory $projectDir

# 4) Aguardar web ficar de pe e abrir browser
$webReady = Wait-PortReady -HostName $Config.app.host -Port $Config.app.port -TimeoutSeconds $Config.app.ready_timeout_seconds
if (-not $webReady) {
    Write-LauncherLog "[web] nao ficou pronto em $($Config.app.ready_timeout_seconds)s. Abrindo browser mesmo assim."
}
if ($Config.app.open_browser) { Open-AppUrl }

# ---------- Tray icon ----------

$tray = New-Object System.Windows.Forms.NotifyIcon
$tray.Icon = [System.Drawing.SystemIcons]::Application
$tray.Text = $Config.app.name
$tray.Visible = $true
$tray.BalloonTipTitle = $Config.app.name
$tray.BalloonTipText = "Aplicacao rodando em http://$($Config.app.host):$($Config.app.port)"
$tray.ShowBalloonTip(3000)

$menu = New-Object System.Windows.Forms.ContextMenuStrip

$itemOpen = $menu.Items.Add('Abrir interface')
$itemOpen.Add_Click({ Open-AppUrl })

$itemLogs = $menu.Items.Add('Pasta de logs')
$itemLogs.Add_Click({ Start-Process explorer.exe $LogsDir })

$itemRestart = $menu.Items.Add('Reiniciar tudo')
$itemRestart.Add_Click({
    $tray.Visible = $false
    Stop-AllProcesses
    # Re-spawn este script
    Start-Process -FilePath 'powershell.exe' -ArgumentList @('-NoProfile','-WindowStyle','Hidden','-ExecutionPolicy','Bypass','-File',$PSCommandPath) | Out-Null
    [System.Windows.Forms.Application]::Exit()
})

$menu.Items.Add('-') | Out-Null

$itemQuit = $menu.Items.Add('Sair')
$itemQuit.Add_Click({
    $tray.Visible = $false
    Stop-AllProcesses
    [System.Windows.Forms.Application]::Exit()
})

$tray.ContextMenuStrip = $menu

# Clique simples abre a interface
$tray.Add_MouseClick({
    param($sender, $e)
    if ($e.Button -eq [System.Windows.Forms.MouseButtons]::Left) { Open-AppUrl }
})

# Garante limpeza se a janela do PS for fechada de forma anormal
Register-EngineEvent PowerShell.Exiting -Action { Stop-AllProcesses } | Out-Null

# Event loop bloqueante — mantém o tray ativo
[System.Windows.Forms.Application]::Run()

# Caso saia do loop sem ter chamado Quit (fechamento abrupto)
Stop-AllProcesses
