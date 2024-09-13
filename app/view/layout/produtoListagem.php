<?php
namespace app\view\layout;

use app\helpers\functions;
use app\models\main\produtoModel;
use app\view\layout\abstract\pagina;
use core\url;

class produtoListagem extends pagina
{

    public function __construct()
    {
        $this->setTemplate("produtoListagem.html");

        $produtos = produtoModel::getProdutoAndVinculos(ativo:1);

        if(!$produtos){
            return $this;
        }

        $lastMarca = $produtos[0]["marca_id"];
        foreach ($produtos as $produto){

            if($lastMarca != $produto["marca_id"]){
                if(isset($produto["wave_tipo"]) && $produto["wave_tipo"])
                    $this->tpl->wave = (new wave($produto["wave_tipo"],$produto["wave_color"],$produto["wave_color_background"],$produto["wave_largura"],$produto["wave_distancia"],functions::createNameId($produto["marca_nome"])))->parse();
                $this->tpl->block("BLOCK_WAVE");
                $this->tpl->block("BLOCK_PRODUTOS");
            }

            $this->tpl->marca = $produto["marca_nome"];
            $this->tpl->marca_descricao = $produto["marca_descricao"];
            foreach ($produto["imagens"] as $key => $imagem){
                
                $this->tpl->active = "";

                if($key == 0)
                    $this->tpl->active = "active";
                
                $this->tpl->link_produto = url::getUrlBase()."produtos/detalhe/".$produto["id"];
                $this->tpl->img_produto =  $imagem["caminho"];
                $this->tpl->img_titulo_produto = $imagem["titulo"];
                $this->tpl->block("BLOCK_IMAGENS");
            }
            $this->tpl->link_produto = url::getUrlBase()."produtos/detalhe/".$produto["id"];
            $this->tpl->produto = $produto["nome"];

            $this->tpl->block("BLOCK_PRODUTO");

            $lastMarca = $produto["marca_id"];
        }

        $this->tpl->block("BLOCK_PRODUTOS");
    }
}
