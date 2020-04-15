<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tienda;
use App\Models\Producto;

class AlertaRata extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_tienda',
        'id_producto',
        'precio_antes',
        'precio_oferta_antes',
        'precio_tarjeta_antes',
        'precio_ahora',
        'precio_oferta_ahora',
        'precio_tarjeta_ahora',
        'screenshot_url',
        'porcentaje_rata',
        'porcentaje_rata_relativo',
        'nombre_tienda',
        'nombre_producto',
        'url_compra',
        'url_imagen',
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
        return $this->belongsTo(Producto::class, 'id_producto');
    }

}
