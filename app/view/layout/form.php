<?php
namespace app\view\layout;

use app\helpers\functions;
use app\view\layout\abstract\layout;
use app\helpers\mensagem;
use app\models\calendarNow;
use stdClass;

/**
 * Classe form é responsável por gerar um formulário dinâmico com base em um template HTML.
 */
class form extends layout
{
    private $inputs_custom = [];
    
    public function __construct(string $action,$nome = "manutencao",string $include = "",string $target = "",bool $hasRecapcha = false)
    {
        $this->setTemplate("form.html");
        $mensagem = new mensagem;
        $this->tpl->mensagem = $mensagem->parse();
        $this->tpl->form_nome = functions::createNameId($nome);
        $this->tpl->action = $action;

        if(!$target){
            $this->tpl->target = "#form-".$this->tpl->form_nome;
        }
        else{
            $this->tpl->target = $target;
        }
        
        if($include){
            $this->tpl->include = ' hx-include="'.$include.'"';
        }

        $this->tpl->block("BLOCK_START");

        if($hasRecapcha){
            $this->setHidden("g-recaptcha-{$nome}-response","");
            $empresa = (new calendarNow)->get(1);
            $recapcha = $this->getTemplate("recapcha.html");
            $recapcha->element_id = "g-recaptcha-{$nome}-response";
            $recapcha->empresa = $empresa;
            $this->tpl->recapcha = $recapcha->parse();
        }
    }

    public function setElement(string $input,string $nome = ""):form
    {
        $tpl = $this->getTemplate("inputs.html");
        $tpl->block_um_input = $input;
        $tpl->nome = $nome;
        $tpl->block("BLOCK_INPUT");
        $this->tpl->input = $tpl->parse();
        $this->tpl->block("BLOCK_INPUT");

        return $this;
    }

    public function setCustomElements(string $extra_class = ""):form
    {
        $tpl = $this->getTemplate("inputs.html",true);
        $tpl->extra_class = $extra_class;
        foreach ($this->inputs_custom as $custom) { 
            $tpl->tamanho = $custom->tamanho;
            $tpl->nome = $custom->nome;
            if(is_array($custom->input)){
                foreach ($custom->input as $input){
                    $tpl->block_um_input = $input;
                    $tpl->block("BLOCK_INPUT_CUSTOM"); 
                }
            }
            else{
                $tpl->block_um_input = $custom->input;
                $tpl->block("BLOCK_INPUT_CUSTOM");  
            }
            $tpl->block("BLOCK_DIV"); 
        }
        $tpl->block("BLOCK_CUSTOM");
        $this->tpl->input = $tpl->parse();
        $this->tpl->block("BLOCK_INPUT");
        $this->inputs_custom = [];

        return $this;
    }

    public function addCustomElement(int|string $tamanho,string|array $input,string $nome = ""):form
    {
        $custom = new stdClass;
        $custom->tamanho = $tamanho;
        $custom->nome = $nome;
        $custom->input = $input;
        $this->inputs_custom[] = $custom;
        return $this;
    }

    public function setTwoElements(string $input,string $input2, array $nomes = ["", ""]):form
    {
        $tpl = $this->getTemplate("inputs.html");
        $tpl->block_dois_input = $input;
        $tpl->nome_um = $nomes[0];
        $tpl->block_dois_input_dois = $input2;
        $tpl->nome_dois = $nomes[1];
        $tpl->block("BLOCK_INPUT_DOIS");
        $this->tpl->input = $tpl->parse();
        $this->tpl->block("BLOCK_INPUT");

        return $this;
    }

    public function setHidden(string $nome,$valor):form
    {
        $this->tpl->nome = $nome;
        $this->tpl->cd_value = $valor;
        $this->tpl->block("BLOCK_INPUT_HIDDEN");

        return $this;
    }

    public function setThreeElements(string $input,string $input2,string $input3, array $nomes = ["", "", ""]):form
    {
        $tpl = $this->getTemplate("inputs.html");
        $tpl->block_tres_input = $input;
        $tpl->nome_um = $nomes[0];
        $tpl->block_tres_input_dois = $input2;
        $tpl->nome_dois = $nomes[1];
        $tpl->block_tres_input_tres = $input3;
        $tpl->nome_dois = $nomes[2];
        $tpl->block("BLOCK_INPUT_TRES");
        $this->tpl->input = $tpl->parse();
        $this->tpl->block("BLOCK_INPUT");

        return $this;
    }

    public function setButton(string $button):form
    {
        $this->tpl->button = $button;
        $this->tpl->block("BLOCK_BUTTONS");

        return $this;
    }

    public function setButtonNoForm(string $button):form
    {
        $this->tpl->button_no = $button;
        $this->tpl->block("BLOCK_BUTTONS_NO_FORM");

        return $this;
    }

    public function show():void
    {
        $this->tpl->block("BLOCK_END");

        $this->tpl->show();
    }

    public function parse():string
    {
        $this->tpl->block("BLOCK_END");

        return $this->tpl->parse();
    }
}
