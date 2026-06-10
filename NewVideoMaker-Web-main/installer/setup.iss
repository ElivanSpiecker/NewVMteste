; setup.iss -- instalador Inno Setup do NEW VideoMaker
;
; Compile com:  ISCC.exe /DAppVersion=0.1.0 /Oinstaller\dist installer\setup.iss
; Ou simplesmente:  .\installer\build.ps1   (chama o iscc e define /DWithComponents se houver payload)
;
; Empacota: installer\staging\php  +  installer\staging\app
;   e, se /DWithComponents for passado: installer\staging\components (ComfyUI, ACE-Step, NewVideoMaker, ollama)
;
; Saida:    installer\dist\NewVideoMaker-Setup-{AppVersion}.exe  (+ arquivos .bin se disk spanning)

#ifndef AppVersion
  #define AppVersion "0.1.0"
#endif

#define AppName       "NEW VideoMaker"
#define AppPublisher  "Elivan & Nicolas (UNIVATES)"
#define AppExeName    "launcher\start.bat"
#define AppId         "{{4F7B1E8C-22A1-4D7D-AD3C-1C7B6F9E5B11}}"

[Setup]
AppId={#AppId}
AppName={#AppName}
AppVersion={#AppVersion}
AppPublisher={#AppPublisher}
AppMutex=NewVideoMakerInstallerMutex
DefaultDirName={autopf}\NewVideoMaker
DefaultGroupName={#AppName}
DisableProgramGroupPage=yes
DisableDirPage=no
DisableWelcomePage=no
OutputBaseFilename=NewVideoMaker-Setup-{#AppVersion}
WizardStyle=modern
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
PrivilegesRequired=lowest
PrivilegesRequiredOverridesAllowed=dialog commandline
UninstallDisplayName={#AppName} {#AppVersion}
UninstallDisplayIcon={app}\app\public\favicon.ico
ShowLanguageDialog=auto
MinVersion=10.0
#ifdef WithComponents
  ; Modo COMPLETO: componentes pesados (modelos ja sao binarios comprimidos).
  ; Compressao rapida + disk spanning em fatias de ~2 GB para o instalador caber em
  ; sistemas de arquivos / midias sem o limite de arquivo unico gigante.
  Compression=lzma2/fast
  SolidCompression=no
  DiskSpanning=yes
  DiskSliceSize=2100000000
#else
  ; Modo so app web (~100 MB): compressao maxima vale a pena.
  Compression=lzma2/ultra
  SolidCompression=yes
#endif

[Languages]
Name: "brazilianportuguese"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl"
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "Criar atalho na &area de trabalho"; GroupDescription: "Atalhos:"; Flags: checkedonce
Name: "quicklaunchicon"; Description: "Criar atalho na barra de inicio rapido"; GroupDescription: "Atalhos:"; Flags: unchecked

[Dirs]
; Garante permissao de escrita para o app gravar SQLite, logs e uploads
Name: "{app}\app\database"; Permissions: users-modify
Name: "{app}\app\storage"; Permissions: users-modify
Name: "{app}\app\bootstrap\cache"; Permissions: users-modify
Name: "{app}\app\launcher\logs"; Permissions: users-modify
#ifdef WithComponents
  ; Pipeline grava em components\NewVideoMaker\output
  Name: "{app}\components"; Permissions: users-modify
#endif

[Files]
; PHP embarcado
Source: "staging\php\*"; DestDir: "{app}\php"; Flags: ignoreversion recursesubdirs createallsubdirs

; App Laravel inteiro
Source: "staging\app\*"; DestDir: "{app}\app"; Flags: ignoreversion recursesubdirs createallsubdirs

; Scripts de pos/pre acoes
Source: "postinstall.ps1"; DestDir: "{app}\installer"; Flags: ignoreversion
Source: "preuninstall.ps1"; DestDir: "{app}\installer"; Flags: ignoreversion

#ifdef WithComponents
  ; Componentes pre-montados: ComfyUI, ACE-Step, NewVideoMaker, ollama.
  ; nocompression -- modelos de IA ja sao binarios comprimidos; recomprimir so gasta CPU.
  Source: "staging\components\*"; DestDir: "{app}\components"; Flags: ignoreversion recursesubdirs createallsubdirs nocompression
#endif

[Icons]
Name: "{group}\{#AppName}"; Filename: "{app}\app\launcher\start.bat"; WorkingDir: "{app}\app\launcher"; IconFilename: "{app}\app\public\favicon.ico"; Comment: "Iniciar NEW VideoMaker"
Name: "{group}\Pasta de instalacao"; Filename: "{app}"
Name: "{group}\Desinstalar NEW VideoMaker"; Filename: "{uninstallexe}"
Name: "{commondesktop}\{#AppName}"; Filename: "{app}\app\launcher\start.bat"; WorkingDir: "{app}\app\launcher"; IconFilename: "{app}\app\public\favicon.ico"; Tasks: desktopicon
Name: "{userappdata}\Microsoft\Internet Explorer\Quick Launch\{#AppName}"; Filename: "{app}\app\launcher\start.bat"; WorkingDir: "{app}\app\launcher"; IconFilename: "{app}\app\public\favicon.ico"; Tasks: quicklaunchicon

[Run]
; First-run: cria .env, gera APP_KEY, roda migrations, autoconfigura componentes
Filename: "powershell.exe"; \
  Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\installer\postinstall.ps1"" -InstallDir ""{app}"""; \
  StatusMsg: "Configurando aplicacao (primeira execucao)..."; \
  Flags: runhidden waituntilterminated

; Oferece iniciar o app ao final
Filename: "{app}\app\launcher\start.bat"; \
  Description: "Iniciar {#AppName} agora"; \
  Flags: postinstall nowait skipifsilent shellexec

[UninstallRun]
; Mata processos antes de remover arquivos
Filename: "powershell.exe"; \
  Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\installer\preuninstall.ps1"" -InstallDir ""{app}"""; \
  RunOnceId: "stopServices"; \
  Flags: runhidden waituntilterminated

[UninstallDelete]
; Pastas geradas em runtime que o Inno nao trackeia
Type: filesandordirs; Name: "{app}\app\storage\logs"
Type: filesandordirs; Name: "{app}\app\storage\framework\cache"
Type: filesandordirs; Name: "{app}\app\storage\framework\sessions"
Type: filesandordirs; Name: "{app}\app\storage\framework\views"
Type: filesandordirs; Name: "{app}\app\storage\app\uploads"
Type: filesandordirs; Name: "{app}\app\storage\app\youtube-uploads"
Type: filesandordirs; Name: "{app}\app\launcher\logs"
Type: files;          Name: "{app}\app\database\*.sqlite"
Type: files;          Name: "{app}\app\database\*.sqlite-journal"
Type: files;          Name: "{app}\app\.env"
Type: files;          Name: "{app}\app\bootstrap\cache\*.php"
#ifdef WithComponents
  ; Saida gerada pelo pipeline
  Type: filesandordirs; Name: "{app}\components\NewVideoMaker\output"
#endif

[Code]
function InitializeSetup(): Boolean;
begin
  Result := True;
end;
