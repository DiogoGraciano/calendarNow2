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
    
    public function set(array $markers):self
    {
        return $this;
    }

    public function addMarker($latitude,$logitude):map
    {
        return $this;
    }
}
