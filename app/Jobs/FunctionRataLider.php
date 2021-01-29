<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\General\Arr as ArrHelper;
use App\Models\SospechaRata;
use App\Helpers\General\Rata;

class FunctionRataLider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $categories;
    private $method;
    private $protocol;
    private $uri;
    private $page_start;
    private $tienda;
    private $total_elements_field;
    private $per_page_field;
    private $discount_field;
    private $results_field;
    private $sku_field;
    private $nombre_field;
    private $precio_referencia_field;
    private $precio_oferta_field;
    private $precio_tarjeta_field;
    private $buy_url_field;
    private $image_url_field;
    private $current_page_field;
    private $client;
    private $discount;
    private $webhook;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $category, string $discount, string $webhook)
    {
        $this->categories = $category;
        $this->discount = $discount;
        $this->protocol = 'https';
        $this->method = 'POST';
        $this->uri = 'buysmart-bff-production.lider.cl/buysmart-bff/category';
        $this->page_start = 1;
        $this->total_pages = 1;
        $this->tienda = null;
        $this->total_elements_field = '';
        $this->per_page_field = '';
        $this->discount_field = 'discount';
        $this->results_field = 'products';
        $this->current_page_field = '';
        $this->sku_field = 'sku';
        $this->nombre_field = 'displayName';
        $this->precio_referencia_field = 'price.BasePriceReference';
        $this->precio_oferta_field = 'price.BasePriceSales';
        $this->precio_tarjeta_field = 'price.BasePriceTLMC';
        $this->buy_url_field = 'https://www.lider.cl/catalogo/product/sku/';
        $this->image_url_field = '';
        $this->webhook = config($webhook);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        $tienda = null;
        $total_pages = 0;
        $tienda = \App\Models\Tienda::whereNombre('Lider')->first();
        $_d = (integer)$this->discount;
        if (!$tienda) return null;
        foreach ($this->categories as $key => $category) {
            $response = null;
            $data = null;
            try {
                $page_start = 1;
                $url = $this->protocol.'://'.$this->uri;
                Log::debug('Getting url: '.$url);
                $body = [
                    "categories" => $category,
                    "page" => $page_start,
                    "facets" => [],
                    "sortBy" => "discount_desc",
                    "hitsPerPage" => 100
                ];
                $headers = [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
                    'Accept'     => 'application/json',
                    'Origin' => 'https://www.lider.cl',
                    'Referer' => 'https://www.lider.cl/'
                ];
                $options = [
                    'json' => $body,
                    'headers' => $headers,
                ];
                try {
                    $response = $client->post($url, $options)->getBody()->getContents();
                } catch (\Throwable $th) {
                    Log::warning("FunctionRataLider: No se ha obtenido respuesta satisfactoria de parte del request ".$tienda->nombre);
                    throw new \Exception("Error Processing Request", 1);
                }
                if ((boolean)$response){
                    try {
                        $data = json_decode($response, true);
                    } catch (\Exception $e) {
                        Log::warning("FunctionRataLider: No se ha podido parsear la respuesta de ".$tienda->nombre);
                        throw new \Exception("Error Processing Request", 1);
                    }
                    $results = ArrHelper::get($data, $this->results_field, []);
                    // FunctionRataLider es distinto, porque aqui se verifica el porcentaje de descuento
                    // antes de agregar
                    foreach ($results as $key => $row) {
                        if ((integer)ArrHelper::get($row, $this->discount_field, -1) >= $_d){
                            // Si es mayor el porcentaje de descuento al umbral dado
                            $sku = (string)ArrHelper::get($row, $this->sku_field, null);
                            if (!$sku) continue;
                            $sospecha = \App\Models\SospechaRata::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                            if (!$sospecha){
                                //create new sospecha
                                $nombre = null;
                                $precio_referencia = null;
                                $precio_oferta = null;
                                $precio_tarjeta = null;
                                $url_compra = null;
                                try {
                                    $nombre = (string)ArrHelper::get($row, $this->nombre_field, null);
                
                                    $precio_referencia = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_referencia_field, 0));
                                    if (!$precio_referencia) $precio_referencia = null;
                                    $precio_oferta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_oferta_field, 0));
                                    if (!$precio_oferta) $precio_oferta = null;
                                    $precio_tarjeta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_tarjeta_field, 0));
                                    if (!$precio_tarjeta) $precio_tarjeta = null;
                                    $url_compra = $this->buy_url_field.$sku;
                                    $imagen_url = 'https://images.lider.cl/wmtcl?source=url[file:/productos/'.$sku.'a.jpg]&sink';
                                } catch (\Throwable $th) {
                                    throw $th;
                                }
                                $sospecha  = \App\Models\SospechaRata::create([
                                    'id_tienda' => $tienda->id,
                                    'sku' => $sku,
                                    'nombre_producto' => $nombre,
                                    'nombre_tienda' => $tienda->nombre,
                                    'precio_referencia' => $precio_referencia,
                                    'precio_oferta' => $precio_oferta ? $precio_oferta : null,
                                    'precio_tarjeta' => $precio_tarjeta ? $precio_tarjeta : null,
                                    'categoria' => $category,
                                    'url_compra' => $url_compra,
                                    'url_imagen' => $imagen_url,
                                ]);
                                try {
                                    Rata::sospechaRata($sospecha, $this->webhook);
                                } catch (\Throwable $th) {
                                    //nothing
                                }
                                $producto_original = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                                if (!$producto_original) {
                                    //create new producto
                                    $producto_original  = \App\Models\Producto::create([
                                        'id_tienda' => $tienda->id,
                                        'sku' => $sku,
                                        'nombre' => $nombre,
                                        'estado' => 'Activo',
                                        'precio_referencia' => $sospecha->precio_referencia,
                                        'precio_oferta' => $sospecha->precio_oferta,
                                        'precio_tarjeta' => $sospecha->precio_tarjeta,
                                        'intervalo_actualizacion' => 10,
                                        'ultima_actualizacion' => now(),
                                        'categoria' => $category,
                                    ]);
                                } else {
                                    $producto_original->estado = "Activo";
                                    $producto_original->intentos_fallidos = 0;
                                    $producto_original->actualizacion_pendiente = 1;
                                    $producto_original->intervalo_actualizacion = 10;
                                    $producto_original->ultima_actualizacion = now();
                                    $producto_original->save();
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error obteniendo info de ".$tienda->nombre);
                throw $e;
            }
        }
    }
}
