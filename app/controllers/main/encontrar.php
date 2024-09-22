<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\elements;
use app\helpers\mensagem;
use app\controllers\abstract\controller;
use app\models\agenda;
use app\models\agendaUsuario;
use app\models\empresa;
use app\models\login;
use app\view\layout\map;
use app\view\layout\modal;
use app\view\layout\tab;

class encontrar extends controller{

    public const headTitle = "Encontrar";

    public function index(array $parameters = []):void
    {
        $codigo = "";

        if ($parameters && array_key_exists(0,$parameters)){
            $codigo = $parameters[0];
        }

        $elements = new elements;

        $form = new form($this->url."encontrar/action");

        $elements = new elements;

        $form->setInput($elements->titulo(1,"Encontrar Agenda"))
        ->setinput($elements->input("codigo_agenda","Codigo da Agenda",$codigo))
        ->setButton($elements->button("Adicionar","submit","submit","btn btn-primary w-100 pt-2 btn-block"))
        ->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 pt-2 btn-block","location.href='".$this->url."home"."'"));

        $tab = new tab();
        $tab->addTab("Mapa",$this->loadMap(),true);
        $tab->addTab("Codigo",$form->parse());
        $tab->show();
    }

    public function loadModal(array $parameters = []){
        if(isset($parameters[0])){
            $empresa = (new empresa())->get($parameters[0]);
            $agendas = (new agenda())->get($empresa->id,"id_empresa",0);



            new modal("empresa-".$empresa->id,"Detalhe da Empresa","")

            foreach ($agendas as $agenda){

            }
        }
    }

    private function loadMap():string
    {
        $map = new map();
        $empresas = (new empresa())->getAll();

        foreach ($empresas as $empresa){
            $map->addMarker($empresa->latitude,$empresa->longitude,$empresa->nome,true);
        }
           
        return $map->parse();
    }

    public function action(array $parameters = []):void
    {
        $user = login::getLogged();

        $agenda = (new agenda);
        if(isset($parameters[0]))
            $agenda->get($parameters[0],"codigo");
        else
            $agenda->get($this->getValue("codigo_agenda"),"codigo");

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