<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;

/**
 * Classe elements é responsável por gerar diversos elementos HTML, como botões, labels, checkboxes, inputs, textareas, selects e datalists.
 */
class elements extends pagina{

    private $options = []; 

    public function button(string $label,string $name,string $type="submit",string $class="btn btn-primary w-100 pt-2 btn-block",string $action="",string $extra="")
    {
        $this->setTemplate("elements.html");

        $this->tpl->type = $type;
        $this->tpl->name = $name;
        $this->tpl->class = $class;
        $this->tpl->action = $action;
        $this->tpl->extra = $extra;
        $this->tpl->label = $label;

        $this->tpl->block("BLOCK_BUTTON");   

        return $this->parse();
    }

    public function buttonModal(string $label,string $name,string $Idmodal,string $class="btn btn-primary",string $extra="")
    {
        $this->setTemplate("elements.html");

        $this->tpl->name = $name;
        $this->tpl->modal = $Idmodal;
        $this->tpl->class = $class;
        $this->tpl->extra = $extra;
        $this->tpl->label = $label;

        $this->tpl->block("BLOCK_BUTTON_MODAL");   

        return $this->parse();
    }

    public function buttonHtmx(string $label,string $name,string $action,string $target,string $swap = "outerHTML",string $confirmMessage = "",string $includes = "",string $class="btn btn-primary",string $extra="")
    {
        $this->setTemplate("elements.html");

        $this->tpl->name = $name;
        $this->tpl->class = $class;
        $this->tpl->action = $action;
        $this->tpl->target = $target;
        $this->tpl->swap = $swap;
        if($confirmMessage){
            $extra = $extra.' hx-confirm="'.$confirmMessage.'"';;
        }
        if($includes){
            $extra = $extra.' hx-include="'.$includes.'"';;
        }
        $this->tpl->extra = $extra;
        $this->tpl->name = $name;
        $this->tpl->label = $label;

        $this->tpl->block("BLOCK_BUTTON_HTMX");   

        return $this->parse();
    }

    public function label(string $titulo,string $class = ""){
        $this->setTemplate("elements.html");

        $this->tpl->titulo = $titulo;
        $this->tpl->class = $class;
    
        $this->tpl->block("BLOCK_LABEL");  
        
        return $this->parse();
    }

    public function titulo(int $tipo,string $titulo,string $class = "fw-normal text-title"){
        $this->setTemplate("elements.html");

        $this->tpl->tipo = $tipo;
        $this->tpl->titulo = $titulo;
        $this->tpl->class = $class;
    
        $this->tpl->block("BLOCK_TITULO");  
        
        return $this->parse();
    }

    public function link(string $link,string $value,string $target = "",string $class = "link-primary"){
        $this->setTemplate("elements.html");

        $this->tpl->link = $link;
        $this->tpl->value = $value;
        if($target)
            $this->tpl->target = 'target="'.$target.'"';
        $this->tpl->class = $class;
    
        $this->tpl->block("BLOCK_LINK");  
        
        return $this->parse();
    }

    public function checkbox(string $name,string $label="",bool $required=false,bool $checked=false,bool $readonly=false,string|int|float|null $value = 1,string $type="checkbox",string $class="form-check-input",string $extra=""){

        $this->setTemplate("elements.html");

        $this->tpl->type = $type;
        $this->tpl->name = $name;
        if ($label){
            $this->tpl->label = $label;
            $this->tpl->block("BLOCK_LABEL_CHECKBOX");  
        }
        
        $this->tpl->class = $class;
        if ($required)
            $extra = $extra.' required';
        if ($checked) 
            $extra = $extra.' checked';
        if ($readonly) 
            $extra = $extra.' onclick="return false;"';
        if ($value) 
            $extra = $extra.' value="'.$value.'"';

        $this->tpl->extra = $extra;

        $this->tpl->block("BLOCK_CHECKBOX");  
        
        return $this->parse();
    }
    
    public function input(string $name,string $label,string|int|float|null $value="",bool $required=false,bool $readonly=false,string $placeholder="",int|float|string $max = 1000,int|float|string $min = 1,string $type="text",float $step = 1,string $class="form-control",string $extra=""){

        $this->setTemplate("elements.html");

        $type = strtolower($type);
        
        $this->tpl->type = $type;
        if ($label){
            $this->tpl->label = $label;
            $this->tpl->block("BLOCK_LABEL_INPUT");  
        }
        $this->tpl->name = $name;

        if ($value)
            $this->tpl->value = $value;
        elseif($required)
            $class .= " is-invalid";
        
        $this->tpl->class = $class;

        if ($required == true)
            $extra = $extra." required";
        if ($readonly == true)
            $extra = $extra." readonly";
        if ($max){
            if($type == "number" || $type = "time" || $type = "datetime-local" || $type = "date")
                $extra = $extra.' max="'.$max.'"';
            else 
                $extra = $extra.' maxlength="'.$max.'"';
        }
        if ($min){
            if($type == "number" || $type = "time" || $type = "datetime-local" || $type = "date")
                $extra = $extra.' min="'.$min.'"';
            else 
                $extra = $extra.' minlength="'.$min.'"';
        }
        if ($placeholder)
            $extra = $extra.' placeholder="'.$placeholder.'"';
        if($type == "number" && $step)
            $extra = $extra.' step="'.$step.'"';
        
        $this->tpl->extra = $extra;

        $this->tpl->block("BLOCK_INPUT");   

        return $this->parse();
    }

    public function textarea(string $name,string $label,string|int|float|null $value = "",bool $required=false,bool $readonly=false,string $placeholder="",int $rows = 0,int $cols = 0,int $max = 0,int $min = 0,string $class="form-control",string $extra=""){

        $this->setTemplate("elements.html");

        if ($label){
            $this->tpl->label = $label;
            $this->tpl->block("BLOCK_LABEL_TEXTAREA");  
        }
        $this->tpl->name = $name;
        
        if ($value)
            $this->tpl->value = $value;
        elseif($required)
            $class .= " is-invalid";

        $this->tpl->class = $class;
        
        if ($required == true)
            $extra = $extra." required";
        if ($readonly == true)
            $extra = $extra." readonly";
        if ($rows)
            $extra = $extra.' rows="'.$rows.'"';
        if ($cols)
            $extra = $extra.' cols="'.$cols.'"';
        if ($max)
            $extra = $extra.' maxlength="'.$max.'"';
        if ($min)
            $extra = $extra.' minlength="'.$min.'"';
        if ($placeholder)
            $extra = $extra.' placeholder="'.$placeholder.'"';

        $this->tpl->extra = $extra;
        $this->tpl->block("BLOCK_TEXTAREA"); 
        
        return $this->parse();
    }

    public function select(string $name,string $label,string|int|float|null $value="",bool $required=false,string $class="form-select",string $extra=""){

        $this->setTemplate("elements.html");

        if ($label){
            $this->tpl->label = $label;
            $this->tpl->block("BLOCK_LABEL_SELECT");  
        }
        $this->tpl->name = $name;
        if($required && !$value)
            $class .= " is-invalid";
        $this->tpl->class = $class;
        if ($required == true)
            $this->tpl->extra = $extra." required";

        foreach ($this->options as $option){
            if(isset($option->value) && isset($option->label)){
                $this->tpl->value = $option->value;
                if ($value === $option->value)
                    $this->tpl->extra_option = "selected";
                $this->tpl->label = $option->label;
                $this->tpl->block("BLOCK_OPTION_SELECT");
                $this->tpl->extra_option = "";
            }
        }
        
        $this->tpl->block("BLOCK_SELECT"); 

        $this->options = [];
        
        return $this->parse();
    }

    public function datalist(string $label,string $name,string|int|float|null $value="",bool $required=false,string $class="form-control",string $extra=""){

        $this->setTemplate("elements.html");

        if ($label){
            $this->tpl->label = $label;
            $this->tpl->block("BLOCK_LABEL_DATALIST");  
        }
        $this->tpl->name = $name;
        $this->tpl->value = $value;
        if($required && !$value)
            $class .= " is-invalid";
        $this->tpl->class = $class;
        if ($required == true)
            $this->tpl->extra = $extra." required";

        foreach ($this->options as $option){
            if(isset($option->value) && isset($option->label)){
                $this->tpl->value = $option->value;
                $this->tpl->label = $option->label;
                $this->tpl->extra_option = $option->extra_option;
                $this->tpl->block("BLOCK_OPTION_DATALIST");
            }
        }
        
        $this->tpl->block("BLOCK_DATALIST");  

        $this->options = [];

        return $this->parse();
    }

    public function img(string $img,string $alt,string $extra = "")
    {
        $this->setTemplate("elements.html");
        $this->tpl->img = $img;
        $this->tpl->alt = $alt;
        $this->tpl->extra = $extra;
        $this->tpl->block("BLOCK_IMG"); 
        return $this->parse();
    }

    public function embed(string $caminho,string $type = "application/pdf",string $extra = "")
    {
        $this->setTemplate("elements.html");
        $this->tpl->caminho = $caminho;
        $this->tpl->type = $type;
        $this->tpl->extra = $extra;
        $this->tpl->block("BLOCK_ENBED"); 
        return $this->parse();
    }

    public function setOptions(array $dados,$coluna_vl,$coluna_nm){
        if ($dados){
            foreach ($dados as $dado){
                if(is_subclass_of($dado,"app\db\db")){
                    $dado = $dado->getArrayData();
                }
                if(isset($dado[$coluna_vl]) && isset($dado[$coluna_nm]))
                    $this->addOption($dado[$coluna_vl],$dado[$coluna_nm]);
            }
        }
    }

    public function addOption(string|int|float|null $value,string $label,string $extra_option=""){
        if (is_int($value) || is_float($value))
            $this->options[] = json_decode('{"value":'.$value.',"label":"'.$label.'","extra_option":"'.$extra_option.'"}');
        else
            $this->options[] = json_decode('{"value":"'.$value.'","label":"'.$label.'","extra_option":"'.$extra_option.'"}');

        return $this;
    }


}


