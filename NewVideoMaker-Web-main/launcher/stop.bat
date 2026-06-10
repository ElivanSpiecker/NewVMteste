@echo off
REM Para tudo: fecha qualquer instancia do launcher rodando.
REM Use isto se a opcao "Sair" do tray nao funcionou ou o tray sumiu.

echo Parando launcher e processos do NEW VideoMaker...

REM Mata processos PowerShell que estao executando o start.ps1
for /f "tokens=2" %%i in ('tasklist /v /fi "imagename eq powershell.exe" /fo list ^| findstr /i "start.ps1"') do (
    taskkill /pid %%i /t /f >nul 2>&1
)

REM Mata processos PHP que estao servindo o app ou queue worker (porta 8000 e similares)
taskkill /im php.exe /f >nul 2>&1

echo Pronto.
timeout /t 2 >nul
