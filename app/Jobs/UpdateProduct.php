<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\HistorialPrecio;
use App\Models\MinimoPrecio;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\SospechaRata;
use App\Notifications\PushRata;
use App\Helpers\General\Rata;
use App\Helpers\General\Proxy;
use NotificationChannels\Twitter\TwitterChannel;
use App\Notifications\ProductoAhoraEnOferta;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;
use Notification;
use Carbon\Carbon;
use App\Helpers\General\Arr;

class UpdateProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $producto;
    protected $data;

    /**
     * Create a new job instance.
     * @param \App\Models\Producto $product
     * @param array $data
     * @return void
     */
    public function __construct(Producto $product, array $data)
    {
        $this->producto = $product;
        $this->producto->load('minimo');
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * En este punto, el algoritmo tiene la info del producto parseada
         * y almacenada localmente
         * en esta parte, se busca actualizar dicho producto en la BD
         * y notificar en caso de alerta rata
         */
        $data = $this->data;
        $product = $this->producto;
        $product->refresh();
        $old = $product->replicate();
        $tienda = $product->tienda;
        // Actualizar valores de producto
        foreach ($data as $key => $attr) {
            $product->$key = $attr;
        }
        $product->save();

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
          //no habia nunca antes un minimo, producto agregado

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
          if ($p_rata >= 0.65 && $p_rata_relativo >= 0.63 && !$product->alertado) {
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
          } else if ($p_rata >= 0.85 && $p_rata_relativo >= 0.5 && !$product->alertado){
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

    }
}
