@echo off
REM Wrapper para iniciar o NEW VideoMaker sem janela de console visivel.
REM Da clique duplo aqui — o app sobe em background e aparece na bandeja do Windows.

setlocal
set "SCRIPT_DIR=%~dp0"
start "" /b powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File "%SCRIPT_DIR%start.ps1"
endlocal
exit /b 0
