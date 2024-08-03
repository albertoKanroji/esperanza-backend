<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntradaProducto extends Model
{
    use HasFactory;

    protected $table = 'entrada_producto';

    protected $fillable = [
        'productos_id',
        'fecha_entrada',
        'trabajadores_id',
        'precio_dia',
        'total_deuda',
        'clientes_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajadores_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }
}
