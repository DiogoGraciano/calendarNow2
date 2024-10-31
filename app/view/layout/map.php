<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;
use core\url;

class map extends layout{

    private int $countMaker = 1;

    public function __construct()
    {
        $this->setTemplate("map.html");
        $this->tpl->caminho = url::getUrlBase();
    }
    

    public function addMarker(float $latitude,float $logitude,string $mensagem = "",$open = false):map
    {
        $this->tpl->latitude = $latitude;
        $this->tpl->logitude = $logitude;
        $this->tpl->count = $this->countMaker;
        $this->countMaker++;
        if($mensagem){
            $this->tpl->mensagem = trim($mensagem);
            if($open){
                $this->tpl->open = ".openPopup()";
            }
            $this->tpl->block("BLOCK_MENSAGEM");
        }
        $this->tpl->block("BLOCK_MARKER");
        return $this;
    }
}
