<?php

namespace core;

use app\controllers\main\error;
use app\view\layout\head;
use Exception;

/**
 * Classe para manipular métodos com base na URI.
 */
class method{
    /**
     * URI atual.
     *
     * @var string
     */
    private $uri;

    /**
     * Construtor da classe.
     * Inicializa a URI atual.
     */
    public function __construct()
    {
        $this->uri = url::getUriPath();
    }

    /**
     * Carrega e valida o método com base na URI e no controlador fornecido.
     *
     * @param string $controller Nome do controlador para validar o método.
     * 
     * @return string            Retorna o nome do método validado.
     * 
     * @throws Exception         Lança uma exceção se o método não existir no controlador fornecido.
     */
    public function load($controller){
        $method = $this->getMethod();

        if(!method_exists($controller, $method)){
            (new head("Erro"))->show();
            (new error)->index();
            die;
        }

        return $method;
    }

    /**
     * Obtém o nome do método a partir da URI.
     *
     * @return string   Retorna o nome do método extraído da URI ou "index" se nenhum método for especificado.
     */
    private function getMethod(){

        if (substr_count($this->uri,'/') > 1){
            $method = array_values(array_filter(explode('/',$this->uri)));
            if (array_key_exists(1,$method))
                return $method[1];
        }

        return "index";
    }
}

?>
