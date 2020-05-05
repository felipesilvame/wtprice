<?php

namespace App\Helpers\General;

use Exception;
use App\Models\Proxy as ProxyModel;

class Proxy
{
    const PROXYS = [
        'http://lum-customer-hl_9bd375c3-zone-static:k2o7fh2omlyg@zproxy.lum-superproxy.io:22225'
    ];

    /**
     * Elige un proxy random
     * @return string
     */
    static public function random(){
        $proxys = ProxyModel::where('activo', true)->get();
        if(count($proxys) == 0) return self::PROXYS[array_rand(self::PROXYS)];
        else return $proxys->random();
    }

    /**
     * Elige uno determinado
     * @param int $index
     * @return string
     */
    static public function index(int $index){
        $proxys = ProxyModel::where('activo', true)->get();
        if(count($proxys) == 0){
            if ($index < 0 || $index >= count(self::PROXYS)) {
                throw new Exception("Número no válido", 1);
            } else return self::PROXYS[$index];
        } else {
            if ($index < 0 || $index >= count($proxys)) {
                throw new Exception("Número no válido", 1);
            } else return $proxys[$index];
        }
        
    }
}
