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

class ProcessLaPolarProduct implements ShouldQueue
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
            $product->actualizacion_pendiente = true;
            $product->save();
            throw $e;
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
            $product->intentos_fallidos += 1;
            $product->actualizacion_pendiente = true;
            $product->save();
            throw $e;
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
          // check precio referencia, cancel if fails
          try {
            $precio_referencia = trim($crawler->filter($tienda->campo_precio_referencia)->first()->text());
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
            $product->save();
            throw $e;
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
              $precio_oferta = trim($crawler->filter($tienda->campo_precio_oferta)->first()->text());
              $p_oferta = (integer)preg_replace('/[^0-9]/','',$precio_oferta);
              if ($p_oferta && $p_oferta < 10000000) {
                $product->precio_oferta = $p_oferta;
                $product->intentos_fallidos = 0;
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
              $precio_tarjeta = trim($crawler->filter($tienda->campo_precio_tarjeta)->first()->text());
              $p_tarjeta = (integer)preg_replace('/[^0-9]/','',$precio_tarjeta);
              if ($p_tarjeta && $p_tarjeta < 10000000) {
                $product->precio_tarjeta = $p_tarjeta;
                $product->intentos_fallidos = 0;
              }
              $product->save();
            }
          } catch (\Exception $e) {
            // si no se pudo obtener el precio tarjeta de un producto, y antes tenia... se elimina
            $product->precio_oferta = null;
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
            try {
              \Notification::route('slack', env('SLACK_NUEVA_URL'))
                ->notify(new \App\Notifications\ProductAdded($product));
            } catch (\Exception $e) {

            }


          } else {
            if ((!$minimo->precio_referencia) || $minimo->precio_referencia > $product->precio_referencia) {
              if ((boolean)$minimo->precio_referencia && $minimo->precio_referencia > $product->precio_referencia) {
                //check how much it changes
                $percentage_rata = ((int)$minimo->precio_referencia-(int)$product->precio_referencia)/(float)$minimo->precio_referencia;
                if ($percentage_rata >= 0.7) {
                  try {
                    \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                    ->notify(new \App\Notifications\AlertaRata($product, $minimo->precio_referencia, $product->precio_referencia, $percentage_rata));
                  } catch (\Exception $e) {
                    Log::error('No se ha podido enviar notificacion para producto '.$product->id);
                  }


                }
              }
              $minimo->precio_referencia = $product->precio_referencia;
            }
            if ((!$minimo->precio_oferta) || $minimo->precio_oferta > $product->precio_oferta) {
              if ((boolean)$minimo->precio_oferta && $minimo->precio_oferta > $product->precio_oferta) {
                //check how much it changes
                $percentage_rata = ((int)$minimo->precio_oferta-(int)$product->precio_oferta)/(float)$minimo->precio_oferta;
                //compare between the oferta parameter with the reference...
                $percentage_rata_relativo = ((int)$minimo->precio_referencia-(int)$product->precio_oferta)/(float)$minimo->precio_referencia;
                if ($percentage_rata >= 0.55 && $percentage_rata_relativo >= 0.6) {
                  try {
                    \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                    ->notify(new \App\Notifications\AlertaRata($product, $minimo->precio_oferta, $product->precio_oferta, $percentage_rata));
                  } catch (\Exception $e) {
                    Log::error("No se ha podido enviar notificacion para el producto $product->id");
                  }
                }
              } else if (!(boolean)$minimo->precio_oferta && (boolean)$product->precio_oferta &&!(boolean)$product->precio_tarjeta) {
                //si antes no tenia precio oferta y ahora no tiene precio con tarjeta...
                $percentage_rata = ((int)$product->precio_referencia-(int)$product->precio_oferta)/(float)$product->precio_referencia;
                if ($percentage_rata >= 0.50) {
                  try {
                    \Notification::route('slack', env('SLACK_OFERTA_URL'))
                    ->notify(new ProductoAhoraEnOferta($product, $product->precio_referencia, $product->precio_oferta, $percentage_rata));
                  } catch (\Exception $e) {
                    Log::error("No se ha podido enviar notificacion para el producto $product->id");
                  }

                }
              }
              $minimo->precio_oferta = $product->precio_oferta;
            }
            if ((!$minimo->precio_tarjeta) || $minimo->precio_tarjeta > $product->precio_tarjeta) {
              if ((boolean)$minimo->precio_tarjeta && $minimo->precio_tarjeta > $product->precio_tarjeta) {
                //check how much it changes
                $percentage_rata = ((int)$minimo->precio_tarjeta-(int)$product->precio_tarjeta)/(float)$minimo->precio_tarjeta;
                //compare between the oferta parameter with the reference...
                $percentage_rata_relativo = ((int)$minimo->precio_referencia-(int)$product->precio_tarjeta)/(float)$minimo->precio_referencia;
                if ($percentage_rata >= 0.40 && $percentage_rata_relativo >= 0.7) {
                  try {
                    \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                    ->notify(new \App\Notifications\AlertaRata($product, $minimo->precio_tarjeta, $product->precio_tarjeta, $percentage_rata, true));
                  } catch (\Exception $e) {
                    Log::error("No se ha podido enviar notificacion para el producto $product->id");
                  }

                }
              }else if ((!$minimo->precio_tarjeta) && (boolean)$product->precio_tarjeta) {
                // if is not  already notified by rata before...
                // antes no tenia precio tarjeta y ahora si tiene precio con tarjeta...
                $percentage_rata = ((int)$product->precio_referencia-(int)$product->precio_tarjeta)/(float)$product->precio_referencia;
                if ($percentage_rata >= 0.60) {
                  try {
                    \Notification::route('slack', env('SLACK_OFERTA_URL'))
                    ->notify(new ProductoAhoraEnOferta($product, $product->precio_referencia, $product->precio_tarjeta, $percentage_rata, true));
                  } catch (\Exception $e) {
                    Log::error("No se ha podido enviar notificacion para el producto $product->id");
                  }

                }

              }

              $minimo->precio_tarjeta = $product->precio_tarjeta;
            }
          }
          // TODO: create historical, check minimum, etc etc
          $minimo->save();
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
