<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;

class modal extends pagina{

    public function __construct(string $id = "modal",string $title = "Modal",string $content = "")
    {
        $this->setTemplate("modal.html");

        $this->tpl->id = $id;
        $this->tpl->title = $title;
        $this->tpl->content = $content;
    }
}
