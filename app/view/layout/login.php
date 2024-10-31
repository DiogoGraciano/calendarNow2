<?php

namespace app\view\layout;
use app\view\layout\abstract\layout;
use app\helpers\mensagem;
use core\url;
use app\models\calendarNow;

/**
 * Classe para gerar a página de login.
 * Esta classe estende a classe 'pagina' para herdar métodos relacionados ao template.
 */
class login extends layout{

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

        $empresa = (new calendarNow)->get(1);
        $recapcha = $this->getTemplate("recapcha.html");
        $recapcha->element_id = "g-recaptcha-login-response";
        $recapcha->empresa = $empresa;
        $this->tpl->recapcha = $recapcha->parse();
    }
}