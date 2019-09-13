<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\SlashCommand\Jobs\SlashCommandResponseJob;
use App\Notifications\SlackOferta;
use App\Models\Producto;
use App\Models\MinimoPrecio;

class SendSlackOfertas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $comando;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($comando)
    {
        $this->comando = $comando;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $response = $this->comando;
      $array_data = explode(' ',$response);
      $orderBy = 'updated_at';
      $sort = 'DESC';
      $paged = 10;
      $tienda = \Arr::get($array_data, '0', null);
      if ($tienda) {
        $tienda = \App\Models\Tienda::where('nombre', $tienda)->first();
      }
      $estado = $request->input('estado', 'Activo');
      $query = MinimoPrecio::with(['producto' => function ($query) use ($estado) {
        if ($estado != 'Todos') {
          $query = $query->where('estado', $estado);
        }
      }, 'producto.tienda' => function($query){
        $query->select('nombre', 'id');
      }])->orderBy($orderBy, $sort);
      if ($tienda) {
        $query->whereHas('producto', function($q) use ($tienda){
          $q->where('id_tienda', $tienda->id);
        });
      }
      $results = $query->take($paged)->get();

      try {
        \Notification::route('slack', env('SLACK_OFERTA_URL'))
        ->notify(new SlackOferta($results));
      } catch (\Exception $e) {

      }

    }
}
