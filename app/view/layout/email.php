<?php

namespace app\view\layout;

use app\models\login;
use app\view\layout\abstract\pagina;
use core\url;
use app\models\menu;

class email extends pagina{

    private string $logo = "";
    private string $caminho = ""; 

    public function __construct(string $pathlogo = "assets\imagens\logo.webp")
    {
        $this->logo = $pathlogo;
        $this->caminho = url::getUrlBase();
    }

    public function setEmailBtn(string $action,string $titulo,string $descricao,?string $btn_titulo = null,?string $nome_acao = null):header
    {
        $this->setTemplate("btnEmail.html");

        $this->tpl->action = $action;
        $this->tpl->titulo = $titulo;
        $this->tpl->descricao = $descricao;
        $this->tpl->btn_titulo = $btn_titulo?:$titulo;
        $this->tpl->nome_acao = $nome_acao?:strtolower($titulo);
    }
}

?>