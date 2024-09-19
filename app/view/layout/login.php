<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;
use app\helpers\mensagem;
use core\url;

/**
 * Classe para gerar a página de login.
 * Esta classe estende a classe 'pagina' para herdar métodos relacionados ao template.
 */
class login extends pagina{

    /**
     * Exibe o template da página de login.
    */
    public function __construct($usuario="",$senha=""){
        $this->setTemplate("login.html");
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->action_login = "login/action";
        $mensagem = new mensagem;
        $this->tpl->mensagem = $mensagem->parse();
        $this->tpl->usuario = $usuario;
        $this->tpl->senha = $senha;
        $this->tpl->action_cadastro_usuario = "login/usuario";
        $this->tpl->action_cadastro_empresa = "login/empresa";
        $this->tpl->action_esqueci = "login/esqueci";
    }
}