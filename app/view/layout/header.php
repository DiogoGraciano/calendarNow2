<?php

namespace app\view\layout;

use app\models\login;
use app\view\layout\abstract\pagina;
use core\url;
use app\models\menu;

class header extends pagina{

    public function __construct(string $pathlogo = "assets\imagens\logo.webp")
    {
        $data = date('D');
        $mes = date('M');
        $dia = date('d');
        $ano = date('Y');
        
        $semana = array(
            'Sun' => 'Domingo', 
            'Mon' => 'Segunda-Feira',
            'Tue' => 'Terça-Feira',
            'Wed' => 'Quarta-Feira',
            'Thu' => 'Quinta-Feira',
            'Fri' => 'Sexta-Feira',
            'Sat' => 'Sábado'
        );
        
        $mes_extenso = array(
            'Jan' => 'Janeiro',
            'Feb' => 'Fevereiro',
            'Mar' => 'Marco',
            'Apr' => 'Abril',
            'May' => 'Maio',
            'Jun' => 'Junho',
            'Jul' => 'Julho',
            'Aug' => 'Agosto',
            'Nov' => 'Novembro',
            'Sep' => 'Setembro',
            'Oct' => 'Outubro',
            'Dec' => 'Dezembro'
        );
        

        $this->setTemplate("header.html");
        $this->tpl->logo = url::getUrlBase().$pathlogo;
        $this->tpl->data = $this->isMobile() ? $semana["$data"] . ", {$dia}/".date('m')."/{$ano}" : $semana["$data"] . ", {$dia} de " .$mes_extenso["$mes"]. " de {$ano}";

        $model = new menu;
    
        $menus = $model->getByFilter(ativo:1);

        foreach ($menus as $menu)
        {

            $path = explode("/",url::getUriPath());

            $controler = explode("/",$menu["controller"]);

            $ativo = $path[1] == $controler[0];

            if(isset($controler[2]) && isset($path[3])){
                $ativo = $path[3] == $controler[2];
            }

            $user = login::getLogged();

            if($user && in_array($user->tipo_usuario,json_decode($menu["tipo_usuario"]))){
                if($menu["controller"])
                    $this->addLink(url::getUrlBase().$menu["controller"],$menu["nome"],$ativo,$menu["target_blank"],$menu["class_icone"]);
                elseif($menu["link"])
                    $this->addLink($menu["link"],$menu["nome"],false,$menu["target_blank"]);
            }
        }

        return $this;
    }

    public function addLink(string $link,string $titulo,bool $ativo = false,bool $target_blank = false,string $icon = "",string $extra = ""):header
    {
        $this->tpl->link = $link;
        $this->tpl->titulo = $titulo;
        if($icon)
            $this->tpl->icon = '<i class="'.$icon.' me-2 icon"></i>';
        $this->tpl->ativo = $ativo ? "active" : "";
        if($target_blank){
            $extra = $extra.' target="_blank"';
        }
        if($ativo){
            $extra = $extra.' aria-current="page"';
        }
        $this->tpl->extra = $extra;
        $this->tpl->block("BLOCK_LINK_NAV");

        return $this;
    }
}

?>