<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoUploadHandler
{
    /**
     * Processa todos os uploads de um vídeo conforme os modos selecionados,
     * salvando os arquivos em storage/app/uploads/{video_id}/...
     */
    public function handleAll(Video $video, Request $request, array $attributes): void
    {
        $uploadDir = storage_path("app/uploads/{$video->id}");

        if (($attributes['imagens_modo'] ?? null) === 'upload') {
            $this->handleImages($request, $uploadDir);
        }

        if (($attributes['narracao_modo'] ?? null) === 'upload') {
            $this->handleSingleFile($request, 'narracao', $uploadDir, 'narracao', 'mp3');
        }

        if (($attributes['musica_modo'] ?? null) === 'upload') {
            $this->handleSingleFile($request, 'musica', $uploadDir, 'musica', 'mp3');
        }

        if (($attributes['legendas_modo'] ?? null) === 'upload') {
            $this->handleSingleFile($request, 'legendas', $uploadDir, 'legenda', 'srt');
        }
    }

    /** Resolução alvo para vídeo retrato 9:16 (mesma do pipeline FLUX). */
    private const TARGET_W = 640;
    private const TARGET_H = 1120;

    private function handleImages(Request $request, string $uploadDir): void
    {
        $dest = "{$uploadDir}/imagens";
        $this->ensureDir($dest);

        foreach ($request->file('imagens', []) as $i => $file) {
            $outPath = sprintf('%s/cena%02d.jpg', $dest, $i + 1);
            $this->normalizeToPortrait($file->getRealPath(), $outPath);
        }
    }

    /**
     * Carrega a imagem, faz crop central para 9:16 e redimensiona para 640x1120,
     * salvando como JPG (qualidade 90). Sem dependências externas — usa GD.
     */
    private function normalizeToPortrait(string $srcPath, string $outPath): void
    {
        $info = @getimagesize($srcPath);
        if ($info === false) {
            // Fallback: copia o arquivo se não for uma imagem reconhecível
            copy($srcPath, $outPath);
            return;
        }

        [$srcW, $srcH] = $info;
        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($srcPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($srcPath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false,
            IMAGETYPE_BMP  => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($srcPath) : false,
            IMAGETYPE_GIF  => @imagecreatefromgif($srcPath),
            default        => false,
        };

        if (!$src) {
            copy($srcPath, $outPath);
            return;
        }

        // Aplica rotação automática conforme EXIF (fotos de celular)
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($srcPath);
            if (!empty($exif['Orientation'])) {
                $src = $this->applyExifOrientation($src, (int) $exif['Orientation']);
                $srcW = imagesx($src);
                $srcH = imagesy($src);
            }
        }

        $targetRatio = self::TARGET_W / self::TARGET_H; // 9/16 = 0.5625
        $srcRatio    = $srcW / $srcH;

        // Crop central: mantém o maior recorte 9:16 possível
        if ($srcRatio > $targetRatio) {
            // Imagem mais larga que 9:16 → corta laterais
            $cropH = $srcH;
            $cropW = (int) round($srcH * $targetRatio);
            $cropX = (int) round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Imagem mais alta que 9:16 → corta topo/base
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($srcH - $cropH) / 2);
        }

        $dst = imagecreatetruecolor(self::TARGET_W, self::TARGET_H);
        imagecopyresampled(
            $dst, $src,
            0, 0, $cropX, $cropY,
            self::TARGET_W, self::TARGET_H,
            $cropW, $cropH
        );

        imagejpeg($dst, $outPath, 90);
        imagedestroy($src);
        imagedestroy($dst);
    }

    private function applyExifOrientation(\GdImage $img, int $orientation): \GdImage
    {
        return match ($orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }

    private function handleSingleFile(
        Request $request,
        string $field,
        string $uploadDir,
        string $baseName,
        string $defaultExt,
    ): void {
        $this->ensureDir($uploadDir);

        $file = $request->file($field);
        $ext = strtolower($file->getClientOriginalExtension() ?: $defaultExt);
        $file->move($uploadDir, "{$baseName}.{$ext}");
    }

    private function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
