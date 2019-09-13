<?php

namespace App\Http\Controllers\Api;

use App\Models\Producto;
use Illuminate\Http\Request;
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
}
