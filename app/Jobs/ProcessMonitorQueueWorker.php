<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Producto;
use App\Jobs\ProcessProduct;
use \Carbon\Carbon;

/**
*
* This job makes the subsecuent jobs to update prices of different products
* first, it will search all products to be monitored
* then, will filter only the active ones
* then, check if need to be updated
* if needs to be updated, it will make the job to be updated
*
**/

class ProcessMonitorQueueWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // traer todos los productos, cuyo estado sea "activo"
        //$productos = Producto::whereEstado('Activo')->get();
        //optimizated query
        $productos = \App\Models\Producto::where('estado', 'Activo')
          ->where('actualizacion_pendiente', true)
          ->whereRaw('(TIMESTAMPDIFF(MINUTE, ultima_actualizacion, "'.now().'") >= intervalo_actualizacion)')
          ->orderBy('intervalo_actualizacion', 'DESC')->get();
        //all of these productos will update the status to actualizacion_pendiente = false;
        \App\Models\Producto::where('estado', 'Activo')
          ->where('actualizacion_pendiente', true)
          ->whereRaw('(TIMESTAMPDIFF(MINUTE, ultima_actualizacion, "'.now().'") >= intervalo_actualizacion)')
          ->orderBy('intervalo_actualizacion', 'DESC')->update(['actualizacion_pendiente' => false]);
        foreach ($productos as $key => $producto) {
          //check if needs to be updated
          if ((!$producto->ultima_actualizacion) || $producto->ultima_actualizacion->diffInMinutes() >= $producto->intervalo_actualizacion) {
            switch ($producto->tienda->nombre) {
              case 'Falabella':
                ProcessProduct::dispatch($producto)->onQueue('falabella');
                break;
              case 'ABCDin':
                ProcessProduct::dispatch($producto)->onQueue('abcdin');
                break;
              case 'Lider':
                ProcessProduct::dispatch($producto)->onQueue('lider');
                break;
              case 'Ripley':
                ProcessProduct::dispatch($producto)->onQueue('ripley');
                break;
              case 'Corona':
                ProcessProduct::dispatch($producto)->onQueue('corona');
                break;
              case 'Jumbo':
                ProcessProduct::dispatch($producto)->onQueue('jumbo');
                break;
              default:
                ProcessProduct::dispatch($producto);
                break;
            }
            //$producto->actualizacion_pendiente = false;
            //$producto->save();
          }
        }

    }
}
