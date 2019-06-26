<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Producto;
use NotificationChannels\Twitter\TwitterChannel;
use NotificationChannels\Twitter\TwitterStatusUpdate;

class ProductoAhoraEnOferta extends Notification implements ShouldQueue
{
    use Queueable;

    private $product;
    private $precio_antes;
    private $precio_despues;
    private $is_tarjeta;
    private $porcentaje_rata;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Producto $product, int $precio_antes, int $precio_despues, float $porcentaje_rata = null, boolean $is_tarjeta = null)
    {
      $this->product = $product;
      $this->product->load('tienda');
      $this->precio_antes = moneyFormat($precio_antes, 'CLP');
      $this->precio_despues = moneyFormat($precio_despues, 'CLP');
      $this->is_tarjeta = (boolean)$is_tarjeta;
      $this->porcentaje_rata = (boolean)$porcentaje_rata ? (int)(round($porcentaje_rata*100)) : 0;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TwitterChannel::class];
    }

    /**
    * Get the Twitter representation of the notification.
    *
    * @param  mixed  $notifiable
    * @return \NotificationChannels\Twitter\TwitterStatusUpdate
    */
    public function toTwitter($notifiable)
    {
      $product = $this->product;
      $precio_antes = $this->precio_antes;
      $precio_despues = $this->precio_despues;
      $is_tarjeta = $this->is_tarjeta;
      $porcentaje_rata = $this->porcentaje_rata;

      $str = "Un producto que nunca ha estado en oferta... ahora lo está! \n";
      $str .= "Tienda: {$product->tienda->nombre}. $product->nombre. Antes {$precio_antes}, ahora {$precio_despues}.\n";
      $str .= $is_tarjeta ? "(Precio sólo con tarjeta) ": "";
      $str .= "Descuento: {$porcentaje_rata} %.";
      $str .= "$product->url_compra";
      return (new TwitterStatusUpdate($str));
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
