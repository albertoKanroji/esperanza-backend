<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaRecogerProducto extends Model
{
    use HasFactory;
    protected $table = 'salida_recoger_producto';

    protected $fillable = [
        'trabajadores_id',
        'productos_id',
        'clientes_id',
        'camiones_id',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajadores_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class, 'camiones_id');
    }
}