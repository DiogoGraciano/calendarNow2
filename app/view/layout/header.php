<?php

namespace app\view\layout;

use app\models\main\menuSiteModel;
use app\models\main\menuAdmModel;
use app\view\layout\abstract\pagina;
use core\url;

class header extends pagina{

    public function __construct(string $pathlogo = "assets\imagens\logo.png")
    {
        $this->setTemplate("header.html");
        // $this->tpl->logo = url::getUrlBase().$pathlogo;
    }

    public function addLink(string $link,string $titulo,bool $ativo = false,bool $target_blank = false,string $extra = ""):header
    {
        $this->tpl->link = $link;
        $this->tpl->titulo = $titulo;
        $this->tpl->ativo = $ativo ? "active" : "";
        if($target_blank){
            $extra = $extra.' target="_blank"';
        }
        if($ativo){
            $extra = $extra.' aria-current="page"';
        }
        $this->tpl->extra = $extra;
        $this->tpl->block("LINK_NAV");

        return $this;
    }

    public function addMenus(menuSiteModel|menuAdmModel $model):header
    {
        $menus = $model::getByFilter(ativo:1);

        foreach ($menus as $menu)
        {
            $ativo = url::getUrlCompleta() == url::getUrlBase().$menu["controller"] || url::getUrlCompleta() == url::getUrlBase() && $menu["controller"] == "home";

            if($menu["controller"])
                $this->addLink(url::getUrlBase().$menu["controller"],$menu["nome"],$ativo,$menu["target_blank"]);
            elseif($menu["link"])
                $this->addLink($menu["link"],$menu["nome"],false,$menu["target_blank"]);
        }

        return $this;
    }

}

?>