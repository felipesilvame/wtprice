<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Producto;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class AlertaRata extends Notification implements ShouldQueue
{
    use Queueable;

    private $product;
    private $precio_antes;
    private $precio_despues;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Producto $product, int $precio_antes, int $precio_despues)
    {
        $this->product = $product;
        $this->product->load('tienda');
        $this->precio_antes = $precio_antes;
        $this->precio_despues = $precio_despues;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
    * Get the Slack representation of the notification.
    *
    * @param  mixed  $notifiable
    * @return \Illuminate\Notifications\Messages\SlackMessage
    */
    public function toSlack($notifiable)
    {
      $product = $this->product;
      $precio_antes = $this->precio_antes;
      $precio_despues = $this->precio_despues;

       return (new SlackMessage)
           ->from('iRata App', ':mouse:')
           ->to('#i-rata')
           ->image('https://banner2.kisspng.com/20180530/jea/kisspng-ratatouille-mouse-the-walt-disney-company-remy-rec-rat-mouse-5b0f70a4353a97.309237151527738532218.jpg')
           ->content("ALERTA RATA!!!!!")
           ->attachment(function ($attachment) use ($product, $precio_antes, $precio_despues){
             $attachment->title($product->nombre, $product->url_compra)
              ->fields([
                 'Nivel rata' => ':rat::rat::rat::rat::rat:',
                 'Nombre del producto' => $product->nombre,
                 'Tienda' => $product->tienda->nombre,
                 'Precio antes' => $precio_antes ?? '-',
                 'Precio ahora' => $precio_despues ?? '-',
               ]);
           });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
