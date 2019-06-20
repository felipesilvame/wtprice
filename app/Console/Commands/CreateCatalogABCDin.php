<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateCatalogABCDin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:abcdin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create catalog for abcdin';

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
      $this->info('Buscando tienda abcdin...');
      $abcdin = \App\Models\Tienda::whereNombre('ABCDin')->first();
      if ($abcdin) {
        $dir = resource_path().'/catalogs/abcdin';
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $key => $file) {
          $this->info('llenando '.$file);
          $path = resource_path().'/catalogs/abcdin/'.$file;
          $payload = json_decode(file_get_contents($path), true);
          $catalog = \Arr::get($payload, 'data.products');
          $sku = [];
          foreach ($catalog as $key => $row) {
            $p = null;
            $part_number = \Arr::get($row, 'partNumber');
            //add to the database if not exists
            $p = \App\Models\Producto::where('id_tienda',$abcdin->id)->where('sku', $part_number)->first();
            if (!$p) {
              $p = \App\Models\Producto::create([
                'id_tienda' => $abcdin->id,
                'sku' => $part_number,
                'nombre' => (string)$key,
                'intervalo_actualizacion' => random_int(1,35)
              ]);
            }
          }
        }
        $this->info('Hecho!');
      }
    }
}
