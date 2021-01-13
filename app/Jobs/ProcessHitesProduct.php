<?php

namespace App\Jobs;

use App\Models\HistorialPrecio;
use App\Models\MinimoPrecio;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use \Carbon\Carbon;
use NotificationChannels\Twitter\TwitterChannel;
use App\Notifications\ProductoAhoraEnOferta;
use App\Helpers\General\Arr as ArrHelper;
use App\Helpers\General\Rata;
use Illuminate\Support\Str;

class ProcessHitesProduct implements ShouldQueue
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
      try {
        $product = $this->product;
        $product->refresh();
        //check if needs to be updated
        if ($product->intentos_fallidos >= 3) {
          $product->estado = "Detenido";
          $product->save();
        }else if ($product->estado === "Activo" && ((!$product->ultima_actualizacion) || $product->ultima_actualizacion->diffInMinutes() >= $product->intervalo_actualizacion)) {
          $client = new \Goutte\Client();
          $tienda = Tienda::findOrFail($product->id_tienda);
          $old = $product->replicate();
          $request = null;
          $response = null;
          $url = "";
          if ($tienda->protocolo) {
            $url .= $tienda->protocolo."://";
          }
          if ($tienda->prefix_api) {
            $url .= $tienda->prefix_api;
          }
          $url .= $product->sku;
          if ($tienda->suffix_api) {
            $url .= $tienda->suffix_api;
          }
          try {
            $crawler = $client->request($tienda->method, $url);
            if ($client->getResponse()->getStatusCode() !== 200) throw new \Exception("Not Valid Request", 1);
            //
          } catch (\Exception $e) {
            Log::error("No se ha podido obtener respuesta del servidor para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->intentos_fallidos += 1;
            //maybe try later? 
            $product->ultima_actualizacion = now();
            $product->intervalo_actualizacion = random_int(5, 25);
            $product->actualizacion_pendiente = true;
            $product->save();
            return;
          }
          $precio_referencia = null;
          $precio_oferta = null;
          $precio_tarjeta = null;
          $nombre_producto = null;

          //check nombre producto, cancel if fails
          try {
             $nombre_producto = trim($crawler->filter($tienda->campo_nombre_producto)->first()->text());
             $product->nombre = mb_strimwidth($nombre_producto, 0, 250, '...');
          } catch (\Exception $e) {
            Log::error("No se ha podido obtener el nombre para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->ultima_actualizacion = now();
            $product->intentos_fallidos += 1;
            $product->actualizacion_pendiente = true;
            $product->save();
            return;
          }
          // 22-12-2019: updated url img
          if (!$product->imagen_url) {
            try {
              if ($tienda->campo_imagen_url) {
                // Alto harcoding por aca
                $product->imagen_url = trim($crawler->filter($tienda->campo_imagen_url)->first()->attr('src'));
              }
            } catch (\Exception $e) {

            }

          }

          try {
            // 13-01-2020: updated hites method
            try {
              if ($tienda->campo_precio_referencia) {
                $_precio = $crawler->filter($tienda->campo_precio_referencia)->first()->attr('content');
                $precio_referencia = (integer)preg_replace('/[^0-9]/','',$_precio);
              }
            } catch (\Throwable $th) {
              //throw $th;
            }
            try {
              if ($tienda->campo_precio_oferta) {
                $_precio = $crawler->filter($tienda->campo_precio_oferta)->first()->attr('content');
                $precio_referencia = (integer)preg_replace('/[^0-9]/','',$_precio);
              }
            } catch (\Throwable $th) {
              //throw $th;
            }
            try {
              if ($tienda->campo_precio_tarjeta) {
                $_precio = $crawler->filter($tienda->campo_precio_tarjeta)->first()->attr('content');
                $precio_referencia = (integer)preg_replace('/[^0-9]/','',$_precio);
              }
            } catch (\Throwable $th) {
              //throw $th;
            }
            // 15-05-2020: if normal price not listed, use oferta intead
            if (!$precio_referencia || $precio_referencia === '') {
              $precio_referencia = $precio_oferta;
            }

          } catch (\Exception $e) {
              Log::error("No se pudo obtener la lista de precios para el producto ".$product->id." Tienda ".$tienda->nombre);
              $product->intentos_fallidos += 1;
              $product->actualizacion_pendiente = true;
              $product->ultima_actualizacion = now();
              $product->save();
              return;
          }
          // check precio referencia, cancel if fails
          try {
            $p_referencia = (integer)preg_replace('/[^0-9]/','',$precio_referencia);
            if ($p_referencia && $p_referencia < 10000000) {
              $product->precio_referencia = $p_referencia;
              $product->intentos_fallidos = 0;
            } else {
              //antes habia precio referencia, ahora no
              $product->intentos_fallidos +=1;
            }
          } catch (\Exception $e) {
            Log::error("No se ha podido obtener el precio para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->intentos_fallidos += 1;
            $product->actualizacion_pendiente = true;
            $product->ultima_actualizacion = now();
            $product->save();
            return;
          }
          //get url compra
          try {
            if ($tienda->campo_slug_compra) {
              $url_compra = trim($crawler->filter($tienda->campo_slug_compra)->first()->text());
              $product->url_compra = $url_compra;
            } else {
              $product->url_compra = $url;
            }
          } catch (\Exception $e) {
          }
          //get precio oferta if any
          try {
            if ($tienda->campo_precio_oferta) {
              $p_oferta = (integer)preg_replace('/[^0-9]/','',$precio_oferta);
              if ($p_oferta && $p_oferta < 10000000) {
                $product->precio_oferta = $p_oferta;
                $product->intentos_fallidos = 0;
              } else {
                $product->precio_oferta = null;
              }
              $product->save();
            }
          } catch (\Exception $e) {
            // si no se pudo obtener el precio oferta de un producto, y antes tenia... se elimina
            $product->precio_oferta = null;
          }
          //get precio tarjeta if any
          try {
            if ($tienda->campo_precio_tarjeta) {
              $p_tarjeta = (integer)preg_replace('/[^0-9]/','',$precio_tarjeta);
              if ($p_tarjeta && $p_tarjeta < 10000000) {
                $product->precio_tarjeta = $p_tarjeta;
                $product->intentos_fallidos = 0;
              } else {
                $product->precio_tarjeta = null;
              }
              $product->save();
            }
          } catch (\Exception $e) {
            // si no se pudo obtener el precio tarjeta de un producto, y antes tenia... se elimina
            $product->precio_tarjeta = null;
          }
          // create historical data
          if($old->precio_referencia !== $product->precio_referencia ||
              $old->precio_oferta !== $product->precio_oferta ||
              $old->precio_tarjeta !== $product->precio_tarjeta
              ){
                $historical = HistorialPrecio::create([
                'id_producto' => $product->id,
                'precio_referencia' => $product->precio_referencia,
                'precio_oferta' => $product->precio_oferta,
                'precio_tarjeta' => $product->precio_tarjeta,
                'fecha' => Carbon::now(),
                ]);
              }

          $product->ultima_actualizacion = \Carbon\Carbon::now();
          $product->intervalo_actualizacion = random_int(15, 100);
          //el producto ha actualizado correctamente su precio, por lo tanto, tiene hora de ultima actualizacion
          //se puede volver a encolar
          $product->actualizacion_pendiente = true;
          $product->save();

          // check and create minimum
          $minimo = $product->minimo;
          if (!$minimo) {
            $minimo = MinimoPrecio::create([
            'id_producto' => $product->id,
            'precio_referencia' => $product->precio_referencia,
            'precio_oferta' => $product->precio_oferta,
            'precio_tarjeta' => $product->precio_tarjeta,
            'fecha' => Carbon::now(),
            ]);
            // no hay minimo,
            //Hold up! maaaaaaaaaybe you want to check fast if the very first price was wrong,
            // so, just check if has precio_oferta or precio_tarjeta
            [$p_rata, $p_rata_relativo] = Rata::calculaSelf($product);
            if ($p_rata >= 0.75 && !$product->alertado) {
              if ($product->precio_referencia >= 490000) {
                try {
                  Rata::alertaCoipo($product, $minimo, $p_rata);
                } catch (\Throwable $th) {
                  //throw $th;
                }
              } else if ($old->precio_referencia >= 195000){
                // ALERTA RATA LVL 2: ESTO ES UNA RATA
                try {
                  Rata::alertaRata($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
              } else {
                // ALERTA RATA LVL 1: UN HAMSTER
                try {
                  Rata::alertaHamster($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
              }
              \App\Models\AlertaRata::create([
                'id_tienda' => $tienda->id,
                'id_producto' => $product->id,
                'precio_antes' => $old->precio_referencia,
                'precio_oferta_antes' => $old->precio_oferta,
                'precio_tarjeta_antes' => $old->precio_tarjeta,
                'precio_ahora' => $product->precio_referencia,
                'precio_oferta_ahora' => $product->precio_oferta,
                'precio_tarjeta_ahora' => $product->precio_tarjeta,
                'porcentaje_rata' => $p_rata,
                'porcentaje_rata_relativo' => $p_rata_relativo,
                'nombre_tienda' => $tienda->nombre,
                'nombre_producto' => $product->nombre,
                'url_compra' => $product->url_compra,
                'url_imagen' => $product->imagen_url,
              ]);
              $product->alertado = true;
              $product->save();
            }
          } else {
            // 15-04-2020. added static method for comparison
            [$p_rata, $p_rata_relativo] = Rata::calculaRata($product, $minimo);
            if ((boolean)$product->precio_referencia && (!$minimo->precio_referencia || $minimo->precio_referencia > $product->precio_referencia)) {
              $minimo->precio_referencia = $product->precio_referencia;
            }
            if ((boolean)$product->precio_oferta && (!$minimo->precio_oferta || $minimo->precio_oferta > $product->precio_oferta)) {
              $minimo->precio_oferta = $product->precio_oferta;
            }
            if ((boolean)$product->precio_tarjeta && (!$minimo->precio_tarjeta || $minimo->precio_tarjeta > $product->precio_tarjeta)) {
              $minimo->precio_tarjeta = $product->precio_tarjeta;
            }
            $minimo->save();
            // Es hora de discriminar
            if ($p_rata >= 0.60 && $p_rata_relativo >= 0.63 && !$product->alertado) {
              if ($old->precio_referencia >= 490000) {
                // ALERTA RATA LVL 3: ESTA WEA ES UN COIPO
                try {
                  Rata::alertaCoipo($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
              } else if ($old->precio_referencia >= 195000){
                // ALERTA RATA LVL 2: ESTO ES UNA RATA
                try {
                  Rata::alertaRata($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
              } else {
                // ALERTA RATA LVL 1: UN HAMSTER
                try {
                  Rata::alertaHamster($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
              }
              \App\Models\AlertaRata::create([
                'id_tienda' => $tienda->id,
                'id_producto' => $product->id,
                'precio_antes' => $old->precio_referencia,
                'precio_oferta_antes' => $old->precio_oferta,
                'precio_tarjeta_antes' => $old->precio_tarjeta,
                'precio_ahora' => $product->precio_referencia,
                'precio_oferta_ahora' => $product->precio_oferta,
                'precio_tarjeta_ahora' => $product->precio_tarjeta,
                'porcentaje_rata' => $p_rata,
                'porcentaje_rata_relativo' => $p_rata_relativo,
                'nombre_tienda' => $tienda->nombre,
                'nombre_producto' => $product->nombre,
                'url_compra' => $product->url_compra,
                'url_imagen' => $product->imagen_url,
              ]);
              $product->alertado = true;
              $product->save();
            } else if ($p_rata >= 0.85 && !$product->alertado){
              if ($minimo->precio_referencia >= 10000) {
                // RATA LVL 3: COIPO
                try {
                  Rata::alertaCoipo($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
                
              } else {
                // RATA LVL 2: RATA
                try {
                  Rata::alertaRata($product, $minimo, $p_rata);
                } catch (\Exception $e) {
                  //throw $th;
                }
                
              }
              \App\Models\AlertaRata::create([
                'id_tienda' => $tienda->id,
                'id_producto' => $product->id,
                'precio_antes' => $old->precio_referencia,
                'precio_oferta_antes' => $old->precio_oferta,
                'precio_tarjeta_antes' => $old->precio_tarjeta,
                'precio_ahora' => $product->precio_referencia,
                'precio_oferta_ahora' => $product->precio_oferta,
                'precio_tarjeta_ahora' => $product->precio_tarjeta,
                'porcentaje_rata' => $p_rata,
                'porcentaje_rata_relativo' => $p_rata_relativo,
                'nombre_tienda' => $tienda->nombre,
                'nombre_producto' => $product->nombre,
                'url_compra' => $product->url_compra,
                'url_imagen' => $product->imagen_url,
              ]);
              $product->alertado = true;
              $product->save();
            }
          }   
          //save again por si acaso xd xd xd
          $product->actualizacion_pendiente = true;
          $product->save();

        }
      } catch (\Exception $e) {
        //do not nothing xdd
        $product->actualizacion_pendiente = true;
        $product->save();
      }

    }
}
