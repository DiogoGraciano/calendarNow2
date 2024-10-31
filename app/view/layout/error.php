<?php
namespace app\view\layout;
use app\view\layout\abstract\layout;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class error extends layout
{
    /**
     * Mostra o erro renderizado.
     * 
     * @param int $code codigo de erro html
     * @param string $message mensagem do erro html
     */
    public function __construct(int $code = 404,string $message="A Pagina que está procurando não existe")
    {
        $this->setTemplate("error.html");
        $this->tpl->code = $code;
        $this->tpl->message = $message;
    }
}
