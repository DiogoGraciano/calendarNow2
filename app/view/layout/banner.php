<?php
namespace app\view\layout;

use app\models\main\bannerModel;
use app\view\layout\abstract\layout;
use core\url;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class banner extends layout
{

    public function __construct(string $nome = "principal",string $wave_color = "#fff")
    {
        $this->setTemplate("banner.html");
        if($wave_color){
            $this->tpl->wave_color = $wave_color;
            $this->tpl->block("WAVE");
        }
        $this->tpl->nome = $nome;
    }

    public function addBanner(string $imagem,string $alt,bool $active = false,$link = "#",string $extra = ""):banner
    {
        $this->tpl->imagem = $imagem;
        $this->tpl->alt = $alt;
        $this->tpl->active = $active?"active":"";
        $this->tpl->extra = $extra;
        $this->tpl->link = $link;
        $this->tpl->block("BANNER");
        return $this;
    }

    public function addBanners(int $id_tipo):banner
    {
        $banners = bannerModel::getByFilter(id_tipo:$id_tipo,ativo:1);

        foreach ($banners as $key => $bannerDb){
            $this->addBanner($this->isMobile()?$bannerDb["caminho_mobile"]:$bannerDb["caminho"],$bannerDb["titulo"],$key == 0,$bannerDb["link"]?:url::getUrlBase()."produtos");
        }

        return $this;
    }
}
