<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateCatalogFalabella extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:falabella';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create catalog for falabella';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $this->info('llenando catalogo');
      $this->info('Buscando tienda falabella...');
      $falabella = \App\Models\Tienda::whereNombre('Falabella')->first();
      if ($falabella) {
        $dir = resource_path().'/catalogs/falabella';
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $key => $file) {
          $this->info('llenando '.$file);
          $path = resource_path().'/catalogs/falabella/'.$file;
          $payload = json_decode(file_get_contents($path), true);
          $catalog = \Arr::get($payload, 'state.resultList');
          $sku = [];
          foreach ($catalog as $key => $row) {
            $p = null;
            $part_number = \Arr::get($row, 'productId');
            //add to the database if not exists
            $p = \App\Models\Producto::where('id_tienda',$falabella->id)->where('sku', $part_number)->first();
            if (!$p) {
              $p = \App\Models\Producto::create([
                'id_tienda' => $falabella->id,
                'sku' => $part_number,
                'nombre' => (string)$key,
                'intervalo_actualizacion' => random_int(1,20)
              ]);
              //sleep - was too heavy :(
              usleep(5000);
            }
          }
        }
        $this->info('Hecho!');
      }
    }
}
