<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateCatalogRipley extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:ripley';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate the catalog for ripley';

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
      $this->info('Buscando tienda ripley...');
      $ripley = \App\Models\Tienda::whereNombre('Ripley')->first();
      if ($ripley) {
        $dir = resource_path().'/catalogs/ripley';
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $key => $file) {
          $this->info('llenando '.$file);
          $path = resource_path().'/catalogs/ripley/'.$file;
          $payload = json_decode(file_get_contents($path), true);
          $catalog = \Arr::get($payload, 'CatalogEntryView');
          $sku = [];
          foreach ($catalog as $key => $row) {
            $p = null;
            $part_number = \Arr::get($row, 'partNumber');
            //add to the database if not exists
            $p = \App\Models\Producto::where('id_tienda',$ripley->id)->where('sku', $part_number)->first();
            if (!$p) {
              $p = \App\Models\Producto::create([
                'id_tienda' => $ripley->id,
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
