<?php

namespace App\Helpers\General;

use App\Models\Producto;
use App\Models\MinimoPrecio;
use App\Models\Device;
use App\Models\SospechaRata;
use App\Notifications\PushRata;
use GuzzleHttp\Client;
use App\Helpers\General\Arr as ArrHelper;
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
                    $a = self::menorValor($ahora);
                    $p_rata = self::comparar($a, $ahora->precio_referencia);
                    $p_rata_relativo = self::comparar($a, $minimo->precio_referencia);
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

                        /**
                         * Hey! alto, se ha dado un caso donde por error antes no habia precio oferta
                         * ahora si lo hay, y es misteriosamente menor al precio tarjeta que habia
                         */
                        if ($ahora->precio_oferta && $ahora->precio_tarjeta && ($ahora->precio_oferta < $ahora->precio_tarjeta)) {
                            $p_rata = self::comparar($ahora->precio_oferta, $minimo->precio_referencia);
                            $p_rata_relativo = self::comparar($ahora->precio_oferta, $ahora->precio_referencia);
                        } else {
                            $p_rata = self::comparar($ahora->precio_tarjeta, $minimo->precio_tarjeta);
                            $p_rata_relativo = self::comparar($ahora->precio_tarjeta, $minimo->precio_referencia);
                        }
                        
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
                     * Da lo mismo, elijo el más bajo
                     */
                    else {
                        $a = self::menorValor($ahora);
                        $p_rata = self::comparar($a, $minimo->precio_tarjeta);
                        $p_rata_relativo = self::comparar($a, $minimo->precio_referencia);
                    } 
                }
                /**
                 * Caso 3.3, tengo ambos
                 * elijo el más bajo 
                 * menor al precio de oferta siempre
                 */
                else {
                    $a = self::menorValor($ahora);
                    $p_rata = self::comparar($a, $minimo->precio_tarjeta);
                    $p_rata_relativo = self::comparar($a, $minimo->precio_referencia);
                }

            }
            /**
             *  Caso 4. cualquier otro caso no considerado aqui
             *  se compara el precio referencia con el precio más bajo registrado.
             */
            else {
                $a = self::menorValor($ahora);
                $p_rata = self::comparar($a, $ahora->precio_referencia);
                $p_rata_relativo = self::comparar($a, $ahora->precio_referencia);
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
     * Obtiene el valor si hay stock de un producto en una tienda.
     * La entrada es la data JSON, y el segundo parámetro es el criterio para obtener la info,
     * de todas formas, un tercer parámetro va a ser el nombre de la tienda en caso de que
     * se tenga que hardcodear algo, como por ejemplo falabella, que tiene una flag "OUT_OF_STOCK"
     */
    public static function getAvailableFlag($data = null, $predicate = null, $nombre_tienda = null){
        try {
            if ($data && $predicate) {
                if ($nombre_tienda && $nombre_tienda === 'Falabella') {
                    $no_stock = ArrHelper::get_pipo($data, 'responseType', null);
                    if ($no_stock && $no_stock == 'OUT_OF_STOCK') {
                        return "Sin Stock";
                    } else if ($available = ArrHelper::get_pipo($data, $predicate, null)){
                        return "Disponible";
                    } else return "Sin Stock";
                } else if ($nombre_tienda && $nombre_tienda === 'Ripley'){
                    $no_stock = ArrHelper::get_pipo($data, $predicate, null);
                    $unavailable  = ArrHelper::get_pipo($data, 'simple.isUnavailable', false);
                    return ($no_stock || $unavailable) ? "Sin Stock" : "Disponible";
                } else if ($nombre_tienda && $nombre_tienda === 'ABCDin'){
                    $availability = ArrHelper::get_pipo($data, $predicate, null);
                    if ($availability && $availability === 'Available') {
                        return "Disponible";
                    } else if ($availability && $availability === 'Unavailable'){
                        return "Sin Stock";
                    }
                } else if ($nombre_tienda && $nombre_tienda === 'Lider'){
                    $available = ArrHelper::get_pipo($data, $predicate, null);
                    return $available ? 'Disponible' : 'Sin Stock';
                } else if ($nombre_tienda && $nombre_tienda === 'Corona'){
                    $quantity = self::getStock($data, $predicate, $nombre_tienda);
                    return ($quantity > 0) ? 'Disponible' : 'Sin Stock';
                }
                else if ($available = ArrHelper::get_pipo($data, $predicate, null)) {
                    return "Disponible";
                } else return "Sin Stock";
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        return "Sin Información";
    }

    /**
     * Obtiene el stock de un producto en una tienda.
     * La entrada es la data JSON, y el segundo parámetro es el criterio para obtener la info,
     * de todas formas, un tercer parámetro va a ser el nombre de la tienda en caso de que
     * se tenga que hardcodear algo, como por ejemplo falabella, que tiene una flag "OUT_OF_STOCK"
     */
    public static function getStock($data = null, $predicate = null, $nombre_tienda = null){
        try {
            if ($data && $predicate) {
                $quantity = ArrHelper::get_pipo($data, $predicate, null);
                if ($quantity !== null) {
                    return (integer) $quantity;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        return null;
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
            Notification::send(Device::all(),new PushRata($producto, self::menorValor($minimo), self::menorValor($producto)));
        } catch (\Exception $e) {
            //throw $th;
        }
        try {
            \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                      ->notify(new \App\Notifications\AlertaRata($producto, $minimo->precio_referencia, self::menorValor($producto), $p_rata));
        } catch (\Exception $e) {
            //throw $th;
        }
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
    }

    /** 
     * Notifica por todos los medios una alerta coipo
    */
    public static function alertaCoipo(Producto $producto, MinimoPrecio $minimo, float $p_rata){
        $imgUrl = self::imagenUrl($producto->imagen_url);
        try {
            \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                      ->notify(new \App\Notifications\AlertaRata($producto, self::menorValor($minimo), self::menorValor($producto), $p_rata));
        } catch (\Exception $e) {
            //throw $th;
        }
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
     * Manda una sospecha rata por discord
     */
    public static function sospechaRataUrl($url){
        try {
            $client = new Client();
            $response = $client->post('https://discordapp.com/api/webhooks/699757818311475221/mV3nu84k_sd0jCecqdtV2MackIrWuk4uYypQgJCysO9paf25cMC2a4mVaUenjP_w2Sn3', [
                'json' => [
                    'content' => 'Una rata sorprendida ha encontrado un producto con descuento mayor a 70%. Visita la web para corroborar la oferta',
                    'embeds' => [
                        [
                            "title" => $url,
                            "url" => $url,
                            "color" => 2612178,
                            
                        ]
                    ]
                ]
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Manda una sospecha rata por discord
     */
    public static function sospechaRata(SospechaRata $producto){
        $imgUrl = self::imagenUrl($producto->url_imagen);
        try {
            $client = new Client();
            $response = $client->post('https://discordapp.com/api/webhooks/699757818311475221/mV3nu84k_sd0jCecqdtV2MackIrWuk4uYypQgJCysO9paf25cMC2a4mVaUenjP_w2Sn3', [
                'json' => [
                    'content' => 'Una rata sorprendida ha encontrado un producto con descuento mayor a 70%. Visita la web para corroborar la oferta',
                    'embeds' => [
                        [
                            "title" => $producto->nombre_producto.' a tan sólo '.moneyFormat(self::menorValor($producto), 'CLP'),
                            "url" => $producto->url_compra,
                            "color" => 2612178,
                            "fields" => [
                                [
                                    "name" => "Nombre producto",
                                    "value" => $producto->nombre_producto,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Tienda",
                                    "value" => $producto->nombre_tienda,
                                    "inline" => true,
                                ],
                                [
                                    "name" => "Precio antes",
                                    "value" => moneyFormat($producto->precio_referencia, 'CLP'),
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
        } catch (\Throwable $th) {
            //throw $th;
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
