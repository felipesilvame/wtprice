<?php

return [

    /**
     * webhook_rata_ropa es el webhook de discord para notificar sobre productos no necesarios
     */
    'webook_rata_ropa' => env('WEBHOOK_RATA_ROPA', null),

    /**
     * webhook_rata_tecno es el webhook de discord para notificar sobre productos tecnologicos
     */
    'webhook_rata_tecno' => env('WEBHOOK_RATA_TECNO', null),
];