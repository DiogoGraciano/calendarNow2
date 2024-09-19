<?php 
namespace app\controllers\main;
use app\view\layout\head;
use app\view\layout\form;
use app\view\layout\elements;
use app\view\layout\footer;
use app\helpers\mensagem;
use app\controllers\abstract\controller;
use app\models\main\agendaModel;
use app\models\main\usuarioModel;

class encontrarController extends controller{

    public function index($parameters){

        $codigo = "";

        $head = new head();
        $head->show("Home","");

        if ($parameters && array_key_exists(0,$parameters)){
            $codigo = $parameters[0];
        }

        $elements = new elements;

        $form = new form($this->url."encontrar/action");

        $elements = new elements;

        $form->setinputs($elements->input("codigo_agenda","Codigo da Agenda",$codigo));
        $form->setButton($elements->button("Adicionar","submit","submit","btn btn-primary w-100 pt-2 btn-block"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 pt-2 btn-block","location.href='".$this->url."home"."'"));

        $form->show();
          
        $footer = new footer;
        $footer->show();
    }
    public function action(){
        $user = login::getLogged()

        $agenda = agendaModel::getByCodigo($this->getValue("codigo_agenda"));

        if ($agenda && array_key_exists(0,$agenda))
            agendaModel::setAgendaUsuario($user->id,$agenda[0]->id);
        else 
            mensagem::setErro("Agenda não encontrar");

        mensagem::setSucesso("Agenda vinculada com sucesso");
        $this->go("encontrar");
    }
}