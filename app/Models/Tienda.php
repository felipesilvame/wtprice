<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use App\Models\Producto;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * Class Producto.
 */
class Tienda extends Model implements AuditableInterface
{
    use Auditable,
        SoftDeletes,
        Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'protocolo',
        'method',
        'prefix_api',
        'suffix_api',
        'headers',
        'querystring',
        'campo_nombre_producto',
        'campo_precio_referencia',
        'campo_precio_oferta',
        'campo_precio_tarjeta',
        'campo_request_error',
        'url_compra',
        'url_prefix_compra',
        'url_suffix_compra',
        'campo_slug_compra',
        'request_body_sku',
        'campo_imagen_url'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'id',

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'headers' => 'array',
        'querystring' => 'array'
    ];

    /**
     * @var array
     */
    protected $dates = [

    ];

    /**
     * The dynamic attributes from mutators that should be returned with the producto object.
     * @var array
     */
    protected $appends = [

    ];

    // RELATIONS

    /**
     * @return mixed
     */
    public function productos(){
      return $this->hasMany(Producto::class, 'id_tienda');
    }

}
