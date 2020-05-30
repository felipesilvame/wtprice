<?php

namespace App\Http\Controllers\Api;

use App\Models\Producto;
use App\Models\MinimoPrecio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OfertasController extends Controller
{
    public function index(Request $request){
      $orderBy = $request->input('order', 'updated_at');
      $sort = $request->input('sort', 'DESC');
      $paged = $request->input('items', 25);
      $estado = $request->input('estado', 'Activo');
      $query = MinimoPrecio::with(['producto' => function ($query) use ($estado) {
        if ($estado != 'Todos') {
          $query = $query->where('estado', $estado);
        }
      }, 'producto.tienda' => function($query){
        $query->select('nombre', 'id');
      }])->orderBy($orderBy, $sort);
      if ($request->has('tienda')) {
        $query->whereHas('producto', function($q) use ($request){
          $q->where('id_tienda', $request->input('tienda'));
        });
      }

      return response()->json($query->paginate($paged));
    }

    public function ofertas_rata(Request $request){
      
      $paged = $request->input('items', 24);
      $estado = $request->input('estado', 'Activo');
      $tiendas = $request->input('id_tienda', []);
      $orderBy = $request->input('order', 'nombre');
      $sort = $request->input('sort', 'DESC');
      $percent = (float)$request->input('porcentaje', 0.6);

      $query = Producto::select(['productos.uuid', 'productos.id', 'productos.id_tienda', 'productos.imagen_url','productos.disponible', 'productos.stock',
      'productos.nombre', 'productos.precio_referencia', 'productos.precio_oferta', 'productos.precio_tarjeta',
      'productos.estado', 'productos.url_compra', 'tiendas.id as tienda_id', 'tiendas.nombre as nombre_tienda',
      'minimo_precios.precio_referencia as minimo_referencia', 'minimo_precios.precio_oferta as minimo_oferta', 'minimo_precios.precio_tarjeta as minimo_tarjeta',
      'minimo_precios.id_producto', 'minimo_precios.updated_at'])->join('tiendas', 'productos.id_tienda', '=', 'tiendas.id')->join('minimo_precios', 'productos.id', '=', 'minimo_precios.id_producto')
      ->where(function($query) use ($estado, $tiendas, $percent){
        if ($estado != 'Todos') {
          $query->where('productos.estado', $estado);
        }
        if ($tiendas && count($tiendas) !== 0) {
          $query->whereIn('productos.id_tienda', $tiendas);
        }
        $query->where(function($query2) use ($percent){
          $query2->whereRaw('((productos.precio_referencia - minimo_precios.precio_oferta) / cast(productos.precio_referencia as decimal(10,2))) >= ?', $percent)
          ->orWhereRaw('((productos.precio_referencia - minimo_precios.precio_tarjeta) / cast(productos.precio_referencia as decimal(10,2))) >= ?', $percent);
        });
      });
      $query = $query->orderBy('minimo_precios.updated_at', $sort);
      $results = $query->paginate($paged);
      return response()->json($results);
    }
}
