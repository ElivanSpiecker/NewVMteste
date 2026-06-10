<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_secret'];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    /**
     * Decifra automaticamente o valor quando o campo está marcado como secreto.
     */
    public function getValueAttribute(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        if (!$this->is_secret) {
            return $raw;
        }
        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Cifra automaticamente quando is_secret = true.
     * is_secret precisa estar definido ANTES do value no array de atributos.
     */
    public function setValueAttribute(?string $raw): void
    {
        if ($raw === null || !$this->is_secret) {
            $this->attributes['value'] = $raw;
            return;
        }
        $this->attributes['value'] = Crypt::encryptString($raw);
    }
}
