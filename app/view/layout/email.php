<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;
use core\url;

class email extends layout{

    private string $logo = "";

    public function __construct(string $pathlogo = "assets/imagens/logo.webp")
    {
        $this->logo = $pathlogo;
    }

    public function setEmailBtn(string $action,string $titulo,string $descricao,?string $btn_titulo = null,?string $nome_acao = null):email
    {
        $this->setTemplate("email\btnEmail.html");

        $this->tpl->logo = $this->logo;
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->action = $action;
        $this->tpl->titulo = $titulo;
        $this->tpl->descricao = $descricao;
        $this->tpl->btn_titulo = $btn_titulo?:$titulo;
        $this->tpl->nome_acao = $nome_acao?:strtolower($titulo);

        return $this;
    }
}

?>