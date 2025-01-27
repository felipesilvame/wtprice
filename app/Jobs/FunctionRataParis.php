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

class FunctionRataParis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $categories;
    public $method;
    public $protocol;
    public $uri;
    public $page_start;
    public $tienda;
    public $total_elements_field;
    public $per_page_field;
    public $results_field;
    public $sku_field;
    public $nombre_field;
    public $precio_referencia_field;
    public $precio_oferta_field;
    public $precio_tarjeta_field;
    public $buy_url_field;
    public $image_url_field;
    public $current_page_field;
    public $client;
    public $discount;
    public $discount_field;
    public $webhook;
    public $suffix;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $category, string $discount, string $webhook)
    {
        $this->categories = $category;
        $this->discount = (int)$discount;
        $this->protocol = 'https';
        $this->method = 'GET';
        $this->uri = 'www.paris.cl/';
        $this->suffix = '?prefn1=seller&prefv1=Paris.cl&srule=price-low-to-high&start=0&sz=60&format=ajax';
        $this->page_start = 1;
        $this->total_pages = 1;
        $this->tienda = null;
        $this->discount_field = 'span.discount-badge,span.discount-2';
        $this->sku_field = 'div.product-tile';
        $this->nombre_field = 'span.ellipsis_text';
        $this->precio_referencia_field = 'div.price-normal span[itemprop=price], div.item-price.offer-price.price-tc.default-price';
        $this->precio_oferta_field = 'div.price-internet span[itemprop=price], div.item-price.offer-price.price-tc.default-price';
        $this->precio_tarjeta_field = 'div.price-tc.cencosud-price';
        $this->buy_url_field = 'meta[itemprop=url]';
        $this->image_url_field = 'img.img-prod';
        $this->webhook = config($webhook);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \Goutte\Client();
        $tienda = null;
        $total_pages = 0;
        $tienda = \App\Models\Tienda::whereNombre('Paris')->first();
        $_d = (string)$this->discount;
        if (!$tienda) return null;
        foreach ($this->categories as $key => $category) {
            $start = 0;
            $total_pages = 0;
            $pages = 1;
            $epp = 90; // elements per page
            usleep(1000000);
            try {
                $url = $this->protocol.'://'.$this->uri;
                $url .= $category.$this->suffix;
                //Log::debug('Getting url: '.$url);
                $crawler = $client->request($this->method, $url);
                if ($client->getResponse()->getStatusCode() !== 200) throw new \Exception("Not Valid Request", 1);
                $elements = (int)preg_replace('/[^0-9]/','', trim($crawler->filter('div.total-products')->first()->filter('span')->first()->text()));
                if ($elements > 0) {
                    $pages = ceil($elements / (float)$epp);
                }
                Log::debug("Tienda {$tienda->nombre} - Category {$category} - Total Pages: {$pages}");
            } catch (\Exception $e) {
                Log::warning("FunctionRataParis: No se ha obtenido respuesta satisfactoria de parte del request".$tienda->nombre);
                throw new \Exception("Error Processing Request", 1);
            }
            $data = collect([]);
            try {
                $items = $crawler->filter('li.js-product-position,div.onecolumn');
            } catch (\Throwable $th) {
                continue;
            }
            // Getted data, parse each
            $data = $data->merge($this->filterElements($items));
            Log::debug("Tienda {$tienda->nombre} - Category {$category} - Total Elements: {$data->count()}");
            $start += $epp;
            for ($i=1; $i < $pages; $i++) {
                try {
                    $url = $this->protocol.'://'.$this->uri.$category."?srule=price-low-to-high&start=$start&sz=$epp&format=ajax";
                    RecursiveUrlParis::dispatch($url, $category,$_d, $this->webhook);
                } catch (\Throwable $th) {
                    // ignoring page
                }
                $start += $epp;
            }
            try {
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
                            'categoria' => $category,
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
                                    'categoria' => $category,
                                ]);
                            }
                    }
                }
                foreach ($data as $key => $row) {
                    if ($row){
                        // Update product
                        $producto = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $row['sku'])->first();
                        if ($producto){
                            /* This will not update because consumes much performance I/O
                            
                            UpdateProduct::dispatch($producto, [
                                'nombre' => $row['nombre'],
                                'imagen_url' => $row['img'],
                                'url_compra' => $row['url'],
                                'precio_referencia' => $row['precio_normal'],
                                'precio_oferta' => $row['precio_oferta'],
                                'precio_tarjeta' => $row['precio_tarjeta'],
                                'ultima_actualizacion' => now(),
                                'actualizacion_pendiente' => 1,
                                'categoria' => $category,
                                'estado' => 'Activo',
                                'disponible' => true,
                            ]); */
                        } else {
                            \App\Models\Producto::create([
                                'id_tienda' => $tienda->id,
                                'nombre' => $row['nombre'],
                                'sku' => $row['sku'],
                                'imagen_url' => $row['img'],
                                'url_compra' => $row['url'],
                                'precio_referencia' => $row['precio_normal'],
                                'precio_oferta' => $row['precio_oferta'],
                                'precio_tarjeta' => $row['precio_tarjeta'],
                                'ultima_actualizacion' => now(),
                                'actualizacion_pendiente' => 1,
                                'categoria' => $category,
                                'estado' => 'Activo',
                                'disponible' => true,
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

    /**
     * 
     */
    public function filterElements($items){
        return $items->each(function($node){
            $nombre = null;
            $img = null;
            $url = null;
            $sku = null;
            $p_normal = null;
            $p_oferta = null;
            $p_tarjeta = null;
            $discount = 0;
            try {
                $nombre = $node->filter($this->nombre_field)->first()->text();
                $sku = $node->filter($this->sku_field)->attr('data-itemid');
                $url = $node->filter($this->buy_url_field)->first()->attr('content');
                $img = $node->filter($this->image_url_field)->first()->attr('data-src');
            } catch (\Throwable $th) {
                //nothing
            }
            $arreglo_precios = $node->filter('.price__text,.price__text-sm')->each(function ($node){ return $node->text(); });
            try {
                if (count($arreglo_precios) == 3){
                    $p_tarjeta = (integer)preg_replace('/[^0-9]/','', trim($arreglo_precios[0]));
                    $p_oferta = (integer)preg_replace('/[^0-9]/','', trim($arreglo_precios[1]));
                    $p_normal = (integer)preg_replace('/[^0-9]/','', trim($arreglo_precios[2]));
                } elseif (count($arreglo_precios) == 2){
                    $p_oferta = (integer)preg_replace('/[^0-9]/','', trim($arreglo_precios[0]));
                    $p_normal = (integer)preg_replace('/[^0-9]/','', trim($arreglo_precios[1]));
                } elseif (count($arreglo_precios) == 1){
                    $p_normal = (integer)preg_replace('/[^0-9]/','', trim($arreglo_precios[0]));
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
            try {
                $discount = preg_replace("/[^0-9]/", "", $node->filter('.price__badge')->first()->text());
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
                    'precio_tarjeta' => $p_tarjeta > 0 ? $p_tarjeta : null,
                    'descuento' => (int)$discount,
                ];
                return $res;
            } else {
                return null;
            }
        });
    }
}
