<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'chats';

    protected $fillable = [
        'trabajadores_id',
        'token',
        'clientes_id',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajadores_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }
}
