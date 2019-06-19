<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Producto;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class ProductAdded extends Notification
{
    use Queueable;

    private $product;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Producto $product)
    {
      $this->product = $product;
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
       $product->load("tienda");
       $message = 'Nivel rata: :rat: \n';
       $message .= 'Una ratita ha agregado un producto nuevo y su precio a sido agregado\n';
       $message .= 'Nombre del producto: '.$product->nombre.'\n';
       $message .= 'Tienda: '.$product->tienda->nombre.'\n';
       $message .= 'Precio inicial: '.$product->precio_referencia.'\n';
       if ($product->precio_oferta) {
         $message .= 'Precio oferta: '.$product->precio_oferta.'\n';
       }
       if ($product->precio_tarjeta) {
         $message .= 'Precio tarjeta: '.$product->precio_tarjeta.'\n';
       }
       if ($product->intervalo_actualizacion) {
         $message .= 'El producto se actualizará cada '.$product->intervalo_actualizacion.' minutos\n';
       }

       return (new SlackMessage)
           ->from('iRata App', ':mouse:')
           ->to('#i-rata')
           ->image('https://banner2.kisspng.com/20180530/jea/kisspng-ratatouille-mouse-the-walt-disney-company-remy-rec-rat-mouse-5b0f70a4353a97.309237151527738532218.jpg')
           ->content("Una ratita ha agregado un producto nuevo y su precio a sido agregado")
           ->attachment(function ($attachment) use ($product){
             $attachment->fields([
               'Nivel rata' => ':rat:',
               'Nombre del producto' => $product->nombre,
               'Tienda' => $product->tienda->nombre,
               'Precio inicial' => $product->precio_referencia ?? '-',
               'Precio oferta' => $product->precio_oferta ?? '-',
               'Precio tarjeta' => $product->precio_tarjeta ?? '-',
               'Intervalo actualizacion' => $product->intervalo_actualizacion,
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
