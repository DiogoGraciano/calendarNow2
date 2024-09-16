<?php

namespace app\view\layout;

use app\helpers\functions;
use app\view\layout\abstract\pagina;
use core\session;
use core\url;

class head extends pagina{

    public function __construct(string $titulo=""){

        $this->setTemplate("head.html");

        $this->tpl->robots = "index,follow";
        if(session::get("controller_namespace") == "app\controllers\admin"){
            $this->tpl->block("BLOCK_ADMIN");
            $this->tpl->robots = "noindex,nofollow";
        } 
        else{
            $this->tpl->block("BLOCK_SITE");
        }

        // $empresa = empresaModel::get(1);
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