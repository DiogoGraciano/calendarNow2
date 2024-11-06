<?php

namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\view\layout\elements;
use app\view\layout\filter;
use app\models\conta;
use app\models\dre;
use app\models\login;
use app\view\layout\pagination;

class financeiro extends controller
{
    public function index(array $parameters = []):void
    {
        $elements = new elements;

        $cadastro = new consulta(true,"Consulta Financeiro");

        $user = login::getLogged();

        $dt_vencimento = $this->getValue("dt_vencimento");
        $dt_pagamento = $this->getValue("dt_pagamento");
        $nome = $this->getValue("nome");
        $status = $this->getValue("status");

        $filter = new filter($this->url."financeiro/index");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));

        $elements->setOptionsByArray(["P" => "Recebido/Pago", "A" => "Recebido/Pago com Atraso","C" => "Cancelado","I" => "A Receber/Pagar"]);
        $filter->addFilter(3,$elements->input("nome","Nome:",$nome))
                ->addFilter(3, $elements->input("dt_vencimento","Data Vencimento:",$dt_vencimento,false,false,type:"date",class:"form-control form-control-date"))
                ->addFilter(3, $elements->input("dt_pagamento","Data Pagamento:",$dt_pagamento,false,false,type:"date",class:"form-control form-control-date"))
                ->addFilter(3, $elements->select("status","Status",$status));

        $cadastro->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."financeiro/manutencao'"));
        $cadastro->addButtons($elements->buttonHtmx("Cancelar Conta","cancel","massCancel","#consulta-admin",confirmMessage:"Tem certeza que deseja cancelar?",includes:"#consulta-admin"));

        $cadastro->addColumns("1","Id","id")
                ->addColumns("15","Nome","nome")
                ->addColumns("12","Data Vencimento","dt_vencimento")
                ->addColumns("12","Data Pagamento","dt_pagamento")
                ->addColumns("12","Total","total")
                ->addColumns("10","Status","status");

        $conta = new conta;
        $dados = $conta->prepareList($conta->getByFilter($user->id_empresa,$nome,$dt_vencimento,$dt_pagamento,$status,$this->getLimit(),$this->getOffset()));

        $cadastro->setData($this->url."conta/manutencao",$this->url."conta/action",$dados,"id")
        ->addPagination(new pagination(
            $conta::getLastCount("getByFilter"),
            "financeiro/index",
            "#consulta-admin",
            limit:$this->getLimit()))
        ->addFilter($filter)
        ->show();
    }

    public function manutencao(array $parameters = [],?conta $conta = null):void
    {
        $id = "";
        
        $form = new form($this->url."financeiro/action/");

        $elements = new elements;

        if ($parameters && array_key_exists(0,$parameters)){
            $form->setHidden("cd",$parameters[0]);
            $id = $parameters[0];
        }
        
        $dado = $conta?:(new conta)->get($id);
        
        $form->setElement($elements->titulo(1,"Manutenção Conta"));
        $form->setElement($elements->input("nome","Nome:",$dado->nome,true));
        $form->setTwoElements($elements->input("dt_vencimento","Data Vencimento:",$dado->dt_vencimento,true,false,type:"date",class:"form-control form-control-date"),
                              $elements->input("dt_pagamento","Data Pagamento:",$dado->dt_pagamento,true,false,type:"date",class:"form-control form-control-date"));

        $user = login::getLogged();

        $dres = (new dre)->getByFilter($user->id_empresa);
        foreach ($dres as $dre){
            $elements->addOption($dre["id"],$dre["codigo"]." - ".$dre["descricao"]);
        }
        $form->setElement($elements->select("dre","DRE Administrativo:",$dado->id_dre));

        $elements->setOptionsByArray(["P" => "A Pagar", "R" => "A Receber"]);
        $tipo = $elements->select("tipo","Tipo:",$dado->tipo);

        $elements->setOptionsByArray(["C" => "Composto", "S" => "Simples"]);
        $tipo_juros = $elements->select("tipo_juros","Juros:",$dado->tipo_juros);

        $form->setTwoElements($tipo,$tipo_juros);
        
        $elements->setOptionsByArray(["P" => "Recebido/Pago", "A" => "Recebido/Pago com Atraso","C" => "Cancelado","I" => "A Receber/Pagar"]);
        $status = $elements->select("status","Status:",$dado->status);
        $valor = $elements->input("valor","Valor:",$dado->valor,true,type:"number",step:"0.01",max:999999999);

        $form->setTwoElements($status,$valor);

        $form->setButton($elements->button("Salvar","submit"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."feriado'"));
        $form->show();
    }

    public function action(array $parameters = [] ){

        $user = login::getLogged();

        if (isset($parameters[0])){
            (new conta)->remove(($parameters[0]));
            $this->index();
            return;
        }

        $id = intval(($this->getValue('cd')));
        $conta = new conta;
    
        $conta->id               = $id;
        $conta->nome             = $this->getValue('nome');
        $conta->tempo            = $this->getValue('tempo');
        $conta->valor            = $this->getValue('valor');
        $conta->id_empresa       = $user->id_empresa;

        $conta->set();

        $this->manutencao([$conta->id],$conta);
    }
}
