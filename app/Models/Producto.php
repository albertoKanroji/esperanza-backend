<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'dimenciones',
        'modelo',
        'color',
        'id_categoria',
        'categorias_id',
        'estado_escaneo',
        'almacenes_id',
        'estado_deuda',
        'estadia'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categorias_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacenes_id');
    }
}
