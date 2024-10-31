<?php
namespace app\view\layout;
use app\view\layout\abstract\layout;

class filter extends layout
{
    public function __construct(string $action,string $target = "#consulta-admin")
    {
        $this->setTemplate("filter.html");
        $this->tpl->action = $action;
        $this->tpl->target = $target;
    }

    public function addLinha()
    {
        $this->tpl->block("BLOCK_LINHA_FILTER");
        
        return $this;
    }

    public function addFilter($tamanho, $input)
    {
        $this->tpl->tamanho = $tamanho;
        $this->tpl->filter = $input;
        $this->tpl->block("BLOCK_INPUT");
        $this->tpl->block("BLOCK_FILTER");

        return $this;
    }

    public function addbutton($button)
    {
        $this->tpl->button = $button;
        $this->tpl->block("BLOCK_BUTTON");

        return $this;
    }
}
