<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\General\Arr;
use App\Models\SospechaRata;
use App\Helpers\General\Rata;
use App\Jobs\UpdateProduct;

class FunctionRataEntel implements ShouldQueue
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
    private $discount_field;
    private $webhook;
    private $suffix;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $discount, string $webhook)
    {
        $this->discount = (int)$discount;
        $this->protocol = 'https';
        $this->method = 'GET';
        $this->uri = 'miportal.entel.cl/catalogo/celulares';
        $this->suffix = '?No=0&Nrpp=9990&Ns=listPrice|0&contentPath=/pages/storechilepp/catalogo/celulares&eIdx=6&sIdx=1&subPath=main[1]&format=json-rest';
        $this->suffix .= '&_='.(string)floor(microtime(true));
        $this->page_start = 1;
        $this->total_pages = 1;
        $this->tienda = null;
        $this->discount_field = 'span.discount-badge';
        $this->sku_field = '';
        $this->nombre_field = 'a.link.product-name--bundle';
        $this->precio_referencia_field = 'span.price-item.list.strike-through.only-normal-price,span.price-item.list.strike-through';
        $this->precio_oferta_field = 'span.price-item.sales.strike-through,span.price-item.sales';
        $this->precio_tarjeta_field = 'span.price-item.hites-price';
        $this->buy_url_field = 'a.link.product-name--bundle';
        $this->image_url_field = 'img.tile-image';
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
        $tienda = \App\Models\Tienda::whereNombre('Entel')->first();
        $_d = (string)$this->discount;
        if (!$tienda) return null;
        try {
            $url = $this->protocol.'://'.$this->uri;
            $url .= $this->suffix;
            //Log::debug('Getting url: '.$url);
            $total_pages = 0;
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
                'Accept'     => 'application/json',
                'Origin' => 'https://www.entel.cl',
                'Referer' => 'https://www.entel.cl',
                'X-Requested-With' => 'XMLHttpRequest'
            ];
            $res = $client->request($this->method, $url, ['headers' => $headers]);
            if ($res->getStatusCode() !== 200) throw new \Exception("Not Valid Request", 1);
        } catch (\Exception $e) {
            Log::warning("FunctionRataEntel: No se ha obtenido respuesta satisfactoria de parte del request".$tienda->nombre);
            throw $e;
        }
        $data = collect([]);
        try {
            $items = Arr::get(json_decode($res->getBody(), true), 'response.records');
        } catch (\Throwable $e) {
            Log::warning("FunctionRataEntel: No se ha obtenido respuesta satisfactoria de parte del request".$tienda->nombre);
            throw $e;
        }
        // Getted data, parse each
        try {
            foreach($items as $node ) {
                $nombre = null;
                $img = null;
                $url = null;
                $sku = null;
                $p_normal = null;
                $p_oferta = null;
                $p_tarjeta = null;
                $discount = 0;
                try {
                    $nombre = Arr::get($node, 'attributes.displayName.0', null);
                    $sku = Arr::get($node, 'attributes.sku.0', null);
                    $url = 'https://miportal.entel.cl'.Arr::get($node, 'attributes.seoUrl.0', null);
                    $img = 'https://miportal.entel.cl'.Arr::get($node, 'attributes.productImage.0', null);
                } catch (\Throwable $th) {
                    //nothing
                }
                try {
                    $p_normal = (integer)preg_replace('/[^0-9]/','', trim((integer)Arr::get($node, 'attributes.skuListPrice.0', 0)));
                } catch (\Throwable $th) {
                    //throw $th;
                }
                try {
                    $p_oferta = (integer)preg_replace('/[^0-9]/','', trim((integer)Arr::get($node, 'attributes.productListPrice.0', 0)));
                } catch (\Throwable $th) {
                    //throw $th;
                }
                try {
                    //$p_tarjeta = (integer)preg_replace('/[^0-9]/','', trim($node->filter($this->precio_tarjeta_field)->first()->text()));
                } catch (\Throwable $th) {
                    //throw $th;
                }
                try {
                    //$discount = preg_replace("/[^0-9]/", "", $node->filter($this->discount_field)->first()->text());
                    // Descuento se calcula de forma manual
                    if ($p_normal && $p_oferta) {
                        $discount = (integer)((float)($p_normal - $p_oferta) / $p_normal * 100.0);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
                if ($nombre && $sku && $p_normal && $img) {
                    $res = [
                        'nombre' => $nombre,
                        'img' => $img,
                        'url' => $url,
                        'sku' => $sku,
                        'precio_normal' => $p_normal > 0 ? $p_normal : null,
                        'precio_oferta' => $p_oferta > 0 ? $p_oferta : null,
                        'precio_tarjeta' => null,
                        'descuento' => (int)$discount,
                    ];
                    $data->push($res);
                }
            }
            // filtered is the products that has the % 
            $filtered = $data->filter(function($item, $key){
                if ($item && isset($item['descuento'])) {
                    return $item['descuento'] >= $this->discount;
                } else return false;
            });
            foreach ($filtered->values() as $key => $item) {
                $sospecha = \App\Models\SospechaRata::where('id_tienda', $tienda->id)->where('sku', $item['sku'])->first();
                if (!$sospecha){
                    $sospecha  = \App\Models\SospechaRata::create([
                        'id_tienda' => $tienda->id,
                        'sku' => $item['sku'],
                        'nombre_producto' => $item['nombre'],
                        'nombre_tienda' => $tienda->nombre,
                        'precio_referencia' => $item['precio_normal'],
                        'precio_oferta' => $item['precio_oferta'] ? $item['precio_oferta'] : null,
                        'precio_tarjeta' => $item['precio_tarjeta'] ? $item['precio_tarjeta'] : null,
                        'categoria' => 'celulares',
                        'url_compra' => $item['url'],
                        'url_imagen' => $item['img'],
                    ]);
                    try {
                        Rata::sospechaRata($sospecha, $this->webhook);
                    } catch (\Throwable $th) {
                        //nothing
                    }
                    $producto_original = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $item['sku'])->first();
                        if (!$producto_original) {
                            //create new producto
                            $producto_original  = \App\Models\Producto::create([
                                'id_tienda' => $tienda->id,
                                'sku' => $item['sku'],
                                'nombre' => $item['nombre'],
                                'estado' => 'Activo',
                                'precio_referencia' => $sospecha->precio_referencia,
                                'precio_oferta' => $sospecha->precio_oferta,
                                'precio_tarjeta' => $sospecha->precio_tarjeta,
                                'intervalo_actualizacion' => 10,
                                'ultima_actualizacion' => now(),
                                'categoria' => 'celulares',
                            ]);
                        }
                }
            }
        } catch (\Throwable $th) {
            Log::error("Error obteniendo info de ".$tienda->nombre);
            throw $th;
        }
    }
}
