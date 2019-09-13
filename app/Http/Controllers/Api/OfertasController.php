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
        $query = $query->where('id_tienda', $request->input('tienda'));
      }

      return response()->json($query->paginate($paged));
    }
}
