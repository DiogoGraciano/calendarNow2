<?php

namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\helpers\mensagem;
use app\view\layout\elements;
use app\view\layout\filter;
use app\models\conta;
use app\models\dre as ModelsDre;
use app\models\login;
use app\view\layout\modal;
use app\view\layout\pagination;

class dre extends controller
{
    public function index(array $parameters = []){
        $elements = new elements;

        $cadastro = new consulta(false,"Consulta Contas DRE");

        $user = login::getLogged();

        $descricao = $this->getValue("descricao");

        $filter = new filter($this->url."dre/index");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));
        $filter->addFilter(3,$elements->input("descricao","Descricao:",$descricao));

        $cadastro->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."dre/manutencao'"));

        $cadastro->addColumns("40","Codigo","codigo")
                ->addColumns("40","Descricao","descricao")
                ->addColumns("15","Ações","acoes");

        $dre = new ModelsDre;
        $dados = $dre->prepareList($dre->getByFilter($user->id_empresa,$descricao,$this->getLimit(),$this->getOffset()));

        $cadastro->setData($this->url."dre/manutencao",$this->url."dre/action",$dados,"id")
                ->addPagination(new pagination(
                    $dre::getLastCount("getByFilter"),
                    "dre/index",
                    "#consulta-admin",
                    limit:$this->getLimit()))
                ->addFilter($filter)
                ->show();
    }

    public function manutencao(array $parameters = [],?ModelsDre $dre = null){
        $id = "";
        
        $form = new form($this->url."dre/action/");

        $elements = new elements;

        if ($parameters && array_key_exists(0,$parameters)){
            $form->setHidden("cd",$parameters[0]);
            $id = $parameters[0];
        }
        
        $dado = $dre?:(new ModelsDre)->get($id);
        
        $form->setElement($elements->titulo(1,"Manutenção DRE"));
        $form->setElement($elements->input("codigo","Codigo:",$dado->codigo,true,type:"number",max:9999999999));
        $form->setElement($elements->input("descricao","Descrição:",$dado->descricao,true));
        $form->setButton($elements->button("Salvar","submit"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."dre'"));
        $form->show();
    }

    public function action(array $parameters):void
    {
        $user = login::getLogged();

        $dre = new ModelsDre;
       
        if (isset($parameters[0])){
            $dre = $dre->get($parameters[0]);

            if($dre->id_empresa){
                $dre->remove();
            }
            else{
                mensagem::setErro("Não é possivel excluir essa conta, Apenas é permitido excluir contas cadastradas pela a empresa");
            }
                
            $this->index(); 
            return;
        }

        $dre = $dre->get($this->getValue('cd'));

        if($dre->id && !$dre->id_empresa){
            mensagem::setErro("Não é possivel editar essa conta, Apenas é permitido editar contas cadastradas pela a empresa");
            $this->go("dre/index");
        }
        
        $dre->id_empresa       = intval($user->id_empresa);
        $dre->codigo           = $this->getValue('codigo');
        $dre->descricao        = $this->getValue('descricao');
        $dre->set();
            
        $this->manutencao([$dre->id],$dre); 
        return;
    }
}
