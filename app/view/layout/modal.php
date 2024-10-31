<?php

namespace app\view\layout;
use app\view\layout\abstract\layout;

class modal extends layout{

    public function __construct(string $id = "modal",string $title = "Modal",string $content = "",string $class = "modal fade")
    {
        $this->setTemplate("modal.html");

        $this->tpl->id = $id;
        $this->tpl->title = $title;
        $this->tpl->class = $class;
        $this->tpl->content = $content;
    }
}
