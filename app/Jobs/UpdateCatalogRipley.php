<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;

class UpdateCatalogRipley implements ShouldQueue
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
        '945205',
        '947841',
        '945153',
        '945155',
        '945157',
        '945158',
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.ripley.cl/wcs/resources/store/10151/productview/bySearchTerm/';
      $this->page_start = 0;
      $this->total_pages = 0;
      $this->tienda = null;
      $this->total_pages_field = 'recordSetTotal';
      $this->results_field = 'CatalogEntryView';
      $this->current_page_field = 'recordSetStartNumber';
      $this->sku_field = 'partNumber';
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
            $this->page_start = 1;
            Log::debug("getting first page for category $category");
            $this->makeRequest($this->page_start, $category);
            //total pages is fullfilled
            Log::debug("total pages for category: $this->total_pages");
            if ($this->page_start < $this->total_pages) {
              for ($pages = $this->page_start+1; $pages <= $this->total_pages ; $pages++) {
                Log::debug("making request for page $pages of $this->total_pages for cat $category");
                $this->makeRequest($pages, $category);
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
      $tienda = \App\Models\Tienda::whereNombre('Ripley')->first();
      if ($tienda) {
        $this->tienda = $tienda;
        return true;
      } else return false;
    }

    private function makeRequest($page, $category){
      //get response
      $url = $this->protocol.'://'.$this->uri;
      //add page and category in url;
      $url .= "*?categoryId=$category&pageSize=50&pageNumber=$page&facet=xquantity:({1+*}+1)";
      $response = null;
      $data = null;
      try {
        $response = $this->client->get($url)->getBody()->getContents();
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
        $this->total_pages = (int)ceil((float)ArrHelper::get($data, $this->total_pages_field, 0)/(float)50);
        //check now where am i
        //$this->page_start = (int)ceil((float)ArrHelper::get($data, $this->current_page_field, 0)/(float)50) +1;
        //extract all sku for tienda and put them into database
        $results = ArrHelper::get($data, $this->results_field, []);
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
