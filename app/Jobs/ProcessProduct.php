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
use NotificationChannels\Twitter\TwitterChannel;
use App\Notifications\ProductoAhoraEnOferta;
use App\Helpers\General\Arr as ArrHelper;

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
      $this->product->refresh();
      //check if needs to be updated
      if ($this->product->intentos_fallidos >= 3) {
        $this->product->estado = "Detenido";
        $this->product->save();
      }else if ($this->product->estado === "Activo" && ((!$this->product->ultima_actualizacion) || $this->product->ultima_actualizacion->diffInMinutes() >= $this->product->intervalo_actualizacion)) {
        $client = new \GuzzleHttp\Client();
        $tienda = Tienda::findOrFail($this->product->id_tienda);
        $old = $this->product->replicate();
        $request = null;
        $response = null;
        $url = "";
        if ($tienda->protocolo) {
          $url .= $tienda->protocolo."://";
        }
        if ($tienda->prefix_api) {
          $url .= $tienda->prefix_api;
        }
        if ($tienda->method == "GET") {
          $url .= $this->product->sku;
        } else if ($tienda->method == "POST" && !$tienda->request_body_sku) {
          $url .= $this->product->sku;
        }
        if ($tienda->prefix_api) {
          $url .= $tienda->suffix_api;
        }
        $options = [];
        if ($tienda->headers) {
          $options['headers'] = $tienda->headers;
        }
        if ($tienda->request_body_sku && $tienda->method == "POST") {
          $options['body'] = json_encode([$tienda->request_body_sku => $this->product->sku]);
        }
        try {
          if ($tienda->method == "POST") {
            $response = $client->post($url, $options)->getBody()->getContents();
          }
          else {
            if ($tienda->headers) {
              $request = new \GuzzleHttp\Psr7\Request($tienda->method, $url, $tienda->headers);
            }
            else{
              $request = new \GuzzleHttp\Psr7\Request($tienda->method, $url);
            }

            $response = (string) $client->send($request)->getBody();
          }
        } catch (\Exception $e) {
          Log::error("No se ha podido obtener respuesta del servidor para el producto ".$this->product->id." Tienda ".$tienda->nombre);
          $this->product->intentos_fallidos += 1;
          $this->product->save();
        }
        if ((boolean) $response){
          $data = null;
          try {
            $data = json_decode($response, true);
          } catch (\Exception $e) {
            Log::warning("Producto id ".$this->product->id.": No se ha podido convertir la respuesta a JSON");
            $this->product->intentos_fallidos +=1;
            $this->product->save();
          }
          $precio_referencia = null;
          $precio_oferta = null;
          $precio_tarjeta = null;
          $nombre_producto = null;
          //quickfix: ArrHelper::get_pipo($data, $tienda->campo_nombre_producto) checks if the product exists.
          if ($data && ArrHelper::get_pipo($data, $tienda->campo_nombre_producto)) {
            if (!$this->product->url_compra) {
              try {
                if ($tienda->campo_slug_compra) {
                  $url_compra = ArrHelper::get_pipo($data, $tienda->campo_slug_compra);
                  if ($url_compra) {
                    $this->product->url_compra = $tienda->url_prefix_compra.$url_compra.$tienda->url_suffix_compra;
                    $this->product->save();
                  }
                } else if ((boolean)$tienda->url_prefix_compra) {
                  //try to guess url compra with the sku
                  $this->product->url_compra = $tienda->url_prefix_compra.(string)$this->product->sku.$tienda->url_suffix_compra;
                  $this->product->save();
                }
              } catch (\Exception $e) {
                //does nothing... maybe log this?
                Log::warning("Producto id".$this->product->id.": No se ha podido aplicar la url de compra");
              }
            }

            try {
              if ($tienda->campo_precio_tarjeta) {
                $p_tarjeta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($data, $tienda->campo_precio_tarjeta));
                if ($p_tarjeta) {
                  if ($p_tarjeta < 10000000) {
                    $this->product->precio_tarjeta = $p_tarjeta;
                  } else {
                    $this->product->intentos_fallidos += 1;
                  }
                }

              }
            } catch (\Exception $e) {
              //Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio de tarjeta");
              //$this->product->intentos_fallidos +=1 ;
            }
            try {
              if ($tienda->campo_precio_referencia) {
                $p_referencia = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($data, $tienda->campo_precio_referencia));
                if ($p_referencia && $p_referencia < 10000000) {
                  $this->product->precio_referencia = $p_referencia;
                } else {
                  $this->product->intentos_fallidos +=1;
                }
              }
            } catch (\Exception $e) {
              Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio de referencia");
              $this->product->intentos_fallidos +=1 ;
            }
            try {
              if ($tienda->campo_precio_oferta) {
                $p_oferta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($data, $tienda->campo_precio_oferta));
                if ($p_oferta) {
                  if ($p_oferta < 10000000) {
                    $this->product->precio_oferta = $p_oferta;
                    // code...
                  } else {
                    $this->product->intentos_fallidos +=1;
                  }
                }

              }
            } catch (\Exception $e) {
              //Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el precio oferta");
              //$this->product->intentos_fallidos +=1 ;
            }
            try {
              if ($tienda->campo_nombre_producto) {
                $this->product->nombre = ArrHelper::get_pipo($data, $tienda->campo_nombre_producto);
              }
            } catch (\Exception $e) {
              Log::warning("Producto id ".$this->product->id.": No se ha podido obtener el nombre del producto. Tienda ".$tienda->nombre);
              $this->product->nombre = '-';
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
            $this->product->intervalo_actualizacion = random_int(15, 45);

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
                if ((boolean)$minimo->precio_referencia && $minimo->precio_referencia > $this->product->precio_referencia) {
                  //check how much it changes
                  $percentage_rata = ((int)$minimo->precio_referencia-(int)$this->product->precio_referencia)/(float)$minimo->precio_referencia;
                  if ($percentage_rata >= 0.6) {
                    \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                    ->notify(new \App\Notifications\AlertaRata($this->product, $minimo->precio_referencia, $this->product->precio_referencia, $percentage_rata));
                  }
                }
                $minimo->precio_referencia = $this->product->precio_referencia;
              }
              if ((!$minimo->precio_oferta) || $minimo->precio_oferta > $this->product->precio_oferta) {
                if ((boolean)$minimo->precio_oferta && $minimo->precio_oferta > $this->product->precio_oferta) {
                  //check how much it changes
                  $percentage_rata = ((int)$minimo->precio_oferta-(int)$this->product->precio_oferta)/(float)$minimo->precio_oferta;
                  //compare between the oferta parameter with the reference...
                  $percentage_rata_relativo = ((int)$minimo->precio_referencia-(int)$this->product->precio_oferta)/(float)$minimo->precio_referencia;
                  if ($percentage_rata >= 0.4 && $percentage_rata_relativo >= 0.6) {
                    \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                    ->notify(new \App\Notifications\AlertaRata($this->product, $minimo->precio_oferta, $this->product->precio_oferta, $percentage_rata));
                  }
                } else if (!(boolean)$minimo->precio_oferta && !(boolean)$this->product->precio_tarjeta) {
                  //si antes no tenia precio oferta y ahora no tiene precio con tarjeta...
                  $percentage_rata = ((int)$this->product->precio_referencia-(int)$this->product->precio_oferta)/(float)$this->product->precio_referencia;
                  if ($percentage_rata >= 0.33) {
                    \Notification::route(TwitterChannel::class, '')
                      ->notify(new ProductoAhoraEnOferta($this->product, $this->product->precio_referencia, $this->product->precio_oferta, $percentage_rata));
                  }
                }
                $minimo->precio_oferta = $this->product->precio_oferta;
              }
              if ((!$minimo->precio_tarjeta) || $minimo->precio_tarjeta > $this->product->precio_tarjeta) {
                if ((boolean)$minimo->precio_tarjeta && $minimo->precio_tarjeta > $this->product->precio_tarjeta) {
                  //check how much it changes
                  $percentage_rata = ((int)$minimo->precio_tarjeta-(int)$this->product->precio_tarjeta)/(float)$minimo->precio_tarjeta;
                  //compare between the oferta parameter with the reference...
                  $percentage_rata_relativo = ((int)$minimo->precio_referencia-(int)$this->product->precio_tarjeta)/(float)$minimo->precio_referencia;
                  if ($percentage_rata >= 0.25 && $percentage_rata_relativo >= 0.7) {
                    \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                    ->notify(new \App\Notifications\AlertaRata($this->product, $minimo->precio_tarjeta, $this->product->precio_tarjeta, $percentage_rata, true));
                  }
                }else if (!(boolean)$minimo->precio_tarjeta) {
                  // if is not  already notified by rata before...
                  $percentage_rata = ((int)$this->product->precio_referencia-(int)$this->product->precio_tarjeta)/(float)$this->product->precio_referencia;
                  if ($percentage_rata >= 0.40) {
                    \Notification::route(TwitterChannel::class, '')
                      ->notify(new ProductoAhoraEnOferta($this->product, $this->product->precio_referencia, $this->product->precio_tarjeta, $percentage_rata, true));
                  }

                }

                $minimo->precio_tarjeta = $this->product->precio_tarjeta;
              }
            }
            // TODO: create historical, check minimum, etc etc
            $minimo->save();
          } else {
            Log::warning("Advertencia producto ".$this->product->id.": puede que ya no haya stock o sea descontinuado. Tienda".$tienda->nombre);
            $this->product->intentos_fallidos += 1;
          }
        }
      }
      $this->product->save();
    }
}
