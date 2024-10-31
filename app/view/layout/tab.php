<?php
namespace app\view\layout;

use app\helpers\functions;
use app\view\layout\abstract\layout;

class tab extends layout
{

    public function __construct()
    {
        $this->setTemplate("tab.html");
    }

    public function addTab(string $name,string $content,?bool $active = false):tab
    {
        $this->tpl->name = functions::createNameId($name);
        $this->tpl->name_label = $name;
        $this->tpl->content = $content;
        $this->tpl->active = $active?"show active":false;
        $this->tpl->block("BLOCK_TAB");
        $this->tpl->block("BLOCK_TAB_CONTENT");
        return $this;
    }
}
