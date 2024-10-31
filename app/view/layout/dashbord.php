<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;

class dashbord extends layout
{
    public function __construct()
    {
        $this->setTemplate("dashbord.html");
    }

    public function addCard(string $titulo,string $destaque,string $frase_data,string $icon):dashbord
    {
        $this->tpl->titulo = $titulo;
        $this->tpl->destaque = $destaque;
        $this->tpl->frase_data = $frase_data;
        $this->tpl->icon = $icon;
        $this->tpl->block("BLOCK_CARD");

        return $this;
    }

    public function addGrafico(grafico $grafico,string $title){
        $this->tpl->titulo_grafico = $title;
        $this->tpl->grafico = $grafico->parse();
        $this->tpl->block("BLOCK_GRAFICO");

        return $this;
    }
}
