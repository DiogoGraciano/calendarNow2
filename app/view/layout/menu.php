<?php

namespace app\view\layout;
use app\view\layout\abstract\layout;
use app\helpers\mensagem;

class menu extends layout{

    private $elements = [];

    public function __construct()
    {
        $this->setTemplate("menu.html");

        $mensagem = new mensagem;
        $this->tpl->mensagem = $mensagem->parse();
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
