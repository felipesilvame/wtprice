<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\MinimoPrecio;
use Illuminate\Http\Request;

/**
 * Class HomeController.
 */
class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
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
      if ($request->has('tienda')) {
        $query->whereHas('producto', function($q) use ($request){
          $q->where('id_tienda', $request->input('tienda'));
        });
      }
      $results = $query->paginate($paged);
      //dd($results->toArray());
        return view('frontend.index')->with([
          'items' => $results
        ]);
    }
}
