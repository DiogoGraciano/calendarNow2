<?php
namespace app\view\layout;

use app\helpers\functions;
use app\view\layout\abstract\pagina;

class div extends pagina
{

    public function __construct(string $name,string $class = "col-md-12",string $extra = "")
    {
        $this->setTemplate("div.html");
        $this->tpl->name = functions::createNameId($name);
        $this->tpl->class = $class;
        $this->tpl->extra = $extra;
    }

    public function addContent(string $content):div
    {
        $this->tpl->content = $content;
        $this->tpl->block("BLOCK_CONTENT");
        return $this;
    }
}
