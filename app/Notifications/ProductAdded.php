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
    private $precio_ref;
    private $precio_oferta;
    private $precio_tarjeta;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Producto $product)
    {
      $this->product = $product;
      $this->precio_oferta = $product->precio_oferta ? moneyFormat($product->precio_oferta, 'CLP') : null;
      $this->precio_ref = $product->precio_referencia ? moneyFormat($product->precio_referencia, 'CLP') : null;
      $this->precio_tarjeta = $product->precio_tarjeta ? moneyFormat($product->precio_tarjeta, 'CLP') : null;
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
       $precio_oferta = $this->precio_oferta;
       $precio_ref = $this->precio_ref;
       $precio_tarjeta = $this->precio_tarjeta;
       $product->load("tienda");

       return (new SlackMessage)
           ->from('iRata App', ':mouse:')
           ->to('#i-rata-nuevos')
           ->image('https://banner2.kisspng.com/20180530/jea/kisspng-ratatouille-mouse-the-walt-disney-company-remy-rec-rat-mouse-5b0f70a4353a97.309237151527738532218.jpg')
           ->content("Una ratita ha agregado un producto nuevo y su precio a sido agregado")
           ->attachment(function ($attachment) use ($product, $precio_oferta, $precio_ref, $precio_tarjeta){
             $attachment->title($product->nombre, $product->url_compra)
             ->fields([
               'Nivel rata' => ':rat:',
               'Nombre del producto' => $product->nombre,
               'Tienda' => $product->tienda->nombre,
               'Precio inicial' => $precio_ref ?? '-',
               'Precio oferta' => $precio_oferta ?? '-',
               'Precio tarjeta' => $precio_tarjeta ?? '-',
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
