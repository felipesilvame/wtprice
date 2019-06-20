<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateCatalogLinio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:linio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create catalog for linio';

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
      $this->info('Buscando tienda linio...');
      $linio = \App\Models\Tienda::whereNombre('Linio')->first();
      if ($linio) {
        $dir = resource_path().'/catalogs/linio';
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $key => $file) {
          $this->info('llenando '.$file);
          $path = resource_path().'/catalogs/linio/'.$file;
          $payload = json_decode(file_get_contents($path), true);
          $catalog = \Arr::get($payload, 'searchResult.original.products');
          $sku = [];
          foreach ($catalog as $key => $row) {
            $p = null;
            $part_number = \Arr::get($row, 'path');
            $part_number = substr($part_number, strrpos($part_number, '/') + 1);
            //add to the database if not exists
            $p = \App\Models\Producto::where('id_tienda',$linio->id)->where('sku', $part_number)->first();
            if (!$p) {
              $p = \App\Models\Producto::create([
                'id_tienda' => $linio->id,
                'sku' => $part_number,
                'nombre' => (string)$key,
                'intervalo_actualizacion' => random_int(1,20)
              ]);
            }
          }
        }
        $this->info('Hecho!');
      }
    }
}
