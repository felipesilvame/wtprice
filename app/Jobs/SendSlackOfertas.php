<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\SlashCommand\Jobs\SlashCommandResponseJob;

class SendSlackOfertas extends SlashCommandResponseJob
{
    //use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
      $this
         ->respondToSlack("Here is your response: {$response}")
         ->send();
    }
}
