<?php

namespace App\Helpers\General;

use App\Models\Producto;
use App\Models\MinimoPrecio;
use App\Models\Device;
use App\Notifications\PushRata;
use GuzzleHttp\Client;
use Notification;


/**
 * 
 * Rata static methods for black-box 'rata algorithm percent'
 * 
 * p_rata se define como el porcentaje rata absoluto que hay en el producto,
 * y se compara siempre con el minimo establecido anteriormente
 * 
 * p_rata_relativo se define como el porcentaje rata comparado con los otros precios registrados,
 * dependiendo del caso, puede tomar el valor minimo referencial, valor ahora referencial, u otros
 */
class Rata
{
    /**
     * Calcula el porcentaje rata y el porcentaje rata relativo del producto
     * @param \App\Models\Producto $ahora
     * @param \App\Models\MinimoPrecio $minimo
     * @return array
     */
    public static function calculaRata(Producto $ahora, MinimoPrecio $minimo){
        $p_rata = 0.0;
        $p_rata_relativo = 0.0;
        try {
            /** 
             * Caso 1, comparar entre los precios referencia
             * el p_rata es entre el minimo y el ahora
             * el p_rata_relativo es entre el antes y el ahora
            */
            if(!$minimo->precio_oferta && !$minimo->precio_tarjeta && !$ahora->precio_oferta && !$ahora->precio_tarjeta){
                $p_rata = self::comparar($ahora->precio_referencia, $minimo->precio_referencia);
                $p_rata_relativo = self::comparar($ahora->precio_referencia, $minimo->precio_referencia);
            } 
            /**
             * Caso 2, antes no tenia precio oferta ni precio tarjeta y ahora tengo precio oferta o precio tarjeta
             */
            else if ((!$minimo->precio_oferta && !$minimo->precio_tarjeta ) && ($ahora->precio_oferta || $ahora->precio_tarjeta)){
                /**
                 * Caso 2.1, solo tengo precio oferta ahora
                 */
                if ($ahora->precio_oferta && !$ahora->precio_tarjeta){
                    // se supone que el precio oferta ahora es menor al minimo precio referencial
                    $p_rata = self::comparar($ahora->precio_oferta, $minimo->precio_referencia);
                    $p_rata_relativo = self::comparar($ahora->precio_oferta, $minimo->precio_referencia);
                } 
                /**
                 * Caso 2.2, solo tengo precio tarjeta ahora
                 */
                else if (!$ahora->precio_oferta && $ahora->precio_tarjeta){
                    $p_rata = self::comparar($ahora->precio_tarjeta, $minimo->precio_tarjeta);
                    $p_rata_relativo = self::comparar($ahora->precio_tarjeta, $minimo->precio_referencia);
                } 
                /**
                 * Caso 2.3, tengo ambos 
                 */
                else {
                    $p_rata = self::comparar($ahora->precio_tarjeta, $minimo->precio_tarjeta);
                    $p_rata_relativo = self::comparar($ahora->precio_tarjeta, $minimo->precio_referencia);
                }
            }
            /**
             * Caso 3, antes si tenia precio oferta o precio tarjeta y ahora tengo precio oferta o precio tarjeta
             */
            else if (($minimo->precio_oferta || $minimo->precio_tarjeta ) && ($ahora->precio_oferta || $ahora->precio_tarjeta)){
                /**
                 * Caso 3.1, antes tenia minimo oferta 
                 */
                if ($minimo->precio_oferta && !$minimo->precio_tarjeta) {
                    /**
                     * Caso 3.1.1, solo tengo precio oferta ahora
                     */
                    if ($ahora->precio_oferta && !$ahora->precio_tarjeta){
                        // se supone que el precio oferta ahora es menor al minimo precio referencial
                        $p_rata = self::comparar($ahora->precio_oferta, $minimo->precio_oferta);
                        $p_rata_relativo = self::comparar($ahora->precio_oferta, $minimo->precio_referencia);
                    } 
                    /**
                     * Caso 3.1.2, tengo precio tarjeta u oferta
                     * Da lo mismo, está el supuesto que el precio de tarjeta es
                     * menor al precio de oferta siempre.
                     */
                    else {
                        $p_rata = self::comparar($ahora->precio_tarjeta, $minimo->precio_tarjeta);
                        $p_rata_relativo = self::comparar($ahora->precio_tarjeta, $minimo->precio_referencia);
                    } 
                }
                /**
                 * Caso 3.2, antes tenia minimo tarjeta
                 */
                else if (!$minimo->precio_oferta && $minimo->precio_tarjeta) {
                    /**
                     * Caso 3.2.1, solo tengo precio oferta ahora
                     */
                    if ($ahora->precio_oferta && !$ahora->precio_tarjeta){
                        // se supone que el precio oferta ahora es menor al minimo precio referencial
                        $p_rata = self::comparar($ahora->precio_oferta, $minimo->precio_tarjeta);
                        $p_rata_relativo = self::comparar($ahora->precio_oferta, $minimo->precio_referencia);
                    } 
                    /**
                     * Caso 3.2.2, tengo precio tarjeta u oferta
                     * Da lo mismo, está el supuesto que el precio de tarjeta es
                     * menor al precio de oferta siempre.
                     */
                    else {
                        $p_rata = self::comparar($ahora->precio_tarjeta, $minimo->precio_tarjeta);
                        $p_rata_relativo = self::comparar($ahora->precio_tarjeta, $minimo->precio_referencia);
                    } 
                }
                /**
                 * Caso 3.3, tengo ambos
                 * De todas formas, el precio tarjeta es 
                 * menor al precio de oferta siempre
                 */
                else {
                    $p_rata = self::comparar($ahora->precio_tarjeta, $minimo->precio_tarjeta);
                    $p_rata_relativo = self::comparar($ahora->precio_tarjeta, $minimo->precio_referencia);
                }

            }
            /**
             *  Caso 4. cualquier otro caso no considerado aqui
             *  Se deja en supuesto que es más preciso y fácil de comprar un producto cuyo precio
             *  esté en oferta, independiente si tiene posibilidad de tarjeta.
             */
            else {
                $p_rata = self::comparar($ahora->precio_oferta, $minimo->precio_oferta);
                $p_rata_relativo = self::comparar($ahora->precio_oferta, $minimo->precio_referencia);
            }
        } catch (\Exception $e) {
            //throw $th;
        }
        return [$p_rata, $p_rata_relativo];
    }

    /**
     * Compara el porcentaje consigo mismo, 
     * en caso de que un producto tenga el valor erroneo 
     * al primer escaneo
     * @param \App\Models\Producto $producto
     * @return array
     */
    public static function calculaSelf(Producto $producto){
        $p_rata = 0.0;
        $p_rata_relativo = 0.0;
        try {
            if ($producto->precio_tarjeta && $producto->precio_oferta) {
                $p_rata = self::comparar($producto->precio_tarjeta, $producto->precio_oferta);
                $p_rata_relativo = self::comparar($producto->precio_tarjeta, $producto->precio_referencia);
            } else if ($producto->precio_tarjeta && !$producto->precio_oferta) {
                $p_rata = self::comparar($producto->precio_tarjeta, $producto->precio_referencia);
                $p_rata_relativo = $p_rata;
            } else if (!$producto->precio_tarjeta && $producto->precio_oferta) {
                $p_rata = self::comparar($producto->precio_oferta, $producto->precio_referencia);
                $p_rata_relativo = $p_rata;
            } 
        } catch (\Exception $e) {
            //throw $th;
        }

        return [$p_rata, $p_rata_relativo];
    }

    /**
     * Compara entre 2 precios, si es negativo, se trunca a 0.
     * Se asume que el primer elemento siempre será menor al segundo
     * @param int|float $a
     * @param int|float $b
     * @return float
     */
    public static function comparar($a, $b){
        try {
            if (!$a || !$b) return 0;
            $c = ((float)$b-(float)$a)/(float)$b;
            return $c < 0 ? 0 : $c;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Notifica por discord una alerta hamster
     */
    public static function alertaHamster(Producto $producto, MinimoPrecio $minimo, float $p_rata){
        $imgUrl = self::imagenUrl($producto->imagen_url);
        try {
            $client = new Client();
            $response = $client->post('https://discordapp.com/api/webhooks/700179498922016829/SYjLTV8a8bL1BZ4QgmRZSaPBABuYtnJ_fYhEu1rhZ4rAnTlNsaqE6T-NyIzpUtH-QwUp', [
                'json' => [
                    'content' => 'Alerta Hamster',
                    'embeds' => [
                        [
                            "title" => $producto->nombre.' a tan sólo '.moneyFormat(self::menorValor($producto), 'CLP'),
                            "url" => $producto->url_compra,
                            "color" => 6122878,
                            "fields" => [
                                [
                                    "name" => "Nombre producto",
                                    "value" => $producto->nombre,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Tienda",
                                    "value" => $producto->tienda->nombre,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Precio antes",
                                    "value" => moneyFormat($minimo->precio_referencia, 'CLP'),
                                ],
                                [
                                    "name" => "Precio ahora",
                                    "value" => moneyFormat(self::menorValor($producto), 'CLP'),
                                ]
                            ],
                            "image" => [
                                "url" => $imgUrl
                            ]
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
        try {
            //Notification::send(Device::all(),new PushRata($producto, self::menorValor($minimo), self::menorValor($producto)));
        } catch (\Exception $e) {
            //throw $th;
        }
        try {
           // \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
            //          ->notify(new \App\Notifications\AlertaRata($producto, self::menorValor($minimo), self::menorValor($producto), $p_rata));
        } catch (\Exception $e) {
            //throw $th;
        }
    }

    /** 
     * Notifica por todos los medios una alerta rata
    */
    public static function alertaRata(Producto $producto, MinimoPrecio $minimo, float $p_rata){
        $imgUrl = self::imagenUrl($producto->imagen_url);
        try {
            $client = new Client();
            $response = $client->post('https://discordapp.com/api/webhooks/700204968312832021/mdNtO_LLhxYH0rv5wIk1PWQJu5xuam9dHBT4GdPzsDkDwYCjaPTy39NpwJD23EpDElID', [
                'json' => [
                    'content' => 'Alerta Rata',
                    'embeds' => [
                        [
                            "title" => $producto->nombre.' a tan sólo '.moneyFormat(self::menorValor($producto), 'CLP'),
                            "url" => $producto->url_compra,
                            "color" => 9323693,
                            "fields" => [
                                [
                                    "name" => "Nombre producto",
                                    "value" => $producto->nombre,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Tienda",
                                    "value" => $producto->tienda->nombre,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Precio antes",
                                    "value" => moneyFormat($minimo->precio_referencia, 'CLP'),
                                ],
                                [
                                    "name" => "Precio ahora",
                                    "value" => moneyFormat(self::menorValor($producto), 'CLP'),
                                ]
                            ],
                            "image" => [
                                "url" => $imgUrl
                            ] 
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
        try {
            Notification::send(Device::all(),new PushRata($producto, self::menorValor($minimo), self::menorValor($producto)));
        } catch (\Exception $e) {
            //throw $th;
        }
        try {
            \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                      ->notify(new \App\Notifications\AlertaRata($producto, self::menorValor($minimo), self::menorValor($producto), $p_rata));
        } catch (\Exception $e) {
            //throw $th;
        }
    }

    /** 
     * Notifica por todos los medios una alerta coipo
    */
    public static function alertaCoipo(Producto $producto, MinimoPrecio $minimo, float $p_rata){
        $imgUrl = self::imagenUrl($producto->imagen_url);
        try {
            $client = new Client();
            $response = $client->post('https://discordapp.com/api/webhooks/700205655453204560/yrAx67HZ8f-ZTijUbIFvgsjMxQVoqSEHH25aZSxTkqPlQQIxQsupQi-HIQntvMB76VRP', [
                'json' => [
                    'content' => 'Alerta COIPO CTM! ESTA WEA ES UN COIPO ',
                    'embeds' => [
                        [
                            "title" => $producto->nombre.' a tan sólo '.moneyFormat(self::menorValor($producto), 'CLP'),
                            "url" => $producto->url_compra,
                            "color" => 13849600,
                            "fields" => [
                                [
                                    "name" => "Nombre producto",
                                    "value" => $producto->nombre,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Tienda",
                                    "value" => $producto->tienda->nombre,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Precio antes",
                                    "value" => moneyFormat($minimo->precio_referencia, 'CLP'),
                                ],
                                [
                                    "name" => "Precio ahora",
                                    "value" => moneyFormat(self::menorValor($producto), 'CLP'),
                                ]
                            ],
                            "image" => [
                                "url" => $imgUrl
                            ] 
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Checkea cual es el precio o monto mas bajo de los que tiene un producto
     * 
     */
    public static function menorValor($producto){
        $valores = [];
        if ($producto->precio_referencia) {
            $valores[] = $producto->precio_referencia;
        }
        if ($producto->precio_oferta) {
            $valores[] = $producto->precio_oferta;
        }
        if ($producto->precio_tarjeta) {
            $valores[] = $producto->precio_tarjeta;
        }
        return min($valores);
    }

    /**
    * Set a placeholder for img notification
    */
    public static function imagenUrl($url){
        if (!$url || $url == '') return 'https://via.placeholder.com/150';
        return parse_url($url, PHP_URL_SCHEME) === null ? 'https:'. $url : $url;
    }
}
