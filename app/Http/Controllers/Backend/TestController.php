<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Jobs\ProcessProduct;


/**
 * Class DashboardController.
 */
class TestController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function checkProduct(Request $request, $id)
    {
      $producto = Producto::findOrFail($id);
      ProcessProduct::dispatch($producto);
      return response()->json(["status" => "success"]);
    }
}
