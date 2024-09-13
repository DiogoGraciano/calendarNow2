<?php
namespace app\view\layout;

use app\models\main\produtoModel;
use app\view\layout\abstract\pagina;
use core\url;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class produtoDetalhe extends pagina
{

    public function __construct(int $id)
    {
        $this->setTemplate("produtoDetalhe.html");

        $produto = produtoModel::getProdutoAndVinculos($id,ativo:1,asArray:false);

        if(!$produto){
            return $this;
        }

        $this->tpl->produto = $produto[0];

        foreach ($produto[0]->imagens as $key => $imagem){
            $this->tpl->active = "";
            if($key == 0)
                $this->tpl->active = "active";
            
            $this->tpl->img_produto =  $imagem->caminho;
            $this->tpl->img_titulo_produto = $imagem->titulo;
            $this->tpl->block("BLOCK_IMAGENS");
        }

        if($produto[0]->documentos){
            foreach ($produto[0]->documentos as $key => $documento){
                $this->tpl->documento_caminho = $documento->caminho;
                $this->tpl->documento_titulo = $documento->titulo;
                $this->tpl->block("BLOCK_DOCUMENTO");
            }
            $this->tpl->block("BLOCK_DOCUMENTOS");
        }
    }
}
