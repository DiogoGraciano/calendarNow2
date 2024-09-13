<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;
use app\helpers\mensagem;

/**
 * Classe para gerar uma lista de objetos com um título e botões opcionais.
 * Esta classe estende a classe 'pagina' para herdar métodos relacionados ao template.
 */
class lista extends pagina{

    /**
     * Array para armazenar os objetos que serão exibidos na lista.
     *
     * @var array
     */
    private $lista;

    /**
     * Construtor da classe.
     * Inicializa o template da lista.
     */
    public function __construct()
    {
        $this->setTemplate("../templates/lista.html");
    }

    /**
     * Define o título da lista e configura os objetos da lista no template.
     *
     * @param string $titulo Título da lista.
     */
    public function setLista(string $titulo){
        $this->tpl->titulo = $titulo;
        $mensagem = new mensagem;
        $this->tpl->mensagem = $mensagem->parse();
        if($this->lista){
            foreach ($this->lista as $objeto){
                $this->tpl->url_objeto = $objeto->url_objeto;
                $this->tpl->titulo_objeto = $objeto->titulo_objeto; 
                $this->tpl->block("BLOCK_LISTA");
            } 
        }
        else
            $this->tpl->block("BLOCK_NO_LISTA");  
    }

    /**
     * Adiciona um botão ao template da lista.
     *
     * @param string $button Texto ou código HTML do botão.
     */
    public function addButton(string $button){
        $this->tpl->button = $button;
        $this->tpl->block("BLOCK_BUTTONS");
    }

    /**
     * Exibe o template da lista.
     */
    public function show(){
        $this->tpl->show();
    }

    /**
     * Adiciona um objeto à lista.
     *
     * @param string $url_objeto URL do objeto.
     * @param string $titulo_objeto Título do objeto.
     */
    public function addObjeto(string $url_objeto,string $titulo_objeto){
        $this->lista[] =  json_decode('{"url_objeto":"'.$url_objeto.'","titulo_objeto":"'.$titulo_objeto.'"}');
    }
}
