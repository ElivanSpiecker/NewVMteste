<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupTask extends Model
{
    protected $fillable = ['kind','label','status','progresso','mensagem','erro'];

    protected $attributes = [
        'status'    => 'pending',
        'progresso' => 0,
    ];

    public function isDone(): bool   { return $this->status === 'done'; }
    public function isFailed(): bool { return $this->status === 'failed'; }
    public function isRunning(): bool { return in_array($this->status, ['pending','running'], true); }
}
