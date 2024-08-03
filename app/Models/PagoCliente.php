<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoCliente extends Model
{
    use HasFactory;
    protected $table = 'pago_cliente';

    protected $fillable = [
        'clientes_id',
        'metodos_pago_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodos_pago_id');
    }
}
