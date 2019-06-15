<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use App\Models\Tienda;
use App\Models\Producto;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

/**
 * Class Producto.
 */
class HistorialPrecio extends Model implements AuditableInterface
{
    use Auditable,
        SoftDeletes,
        Uuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'historial_precios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_producto',
        'precio_referencia',
        'precio_oferta',
        'precio_tarjeta',

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
        'id_producto' => 'integer',
        'precio_referencia' => 'double',
        'precio_tarjeta' => 'double',
        'precio_oferta' => 'double',
    ];

    /**
     * @var array
     */
    protected $dates = [
      'fecha'
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
    public function producto(){
      return $this->belongsTo(Producto::class, 'id_producto');
    }

    /**
     * @return mixed
     */
    // public function historico(){
    //   return $this->hasMany(HistorialPrecio::class, 'id_producto');
    // }

    /**
     * @return mixed
     */
    // public function minimo(){
    //   return $this->hasMany(MinimoPrecio::class, 'id_producto');
    // }
}
