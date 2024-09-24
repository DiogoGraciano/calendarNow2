<?php

namespace app\view\layout;

use app\helpers\functions;
use app\view\layout\abstract\pagina;
use core\url;

class head extends pagina{

    public function __construct(string $titulo=""){

        $this->setTemplate("head.html");

        $this->tpl->robots = "index,follow";
       
        // $empresa = (new calendarNow)->get(1);
        // $empresa->cidade = cidadeModel::get($empresa->id_cidade)->nome;
        // $empresa->estado = estadoModel::get($empresa->id_estado)->nome;

        // $this->tpl->empresa = new stdClass;
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->caminho_completo = url::getUrlCompleta();
        $this->tpl->title = $titulo;
        $this->tpl->class = functions::createNameId($titulo);
    }
}

?>