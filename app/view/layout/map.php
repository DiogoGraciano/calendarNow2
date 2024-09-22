<?php

namespace app\view\layout;

use app\view\layout\abstract\pagina;
use core\url;

class map extends pagina{

    public function __construct()
    {
        $this->setTemplate("map.html");
        $this->tpl->caminho = url::getUrlBase();
    }
    

    public function addMarker(float $latitude,float $logitude,string $mensagem = "",$open = false):map
    {
        $this->tpl->latitude = $latitude;
        $this->tpl->logitude = $logitude;
        if($mensagem){
            $this->tpl->mensagem = $mensagem;
            if($open){
                $this->tpl->open = ".openPopup()";
            }
            $this->tpl->block("BLOCK_MENSAGEM");
        }
        $this->tpl->block("BLOCK_MARKER");
        return $this;
    }
}
