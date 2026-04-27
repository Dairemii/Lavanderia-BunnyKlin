<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = ['folio', 'total', 'metodo_pago', 'productos'];
    
    // Esto le dice a Laravel que convierta el texto a un Arreglo automáticamente
    protected $casts = [
        'productos' => 'array', 
    ];
}