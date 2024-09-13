<?php
namespace app\view\layout;
use app\view\layout\abstract\pagina;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class wave extends pagina
{

    public function __construct(int $type = 0,string $color = "#000",$color_backgroud = "#fff",int $width = 5,int $dasharray = 6,string $name = "",int $margin = 1)
    {
        $this->setTemplate("wave.html");
        $this->tpl->color = $color;
        $this->tpl->color_backgroud = $color_backgroud;
        $this->tpl->name = $name;
        $this->tpl->width = $width;
        $this->tpl->dasharray = $dasharray." ".$dasharray;
        $this->tpl->margin = $margin;
        if(!$type || $type > 6 || $type < 1){
            $this->tpl->block("BLOCK_WAVE_".rand(1,6));
        }
        else{
            $this->tpl->block("BLOCK_WAVE_".$type);
        }
    }
}
