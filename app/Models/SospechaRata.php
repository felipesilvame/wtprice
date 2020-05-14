<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SospechaRata extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_tienda',
        'sku',
        'precio_referencia',
        'precio_oferta',
        'precio_tarjeta',
        'screenshot_url',
        'porcentaje_rata',
        'porcentaje_rata_relativo',
        'nombre_tienda',
        'nombre_producto',
        'categoria',
        'url_compra',
        'url_imagen',
        'disponible',
        'stock',
        'segunda_notificacion',
    ];

    // RELATIONS

    /**
     * @return mixed
     */
    public function tienda(){
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    /**
     * @return mixed
     */
    public function producto(){
        return $this->belongsTo(Producto::class, 'sku', 'sku');
    }
}
