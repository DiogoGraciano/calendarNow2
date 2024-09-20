<?php 
namespace app\controllers\main;
use app\view\layout\head;
use app\view\layout\form;
use app\view\layout\elements;
use app\view\layout\footer;
use app\helpers\mensagem;
use app\controllers\abstract\controller;
use app\models\agenda;
use app\models\agendaUsuario;
use app\models\login;
use app\view\layout\map;

class encontrar extends controller{

    public const headTitle = "Encontrar";

    public function index(array $parameters = []){

        $codigo = "";

        $head = new head();
        $head->show("Home","");

        if ($parameters && array_key_exists(0,$parameters)){
            $codigo = $parameters[0];
        }

        $elements = new elements;

        $form = new form($this->url."encontrar/action");

        $elements = new elements;

        $form->setInput($elements->titulo(1,"Encontrar Agenda"))
        ->setinput($elements->input("codigo_agenda","Codigo da Agenda",$codigo))
        ->setinput((new map)->parse())
        ->setButton($elements->button("Adicionar","submit","submit","btn btn-primary w-100 pt-2 btn-block"))
        ->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 pt-2 btn-block","location.href='".$this->url."home"."'"));

        $form->show();
          
        $footer = new footer;
        $footer->show();
    }

    public function action(){

        $user = login::getLogged();

        $agenda = (new agenda)->get($this->getValue("codigo_agenda"),"codigo");

        if ($agenda->id){

            $agendaUsuario = new agendaUsuario;
            $agendaUsuario->id_agenda = $agenda->id;
            $agendaUsuario->id_usuario = $user->id;

            if($agendaUsuario->set())
                mensagem::setSucesso("Agenda vinculada com sucesso");
            else 
                mensagem::setErro("Agenda não encontrar");
        }

        $this->index();
    }
}