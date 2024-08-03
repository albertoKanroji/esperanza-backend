<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agencia extends Model
{
    use HasFactory;
    protected $table = 'agencia';

    protected $fillable = [
        'nombre',
        'rfc',
    ];
}
