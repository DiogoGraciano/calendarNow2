<?php
namespace app\view\layout;
use app\view\layout\abstract\pagina;

class destaque extends pagina
{
    public function __construct(string $link,string $titulo,string $descricao,int $tamanho_texto = 6,string $id = "destaque")
    {
        $this->setTemplate("destaque.html");
        $this->tpl->id = $id;
        $this->tpl->link = $link;
        $this->tpl->titulo = $titulo;
        $this->tpl->descricao = $descricao;
        $this->tpl->tamanho_texto = $tamanho_texto;
    }

    public function addImage(string $image,int $tamanho_imagem,string $alt_image = "destaque",string $link = "#",bool $active = false):destaque
    {
        if($this->isMobile()){
            $this->tpl->image = $image;
            $this->tpl->alt_image = $alt_image;
            $this->tpl->link_imagem = $link;
            $this->tpl->active = $active?"active":"";
            $this->tpl->block("BLOCK_IMAGEM_MOBILE");
        }
        else{
            $this->tpl->image = $image;
            $this->tpl->alt_image = $alt_image;
            $this->tpl->tamanho_imagem = $tamanho_imagem;
            $this->tpl->link_imagem = $link;
            $this->tpl->block("BLOCK_IMAGE");
        }
        return $this;
    }

    public function addRow(int $tamanho_row):destaque
    {
        if(!$this->isMobile()){
            $this->tpl->tamanho_row = $tamanho_row;
            $this->tpl->block("BLOCK_ROW");
        }

        return $this;
    }

    public function show():void
    {
        if($this->isMobile()){
            $this->tpl->block("BLOCK_IMAGEMS_MOBILE");
        }
        $this->tpl->show();
    }

    public function parse():string
    {
        if($this->isMobile()){
            $this->tpl->block("BLOCK_IMAGEMS_MOBILE");
        }
        return $this->tpl->parse();
    }
}
