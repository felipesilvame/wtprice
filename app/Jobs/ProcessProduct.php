<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\Producto;
use App\Models\HistorialPrecio;
use App\Models\Tienda;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Producto $product)
    {
      $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $client = new \GuzzleHttp\Client();
      $tienda = Tienda::findOrFail($this->product->id_tienda);
      $url = "";
      if ($tienda->protocolo) {
        $url .= $tienda->protocolo."://";
      }
      if ($tienda->prefix_api) {
        $url .= $tienda->prefix_api;
      }
      $url .= $this->product->sku;
      if ($tienda->prefix_api) {
        $url .= $tienda->suffix_api;
      }
      if ($tienda->headers) {
        $request = $client->get($url, ['headers' => $tienda->headers]);
      }else {
        $request = $client->get($url);
      }
      $response = (string) $request->getBody();
      $data = null;
      try {
        $data = json_decode($response, true);
      } catch (\Exception $e) {
        Log::warning("Producto id ".$this->product->id.": No se ha podido convertir la respuesta a JSON");
      }
      $precio_referencia = null;
      $precio_oferta = null;
      $precio_tarjeta = null;
      $nombre_producto = null;
      if ($data) {
        $campo_precio_referencia = null;
        if ($tienda->campo_precio_referencia) {
          $campo_precio_referencia = '$data'.dot_to_array($tienda->campo_precio_referencia);
        }
        $campo_nombre_producto = null;
        if ($tienda->campo_nombre_producto) {
          $campo_nombre_producto = '$data'.dot_to_array($tienda->campo_nombre_producto);
        }
        $campo_precio_oferta = null;
        if ($tienda->$campo_precio_oferta) {
          $campo_precio_oferta = '$data'.dot_to_array($tienda->$campo_precio_oferta);
        }
        $campo_precio_tarjeta = null;
        if ($tienda->$campo_precio_tarjeta) {
          $campo_precio_tarjeta = '$data'.dot_to_array($tienda->$campo_precio_tarjeta);
        }
        try {
          if ($campo_precio_referencia) {
            $precio_referencia = eval("return ".$campo_precio_referencia.";");
            $this->product->precio_referencia = $precio_referencia;
          }
        } catch (\Exception $e) {
          Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio de referencia");

        }
        try {
          if ($campo_precio_oferta) {
            $precio_oferta = eval("return ".$campo_precio_oferta.";");
            $this->product->precio_oferta = $precio_oferta;
          }
        } catch (\Exception $e) {
          Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio oferta");

        }
        try {
          if ($campo_nombre_producto) {
            $nombre_producto = eval("return ".$campo_nombre_producto.";");
            $this->product->nombre = $nombre_producto;
          }
        } catch (\Exception $e) {
          Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el nombre del producto");

        }
        if (count($this->product->getDirty()) > 0) {
          //save ultima actualizacion
        }

        // TODO: create historical, check minimum, etc etc
        $this->product->save();
      }
    }
}
