<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SlackOfertas extends Notification
{
    use Queueable;
    private $ofertas;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($ofertas)
    {
        $this->ofertas = $ofertas;
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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

    /**
    * Get the Slack representation of the notification.
    *
    * @param  mixed  $notifiable
    * @return \Illuminate\Notifications\Messages\SlackMessage
    */
    public function toSlack($notifiable)
    {
      $ofertas = $this->ofertas;


       return (new SlackMessage)
           ->from('iRata App', ':mouse:')
           ->to('#i-rata-ofertas')
           ->content("Producto en oferta")
           ->attachment(function ($attachment) use ($product, $precio_antes, $precio_despues, $tarjeta){
             $attachment->title($product->nombre, $product->url_compra)
              ->fields([
                 'Nivel rata' => ':rat::rat:',
                 'Nombre del producto' => $product->nombre,
                 'Tienda' => $product->tienda->nombre,
                 'Precio antes' => $precio_antes ?? '-',
                 'Precio ahora' => $precio_despues ?? '-',
                 'Tarjeta?' => $tarjeta ? 'Si' : 'No',
               ]);
           });
    }
}
