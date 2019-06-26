<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use App\Models\Tienda;
use App\Models\HistorialPrecio;
use App\Models\MinimoPrecio;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * Class Producto.
 */
class Producto extends Model implements AuditableInterface
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
        'id_tienda',
        'marca',
        'estado',
        'modelo',
        'descripcion',
        'imagen_url',
        'categoria',
        'precio_referencia',
        'precio_oferta',
        'precio_tarjeta',
        'sku',
        'ultima_actualizacion',
        'intervalo_actualizacion',
        'umbral_descuento',
        'url_compra',
        'intentos_fallidos',
        'actualizacion_pendiente',
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
        'id_tienda' => 'integer',
        'precio_referencia' => 'double',
        'precio_tarjeta' => 'double',
        'precio_oferta' => 'double',
        'intervalo_actualizacion' => 'integer',
        'umbral_descuento' => 'float',
        'actualizacion_pendiente' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $dates = [
      'ultima_actualizacion'
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
    public function tienda(){
      return $this->belongsTo(Tienda::class, 'id_tienda');
    }

    /**
     * @return mixed
     */
    public function historico(){
      return $this->hasMany(HistorialPrecio::class, 'id_producto');
    }

    /**
     * @return mixed
     */
    public function minimo(){
      return $this->hasOne(MinimoPrecio::class, 'id_producto');
    }
}
