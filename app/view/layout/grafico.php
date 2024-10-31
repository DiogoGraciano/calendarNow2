<?php
namespace app\view\layout;
use app\view\layout\abstract\layout;

class grafico extends layout{

    function __construct(array $datax,array $datay,string $chartName,string $type = "line",string $class = "",bool $fill = false,bool $legends = false,string $title = "",string $backColor = 'random',string $borderColor = 'rgb(0,0,0)',string $label = "")
    {
        $this->setTemplate("grafico.html", true);

        if ($type == "line" 
            || $type == "bar" 
            || $type == "horizontalBar" 
            || $type == "pie" 
            || $type == "doughnut"){

            if(is_string($backColor))
                $backColor = '"'.$backColor.'"';

            if(is_string($borderColor))
                $borderColor = '"'.$borderColor.'"';

            if ($backColor == '"random"')
                $backColor = json_encode($this->generateColors(count($datax)));

            if ($borderColor == '"random"')
                $borderColor = json_encode($this->generateColors(count($datax)));
            
            $this->tpl->datax = json_encode($datax);
            $this->tpl->datay = json_encode($datay);
            $this->tpl->backColor = $backColor;
            $this->tpl->borderColor = $borderColor;
            $this->tpl->label = '"'.$label.'"';
            $this->tpl->chartName = $chartName;
            $this->tpl->type = $type;
            $this->tpl->legends = !$legends?"false":"true";
            $this->tpl->displayTitle = !$title?"false":"true";
            $this->tpl->title = $title;
            $this->tpl->fill = !$fill?"false":"true";
            $this->tpl->class = $class;
        }
    }

    private function generateColors(int $qtd = 1)
    {
        $array = [];

        if ($qtd > 0) {
            for ($i = 0; $i < $qtd; $i++) {
                $r = rand(0, 100);    
                $g = rand(0, 100); 
                $b = rand(150, 255);

                $array[] = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
            }
        }

        return $array;
    }
}
?>
