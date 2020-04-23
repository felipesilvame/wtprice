<?php

namespace App\Helpers\General;

use Exception;
use App\Models\Proxy as ProxyModel;

class Proxy
{
    const PROXYS = [
        'http://200.73.128.86:80',
        'http://195.4.168.40:8080',
        'http://51.158.107.202:8811',
        'http://45.76.43.163:8080',
        'https://202.142.155.136:8080',
        'socks4://162.220.109.42:58196',
        'https://168.149.142.170:8080',
        'https://168.149.146.172:8080',
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
