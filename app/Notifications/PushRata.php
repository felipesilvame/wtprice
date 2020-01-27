<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PushRata extends Notification
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
    public function __construct(Producto $product, int $precio_antes, int $precio_despues)
    {
        $this->product = $product;
        $this->product->load('tienda');
        $this->precio_antes = moneyFormat($precio_antes, 'CLP');
        $this->precio_despues = moneyFormat($precio_despues, 'CLP');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $product = $this->product;
        $precio_antes = $this->precio_antes;
        $precio_despues = $this->precio_despues;

        return (new WebPushMessage)
            ->title('Alerta rata 🐀')
            ->icon('/notification-icon.png')
            ->body($product->nombre. ' a solo '.$precio_despues)
            ->action('Ver producto', 'view_product')
            ->data(['url' => $product->url_compra])
            ->vibrate([125,75,125,275,200,275,125,75,125,275,200,600,200,600]);
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
