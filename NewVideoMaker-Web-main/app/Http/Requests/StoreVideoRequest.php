<?php

namespace App\Http\Requests;

use App\Http\Controllers\VoiceController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tema'           => ['required', 'string', 'max:2000'],
            'duracao'        => ['required', 'integer', 'min:15', 'max:120'],
            'idioma'         => ['nullable', 'in:PT-BR,EN-US'],
            'voz'            => ['nullable', Rule::in(VoiceController::allVoiceIds())],
            'imagens_modo'   => ['nullable', 'in:gerar,upload'],
            'narracao_modo'  => ['nullable', 'in:gerar,upload,nenhum'],
            'musica_modo'    => ['nullable', 'in:gerar,upload,nenhum'],
            'legendas_modo'  => ['nullable', 'in:gerar,upload,nenhum'],
            'imagens'        => ['required_if:imagens_modo,upload', 'array', 'min:1', 'max:20'],
            'imagens.*'      => ['file', 'mimes:jpg,jpeg,png,webp,bmp', 'max:10240'],
            'narracao'       => ['required_if:narracao_modo,upload', 'file', 'mimes:mp3,wav,m4a,ogg,aac,flac', 'max:51200'],
            'musica'         => ['required_if:musica_modo,upload',   'file', 'mimes:mp3,wav,m4a,ogg,aac,flac', 'max:102400'],
            'legendas'       => ['required_if:legendas_modo,upload', 'file', 'mimes:srt,vtt,txt',              'max:2048'],
        ];
    }

    /**
     * Retorna os dados validados já com defaults aplicados,
     * prontos para o VideoCreationService.
     */
    public function videoAttributes(): array
    {
        $data = $this->validated();

        return [
            'tema'           => $data['tema'],
            'duracao'        => $data['duracao'],
            'idioma'         => $data['idioma']         ?? 'PT-BR',
            'voz'            => $data['voz']            ?? null,
            'imagens_modo'   => $data['imagens_modo']   ?? 'gerar',
            'narracao_modo'  => $data['narracao_modo'] ?? 'gerar',
            'musica_modo'    => $data['musica_modo']   ?? 'gerar',
            'legendas_modo'  => $data['legendas_modo'] ?? 'gerar',
        ];
    }
}
