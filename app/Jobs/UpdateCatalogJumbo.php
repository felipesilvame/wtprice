<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;

/*
*
* Si estas leyendo esto, pido de antemano disculpas,
* ha sido un tremendo chombo querer actualizar de forma automatica los
* catálogos de cada tienda de forma dinamica, por lo que esta parte
* está hardcodeada y variará dependiendo de la tienda, por lo tanto,
* si se incorpora otra tienda además de las creadas en el comando seed:tiendas
* se tendrá que realizar su propio parser
*
*/

class UpdateCatalogJumbo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $categories;
    private $method;
    private $protocol;
    private $uri;
    private $page_start;
    private $tienda;
    private $total_pages_field;
    private $results_field;
    private $sku_field;
    private $current_page_field;
    private $client;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->categories = [
        '/298/302/300/', //celulares
        '/298/302/309/', //computacion y accesorios
        '/298/303/431/', //televisores
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'api.smdigital.cl:8443/v0/cl/jumbo/vtex/front/dev/proxy/api/v1/catalog_system/pub/products/search/';
      $this->page_start = 0;
      $this->page_end = 0;
      $this->total_pages = 0;
      $this->tienda = null;
      $this->total_pages_field = null;
      $this->results_field = null;
      $this->current_page_field = null;
      $this->sku_field = 'linkText';
      $this->client = new \GuzzleHttp\Client();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      if ($this->Tienda()) {
        Log::debug("Tienda found. ID: ".$this->tienda->id);
        // perform the query

        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $this->page_start = 0;
            $from = 0;
            $to = 49;
            Log::debug("getting first page for category $category");
            $this->makeRequest($this->page_start, $to, $category);
            //total pages is fullfilled
            Log::debug("total pages for category: $this->total_pages");
            if ($this->page_start < $this->total_pages) {
              for ($pages = $this->page_start+1; $pages <= $this->total_pages ; $pages++) {
                Log::debug("making request for page $pages of $this->total_pages for cat $category");
                $from = $to+1;
                $to += 50;
                $this->makeRequest($from, $to, $category);
              }
            }
          } catch (\Exception $e) {
            Log::error("Error obteniendo info de ".$this->tienda->nombre);
          }

        }

      }
    }

    /*
    *
    * Check if the tienda exists in database,
    * return true and $this->tienda is object,
    * else return false and $this->tienda is null
    *
    */
    private function Tienda(){
      $tienda = \App\Models\Tienda::whereNombre('Jumbo')->first();
      if ($tienda) {
        $this->tienda = $tienda;
        return true;
      } else return false;
    }

    private function makeRequest($from,$to,$category){
      //get response
      $url = $this->protocol.'://'.$this->uri;
      //add page and category in url;
      $url .= "?fq=C:$category&_from=$from&_to=$to";
      $response = null;
      $data = null;
      $options = [];
      $options['headers'] = ['x-api-key' => 'IuimuMneIKJd3tapno2Ag1c1WcAES97j'];
      try {
        $res = $this->client->get($url, $options);
        $response = $res->getBody()->getContents();
        $this->total_pages = (int)ceil((float)explode("/",$res->getHeader('resources')[0])[1]/(float)50);
      } catch (\Exception $e) {
        Log::warning("No se ha obtenido respuesta satisfactoria de parte del request".$this->tienda->nombre);
        throw new \Exception("Error Processing Request", 1);
      }
      if ((boolean) $response) {
        try {
          $data = json_decode($response, true);
        } catch (\Exception $e) {
          Log::warning("No se ha podido parsear la respuesta de ".$this->tienda->nombre);
          throw new \Exception("Error Processing Request", 1);
        }
        //got response, lets check how many pages has this request
        //$this->total_pages = (int)ceil((float)ArrHelper::get($data, $this->total_pages_field, 0)/(float)20);
        //check now where am i
        //$this->page_start = (int)ceil((float)ArrHelper::get($data, $this->current_page_field, 0)/(float)50) +1;
        //extract all sku for tienda and put them into database
        $results = $data;//ArrHelper::get($data, $this->results_field, []);
        foreach ($results as $key => $row) {
          $sku = (string)ArrHelper::get($row, $this->sku_field, null);
          if (!$sku) continue;
          $producto = \App\Models\Producto::where('id_tienda', $this->tienda->id)->where('sku', $sku)->first();
          if ((boolean) $producto) continue;
          //create new producto
          $producto  = \App\Models\Producto::create([
            'id_tienda' => $this->tienda->id,
            'sku' => $sku,
            'nombre' => $sku,
            'intervalo_actualizacion' => random_int(1,20)
          ]);
        }
      }
    }
}