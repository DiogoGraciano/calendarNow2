<?php
namespace app\view\layout;

use app\helpers\functions;
use app\models\main\cidadeModel;
use app\models\main\empresaModel;
use app\models\main\estadoModel;
use app\view\layout\abstract\pagina;
use core\request;
use core\url;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class contato extends pagina
{
    public function __construct()
    {
        $this->setTemplate("contato.html");

        $empresa = (new calendarNow)->get(1);
        $empresa->cidade = cidadeModel::get($empresa->id_cidade)->nome;
        $empresa->estado = estadoModel::get($empresa->id_estado)->nome;

        if($empresa->telefone){
            $empresa->__TELEFONE__ = functions::onlynumber($empresa->telefone);
            $this->tpl->block("BLOCK_TELEFONE");
        }

        if($empresa->celular){
            $empresa->__CELULAR__ = functions::onlynumber($empresa->celular);
            $this->tpl->block("BLOCK_CELULAR");
        }

        if($empresa->rua && $empresa->cidade && $empresa->estado){
            $empresa->endereco = implode(", ",[$empresa->nome,$empresa->rua,$empresa->numero,$empresa->bairro,$empresa->cidade." - ".$empresa->estado,functions::formatCep($empresa->cep)]);
            $this->tpl->block("BLOCK_ENDERECO");
        }

        if($empresa->contato_email){
            $this->tpl->block("BLOCK_CONTATO_EMAIL");
        }

        if($empresa->contato_sac){
            if(str_contains($empresa->contato_sac,"@"))
                $empresa->__SAC__ = "mailto:".$empresa->contato_sac;
            else 
                $empresa->__SAC__ = "tel:".$empresa->contato_sac;
            $this->tpl->block("BLOCK_CONTATO_SAC");
        }

        if($empresa->contato_comercial){
            if(str_contains($empresa->contato_comercial,"@"))
                $empresa->__COMERCIAL__ = "mailto:".$empresa->contato_comercial;
            else 
                $empresa->__COMERCIAL__ = "tel:".$empresa->contato_comercial;

            $this->tpl->block("BLOCK_CONTATO_COMERCIAL");
        }

        if($empresa->longitude && $empresa->longitude){
            $this->tpl->block("BLOCK_MAPS");
        }

        if($empresa->horario_atendimento){
            $this->tpl->block("BLOCK_ATENDIMENTO");
        }

        $this->tpl->empresa = $empresa;

        $elements = new elements;

        $form = new form(url::getUrlBase()."contato/action","contato",target:"section.contato",hasRecapcha:true);
        $form->setElement($elements->input("nome","Nome:","",true,max:200))
             ->setElement($elements->input("email","Email:","",true,max:200,type:"email"))
             ->setElement($elements->input("telefone","Telefone:","",true,max:12,type:"tel"))
             ->setElement($elements->input("assunto","Assunto:","",true,max:100))
             ->setElement($elements->textarea("mensagem_envio","Mensagem:","",true,max:1000))
             ->setButton($elements->button("Enviar","enviar"));

        $this->tpl->form = $form->set()->parse();
    }
}
