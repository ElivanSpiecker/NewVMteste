# Integração realizada

Este projeto foi integrado com a interface Laravel Blade + Tailwind mantendo o backend original do NewVideoMaker-Web.

## O que foi alterado

- A interface principal deixou de depender das páginas Vue/Inertia.
- As rotas web agora renderizam Blade.
- O backend original foi mantido:
  - Model `Video`
  - Job `GerarVideo`
  - Controllers de API
  - Rotas de API
  - Download de MP4 e SRT
  - Health check dos serviços locais
- Foram adicionadas telas Blade com o visual do front convertido:
  - Criar vídeo
  - Dashboard
  - Meus vídeos
  - Status do processamento
  - Resultado/download
  - Pipeline
  - Configurações
  - Sobre
  - Serviços/Health

## Arquivos principais adicionados/alterados

- `routes/web.php`
- `app/Http/Controllers/VideoController.php`
- `app/Http/Controllers/HealthController.php`
- `resources/css/app.css`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/sidebar.blade.php`
- `resources/views/videos/create.blade.php`
- `resources/views/videos/index.blade.php`
- `resources/views/videos/status.blade.php`
- `resources/views/videos/show.blade.php`
- `resources/views/health/index.blade.php`
- `resources/views/pages/dashboard.blade.php`
- `resources/views/pages/pipeline.blade.php`
- `resources/views/pages/config.blade.php`
- `resources/views/pages/sobre.blade.php`
- `public/assets/*`

## Como rodar

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Para processar os vídeos pela fila:

```bash
php artisan queue:listen --queue=video-generation --tries=1 --timeout=0
```

Ou use o script já existente no `composer.json`:

```bash
composer run dev
```

## Validação feita

- `php -l` executado nos arquivos PHP alterados sem erros de sintaxe.
- `npm install` executado com sucesso.
- `npm run build` executado com sucesso.

Observação: não foi possível executar `composer install` neste ambiente porque o comando `composer` não está instalado no container.
