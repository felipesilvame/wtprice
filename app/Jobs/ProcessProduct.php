<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\Device;
use App\Models\HistorialPrecio;
use App\Models\MinimoPrecio;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\SospechaRata;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use \Carbon\Carbon;
use NotificationChannels\Twitter\TwitterChannel;
use App\Notifications\ProductoAhoraEnOferta;
use App\Helpers\General\Arr as ArrHelper;
use Notification;
use App\Notifications\PushRata;
use App\Helpers\General\Rata;
use App\Helpers\General\Proxy;

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
      $product = $this->product;
      $product->refresh();
      //check if needs to be updated
      if ($product->intentos_fallidos >= 3) {
        $product->estado = "Detenido";
        $product->save();
      }else if ($product->estado === "Activo" && ((!$product->ultima_actualizacion) || $product->ultima_actualizacion->diffInMinutes() >= $product->intervalo_actualizacion)) {
        try {
          $client = new \GuzzleHttp\Client();
          $tienda = Tienda::findOrFail($product->id_tienda);
          if ($tienda->nombre === "Falabella") {
            //FALABELLA BLOCKS THE F*KING REQUESTS!!!!
            usleep(300000);
          }
          $old = $product->replicate();
          $request = null;
          $response = null;
          $url = "";
          $proxy = null;
          $regex = "/[^0-9]/";
          if ($tienda->nombre === 'Lider') {
            $regex = "/[^0-9.]/";
          }
          if ($tienda->protocolo) {
            $url .= $tienda->protocolo."://";
          }
          if ($tienda->prefix_api) {
            $url .= $tienda->prefix_api;
          }
          if ($tienda->method == "GET") {
            $url .= $product->sku;
          } else if ($tienda->method == "POST" && !$tienda->request_body_sku) {
            $url .= $product->sku;
          }
          if ($tienda->suffix_api) {
            $url .= $tienda->suffix_api;
          }
          $options = [];
          if ($tienda->headers) {
            $options['headers'] = $tienda->headers;
          }
          if ($tienda->request_body_sku && $tienda->method == "POST") {
            $options['body'] = json_encode([$tienda->request_body_sku => $product->sku]);
          }
          if($tienda->nombre === 'Ripley' && (boolean)env('APP_PROXY')) {
            //for ripley, add proxy
            $proxy = Proxy::random();
            $options['proxy'] = $proxy->url;
            $options['verify'] = false;
            $options['timeout'] = 15;
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

              $res = $client->send($request, $options);
              $response = (string) $res->getBody();
            }
          } catch(\GuzzleHttp\Exception\ConnectException $e) {
            if ($proxy) {
              Log::error("Error de proxy para el producto ".$product->id." Tienda ".$tienda->nombre);
              $proxy->intentos_fallidos +=1;
              if ($proxy->intentos_fallidos > 100) {
                $proxy->activo = false;
              }
              $proxy->save();
            } else Log::error("Error de Connection para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->ultima_actualizacion = now();
            $product->actualizacion_pendiente = true;
            $product->intervalo_actualizacion = random_int(5, 25);
            $product->save();
            return;
          } catch(\GuzzleHttp\Exception\ClientException $e){
            $status = $e->getResponse()->getStatusCode();
            Log::error("Error ".$status." para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->intentos_fallidos += 1;
            if ($status != 404) {
              if ($proxy) {
                $proxy->intentos_fallidos +=1;
                if ($proxy->intentos_fallidos > 100) {
                  $proxy->activo = false;
                }
                $proxy->save();
              }
              $product->ultima_actualizacion = now();
              $product->actualizacion_pendiente = true;
              $product->intervalo_actualizacion = random_int(5, 25);
            } else {
              $product->estado = 'Detenido';
            }
            $product->save();            
            return;
          } catch(\GuzzleHttp\Exception\RequestException $e){            
            if ($proxy) {
              Log::error("Error de proxy para el producto ".$product->id." Tienda ".$tienda->nombre);
              $proxy->intentos_fallidos +=1;
              if ($proxy->intentos_fallidos > 100) {
                $proxy->activo = false;
              }
              $proxy->save();
            } else Log::error("Error de Request para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->ultima_actualizacion = now();
            $product->actualizacion_pendiente = true;
            $product->intervalo_actualizacion = random_int(5, 25);
            $product->save();
            return;
          } catch (\Exception $e) {
            Log::error("No se ha podido obtener respuesta del servidor para el producto ".$product->id." Tienda ".$tienda->nombre);
            $product->intentos_fallidos += 1;
            //maybe try later? 
            $product->ultima_actualizacion = now();
            $product->actualizacion_pendiente = true;
            $product->intervalo_actualizacion = random_int(5, 25);
            $product->save();            
            return; 
          }
          if ((boolean) $response){
            $data = null;
            try {
              $data = json_decode($response, true);
            } catch (\Exception $e) {
              Log::error("Producto id ".$product->id.": No se ha podido convertir la respuesta a JSON");
              $product->actualizacion_pendiente = true;
              $product->intentos_fallidos +=1;
              $product->save();
              return;
            }
            $precio_referencia = null;
            $precio_oferta = null;
            $precio_tarjeta = null;
            $nombre_producto = null;
            //quickfix: ArrHelper::get_pipo($data, $tienda->campo_nombre_producto) checks if the product exists.
            if ($data && ArrHelper::get_pipo($data, $tienda->campo_nombre_producto)) {
              // 22-12-2019: updated url img
              try {
                if ($tienda->campo_imagen_url) {
                  // try to get url img
                  $imagen_url = ArrHelper::get_pipo($data, $tienda->campo_imagen_url);
                  // Alto harcoding por aca
                  if ($tienda->nombre === 'Lider') {
                    $product->imagen_url = 'https://images.lider.cl/wmtcl?source=url[file:/productos/'.$product->sku.$imagen_url.']&sink';
                  } elseif ($tienda->nombre === 'Falabella') {
                    $product->imagen_url = $tienda->campo_imagen_url.$product->sku.'_1';
                  } else {
                    $product->imagen_url = $imagen_url;
                  }
                }
              } catch (\Exception $e) {

              }
              if (!$product->url_compra) {
                try {
                  if ($tienda->campo_slug_compra) {
                    $url_compra = ArrHelper::get_pipo($data, $tienda->campo_slug_compra);
                    if ($url_compra) {
                      $product->url_compra = $tienda->url_prefix_compra.$url_compra.$tienda->url_suffix_compra;
                      $product->save();
                    }
                  } else if ((boolean)$tienda->url_prefix_compra) {
                    //try to guess url compra with the sku
                    $product->url_compra = $tienda->url_prefix_compra.(string)$product->sku.$tienda->url_suffix_compra;
                    $product->save();
                  }
                } catch (\Exception $e) {
                  //does nothing... maybe log this?
                  Log::warning("Producto id".$product->id.": No se ha podido aplicar la url de compra");
                }
              }

              try {
                if ($tienda->campo_precio_tarjeta) {
                  $p_tarjeta = (integer)preg_replace($regex,'',(string)ArrHelper::get_pipo($data, $tienda->campo_precio_tarjeta));
                  if ($p_tarjeta) {
                    if ($p_tarjeta < 10000000) {
                      $product->precio_tarjeta = $p_tarjeta;
                    } else {
                      $product->intentos_fallidos += 1;
                    }
                  }

                }
              } catch (\Exception $e) {
                //Log::warning("Producto id ".$product->id.": No se ha podido obtener el precio de tarjeta");
                //$product->intentos_fallidos +=1 ;
              }
              try {
                if ($tienda->campo_precio_referencia) {
                  $p_referencia = (integer)preg_replace($regex,'',(string)ArrHelper::get_pipo($data, $tienda->campo_precio_referencia, 0));
                  if (!$p_referencia) {
                    //hardcoded for falabella
                    $p_referencia = (integer)preg_replace($regex,'',(string)ArrHelper::get_pipo($data, 'state.product.prices:label,,formattedLowestPrice'));
                  }
                  if ($p_referencia && $p_referencia < 10000000) {
                    $product->precio_referencia = $p_referencia;
                    $product->intentos_fallidos = 0;
                  } else {
                    $product->intentos_fallidos +=1;
                  }
                }
              } catch (\Exception $e) {
                Log::warning("Producto id ".$product->id.": No se ha podido obtener el precio de referencia");
                $product->intentos_fallidos +=1 ;
                throw $e;
              }
              try {
                if ($tienda->campo_precio_oferta) {
                  $p_oferta = (integer)preg_replace($regex,'',(string)ArrHelper::get_pipo($data, $tienda->campo_precio_oferta, 0));
                  if (!$p_oferta) {
                    //hardcoded for falabella
                    $p_oferta = (integer)preg_replace($regex,'',(string)ArrHelper::get_pipo($data, 'state.product.prices:label,(Oferta),formattedLowestPrice'));
                  }
                  if ($p_oferta) {
                    if ($p_oferta < 10000000) {
                      $product->precio_oferta = $p_oferta;
                      // code...
                    } else {
                      $product->intentos_fallidos +=1;
                    }
                  }

                }
              } catch (\Exception $e) {
                //Log::warning("Producto id ".$product->id.": No se ha podido obtener el precio oferta");
                //$product->intentos_fallidos +=1 ;
              }
              try {
                if ($tienda->campo_nombre_producto) {
                  $product->nombre = mb_strimwidth(ArrHelper::get_pipo($data, $tienda->campo_nombre_producto),0, 250, '...');
                }
              } catch (\Exception $e) {
                Log::warning("Producto id ".$product->id.": No se ha podido obtener el nombre del producto. Tienda ".$tienda->nombre);
                $product->nombre = '-';
                $product->intentos_fallidos +=1;
                throw $e;
              }
              if ($tienda->nombre === 'Falabella') {
                // complete sospecha if its registered
                $sospecha = SospechaRata::where('id_tienda', $tienda->id)->where('sku', $product->sku)->whereNull('nombre_producto')->whereNull('precio_referencia')->first();
                if ($sospecha){
                  //fullfill the subsecuent fields
                  $sospecha->nombre_producto = $product->nombre;
                  $sospecha->precio_referencia = $product->precio_referencia;
                  $sospecha->precio_oferta = $product->precio_oferta;
                  $sospecha->precio_tarjeta = $product->precio_tarjeta;
                  $sospecha->url_imagen = $product->imagen_url;
                  $sospecha->save();
                }
              }

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

              $product->ultima_actualizacion = Carbon::now();
              if ($tienda->nombre === "Falabella" || $tienda->nombre === "Linio") {
                $product->intervalo_actualizacion = random_int(10, 45);
              } elseif ($tienda->nombre === 'Lider'){
		            $product->intervalo_actualizacion = random_int(5, 10);
	            } elseif ($tienda->nombre === 'Ripley'){
                $product->intervalo_actualizacion = random_int(25, 65);
              } else {
                $product->intervalo_actualizacion = random_int(10, 40);
              }

              //13-05-2020: se agrega flag de stock para aquellos que tengan disponible la funcion
              if ($tienda->campo_disponible) {
                $product->disponible = Rata::getAvailableFlag($data, $tienda->campo_disponible, $tienda->nombre);
              }

              if ($tienda->campo_stock) {
                $product->stock = Rata::getStock($data, $tienda->campo_stock, $tienda->nombre);
              }

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
                //no habia nunca antes un minimo, producto agregado
                try {
                  //\Notification::route('slack', env('SLACK_NUEVA_URL'))
                  //->notify(new \App\Notifications\ProductAdded($product));
                } catch (\Exception $e) {
                  //throw $th;
                }

                //Hold up! maaaaaaaaaybe you want to check fast if the very first price was wrong,
                // so, just check if has precio_oferta or precio_tarjeta
                [$p_rata, $p_rata_relativo] = Rata::calculaSelf($product);
                if ($p_rata >= 0.87 && !$product->alertado) {
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
                if ((!$minimo->precio_referencia) || $minimo->precio_referencia > $product->precio_referencia) {
                  $minimo->precio_referencia = $product->precio_referencia;
                }
                if ((!$minimo->precio_oferta) || $minimo->precio_oferta > $product->precio_oferta) {
                  $minimo->precio_oferta = $product->precio_oferta;
                }
                if ((!$minimo->precio_tarjeta) || $minimo->precio_tarjeta > $product->precio_tarjeta) {
                  $minimo->precio_tarjeta = $product->precio_tarjeta;
                }
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
              // TODO: create historical, check minimum, etc etc
              $minimo->save();
            } else {
              Log::warning("Advertencia producto ".$product->id.": puede que ya no haya stock o sea descontinuado. Tienda".$tienda->nombre);
              $product->ultima_actualizacion = now();
              $product->intervalo_actualizacion = random_int(5, 25);
              $product->intentos_fallidos += 1;
              throw new \Exception("puede que ya no haya stock o sea descontinuado", 1);

            }
          }

        } catch (\Exception $e) {
          $product->actualizacion_pendiente = true;
          $product->save();
          return;
        }
      }
      $product->actualizacion_pendiente = true;
      $product->save();
    }
}
