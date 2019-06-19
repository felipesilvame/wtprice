<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\HistorialPrecio;
use App\Models\MinimoPrecio;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use \Carbon\Carbon;

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
      $this->product->load('minimo');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      //check if needs to be updated
      if ((!$this->product->ultima_actualizacion) || $this->product->ultima_actualizacion->diffInMinutes() >= $this->product->intervalo_actualizacion) {
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
          $request = new \GuzzleHttp\Psr7\Request($tienda->method, $url, $tienda->headers);
          //$request = $client->get($url, ['headers' => $tienda->headers]);
        }else {
          $request = new \GuzzleHttp\Psr7\Request($tienda->method, $url);
        }
        $response = (string) $client->send($request)->getBody();
        $data = null;
        try {
          $data = json_decode($response, true);
        } catch (\Exception $e) {
          Log::warning("Producto id ".$this->product->id.": No se ha podido convertir la respuesta a JSON");
          $this->product->intentos_fallidos +=1 ;
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
          if ($tienda->campo_precio_oferta) {
            $campo_precio_oferta = '$data'.dot_to_array($tienda->campo_precio_oferta);
          }
          $campo_precio_tarjeta = null;
          if ($tienda->campo_precio_tarjeta) {
            $campo_precio_tarjeta = '$data'.dot_to_array($tienda->campo_precio_tarjeta);
          }
          try {
            if ($campo_precio_tarjeta) {
              $precio_tarjeta = eval("return ".$campo_precio_tarjeta.";");
              $this->product->precio_tarjeta = $precio_tarjeta;
            }
          } catch (\Exception $e) {
            //Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio de tarjeta");
            //$this->product->intentos_fallidos +=1 ;
          }
          try {
            if ($campo_precio_referencia) {
              $precio_referencia = eval("return ".$campo_precio_referencia.";");
              $this->product->precio_referencia = $precio_referencia;
            }
          } catch (\Exception $e) {
            Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio de referencia");
            $this->product->intentos_fallidos +=1 ;
          }
          try {
            if ($campo_precio_oferta) {
              $precio_oferta = eval("return ".$campo_precio_oferta.";");
              $this->product->precio_oferta = $precio_oferta;
            }
          } catch (\Exception $e) {
            //Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio oferta");
            //$this->product->intentos_fallidos +=1 ;
          }
          try {
            if ($campo_nombre_producto) {
              $nombre_producto = eval("return ".$campo_nombre_producto.";");
              $this->product->nombre = $nombre_producto;
            }
          } catch (\Exception $e) {
            Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el nombre del producto");
            $this->product->intentos_fallidos +=1 ;
          }
          // create historical data
          $historical = HistorialPrecio::create([
            'id_producto' => $this->product->id,
            'precio_referencia' => $this->product->precio_referencia,
            'precio_oferta' => $this->product->precio_oferta,
            'precio_tarjeta' => $this->product->precio_tarjeta,
            'fecha' => Carbon::now(),
          ]);

          if (count($this->product->getDirty()) > 0) {
            //save ultima actualizacion
          }
          $this->product->ultima_actualizacion = \Carbon\Carbon::now();

          // check and create minimum
          $minimo = $this->product->minimo;
          if (!$minimo) {
            $minimo = MinimoPrecio::create([
              'id_producto' => $this->product->id,
              'precio_referencia' => $this->product->precio_referencia,
              'precio_oferta' => $this->product->precio_oferta,
              'precio_tarjeta' => $this->product->precio_tarjeta,
              'fecha' => Carbon::now(),
            ]);
          } else {
            if ((!$minimo->precio_referencia) || $minimo->precio_referencia > $this->product->precio_referencia) {
              $minimo->precio_referencia = $this->product->precio_referencia;
            }
            if ((!$minimo->precio_oferta) || $minimo->precio_oferta > $this->product->precio_oferta) {
              $minimo->precio_oferta = $this->product->precio_oferta;
            }
            if ((!$minimo->precio_tarjeta) || $minimo->precio_tarjeta > $this->product->precio_tarjeta) {
              $minimo->precio_tarjeta = $this->product->precio_tarjeta;
            }
          }
          // TODO: create historical, check minimum, etc etc
          $minimo->save();
        }
        $this->product->save();
      }
    }
}
