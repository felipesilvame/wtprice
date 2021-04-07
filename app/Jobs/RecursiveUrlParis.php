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

class RecursiveUrlParis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $url;
    private $start;
    private $sz;
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
    private $elements;
    private $category;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $category, $discount, $webhook)
    {
        $this->url = $url;
        $this->discount = $discount;
        $this->category = $category;
        $this->protocol = 'https';
        $this->method = 'GET';
        $this->url = $url;
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
        $this->webhook = $webhook;
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
        $category = $this->category;
        if (!$tienda) return null;
        usleep(300000);
        try {
            $url = $this->url;
            //Log::debug('Getting url: '.$url);
            $crawler = $client->request($this->method, $url);
            if ($client->getResponse()->getStatusCode() !== 200) throw new \Exception("Not Valid Request", 1);
        } catch (\Throwable $th) {
            //throw $th;
            return;
        }
        try {
            $data = collect([]);
            $items = $crawler->filter('li.js-product-position,div.onecolumn');
            $data = $data->merge($this->filterElements($items));
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
                        ]);
                    } else {
                        \App\Models\Producto::create([
                            'id_tienda' => $tienda->id,
                            'sku' => $row['sku'],
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
                        ]);
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::error("Error obteniendo info de ".$tienda->nombre);
            throw $th;
        }
    }

    /**
     * 
     */
    private function filterElements($items){
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
            try {
                $p_normal = (integer)preg_replace('/[^0-9]/','', trim($node->filter($this->precio_referencia_field)->first()->filter('span')->first()->text()));
            } catch (\Throwable $th) {
                //throw $th;
            }
            try {
                $p_oferta = (integer)preg_replace('/[^0-9]/','', trim($node->filter($this->precio_oferta_field)->first()->filter('span')->first()->text()));
            } catch (\Throwable $th) {
                //throw $th;
            }
            try {
                $p_tarjeta = (integer)preg_replace('/[^0-9]/','', trim($node->filter($this->precio_tarjeta_field)->first()->filter('span')->first()->text()));
            } catch (\Throwable $th) {
                //throw $th;
            }
            try {
                $discount = preg_replace("/[^0-9]/", "", $node->filter($this->discount_field)->first()->text());
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
