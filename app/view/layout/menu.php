<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;

class menu extends pagina{

    private $elements = [];

    public function __construct()
    {
        $this->setTemplate("../templates/menu.html");
    }

    public function setLista(){
        foreach ($this->elements as $element){
            $this->tpl->element = $element;
            $this->tpl->block("BLOCK_MENU");
        }  
        $this->elements = [];

        return $this;
    }

    public function addElement($element){
        $this->elements[] = $element;

        return $this;
    }
   
}
