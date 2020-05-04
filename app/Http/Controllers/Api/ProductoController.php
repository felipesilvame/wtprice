<?php

namespace App\Http\Controllers\Api;

use App\Models\Producto;
use App\Models\MinimoPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Jobs\ProcessProduct;
use App\Jobs\ProcessParisProduct;

class ProductoController extends Controller
{
    public function index(Request $request){
      $orderBy = $request->input('order', 'created_at');
      $sort = $request->input('sort', 'DESC');
      $paged = $request->input('items', 25);
      $estado = $request->input('estado', 'Activo');
      $query = Producto::with(['minimo', 'tienda' => function($query){
        $query->select('nombre', 'id');
      }])->orderBy($orderBy, $sort);
      if ($request->has('tienda')) {
        $query = $query->where('id_tienda', $request->input('tienda'));
      }
      if ($estado != 'Todos') {
        $query = $query->where('estado', $estado);
      }
      return response()->json($query->paginate($paged));
    }

    public function toggleProducto(AdminRequest $request, $id){
      $producto = \App\Models\Producto::findOrFail($id);
      if ($producto->estado === 'Activo') {
        $producto->estado = 'Detenido';
      } else if ($producto->estado === 'Detenido'){
        $producto->estado = 'Activo';
        $producto->intentos_fallidos = 0;
        $producto->actualizacion_pendiente = true;
      }
      $producto->save();
      return response()->json($producto);
    }

    public function updateProducto(AdminRequest $request, $id){
      $producto = \App\Models\Producto::findOrFail($id);
      $producto->load(['tienda']);
      if ($producto->tienda->nombre === 'Paris') {
        ProcessParisProduct::dispatch($producto);
      }else {
        ProcessProduct::dispatch($producto);
      }
      return response()->json([
        "status" => "success",
        "msg" => "Actualizando producto"
      ]);

    }

    public function show(Request $request, $id){
      $producto = Producto::with(['tienda', 'minimo'])->findOrFail($id);
      return response()->json($producto);
    }

    public function main_products(Request $request){
      $orderBy = $request->input('order', 'updated_at');
      $sort = $request->input('sort', 'DESC');
      $paged = $request->input('items', 12);
      $estado = $request->input('estado', 'Activo');
      $query = MinimoPrecio::with(['producto' => function ($query) use ($estado, $request) {
        if ($estado != 'Todos') {
          $query->where('estado', $estado);
        }
      }, 'producto.tienda' => function($query){
        $query->select('nombre', 'id');
      }])->orderBy($orderBy, $sort);
      $query->whereHas('producto', function($q) use ($request){
        $q->where('estado', 'Activo');
        if ($request->has('tienda')) {
          $q->where('id_tienda', $request->input('tienda'));
        }
      });
      
      $results = $query->paginate($paged);
      return response()->json($results);
    }

    public function new_products(Request $request){
      $orderBy = $request->input('order', 'updated_at');
      $sort = $request->input('sort', 'DESC');
      $paged = $request->input('items', 12);
      $estado = $request->input('estado', 'Activo');
      $query = MinimoPrecio::with(['producto' => function ($query) use ($estado, $request) {
        if ($estado != 'Todos') {
          $query->where('estado', $estado);
        }
      }, 'producto.tienda' => function($query){
        $query->select('nombre', 'id');
      }])->orderBy($orderBy, $sort);
      $query->whereHas('producto', function($q) use ($request){
        $q->where('estado', 'Activo');
        if ($request->has('tienda')) {
          $q->where('id_tienda', $request->input('tienda'));
        }
        $q->whereIn('id', collect(DB::select('SELECT id_producto FROM (SELECT id_producto, COUNT(*) as cnt FROM historial_precios  GROUP BY id_producto HAVING cnt = 1) AS ids'))->pluck('id_producto'));
      });
      
      $results = $query->paginate($paged);
      return response()->json($results);
    }
}
