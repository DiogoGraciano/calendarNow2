<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\elements;
use app\helpers\mensagem;
use app\controllers\abstract\controller;
use app\helpers\functions;
use app\models\agenda;
use app\models\agendaUsuario;
use app\models\empresa;
use app\models\login;
use app\view\layout\div;
use app\view\layout\map;
use app\view\layout\modal;

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

        $form->setElement($elements->titulo(1,"Adicionar Agenda"))
            ->setElement($elements->input("codigo_agenda","Codigo da Agenda",$codigo))
            ->setButton($elements->button("Adicionar","submit","submit","btn btn-primary w-100 pt-2 btn-block"));

        $div = new div("modal-empresa","modal fade",'tabindex="-1" aria-labelledby="modal-empresa" aria-hidden="true"');
        $div->show();

        $div = new div("encontrar");
        $div->addContent($this->loadMap());
        $div->addContent($form->parse());
        $div->show();
    }

    private function loadFormModal(empresa $empresa){

        $elements = new elements;

        $agendas = (new agenda())->getByFilter($empresa->id);
       
        $form = new form($this->url."actionModal","modal-".$empresa->id);

        $form->setHidden("empresa_id",$empresa->id)
            ->setElement($elements->label("Nome: ".$empresa->nome))
            ->setElement($elements->label("CPF/CNPJ: ".functions::formatCnpjCpf($empresa->cnpj)))
            ->setElement($elements->label("Telefone: ".functions::formatPhone($empresa->telefone)))
            ->setElement($elements->label("Email: ".$empresa->email));
        
        $form->setElement($elements->titulo(4,"Agendas"));
        foreach ($agendas as $agenda){
            $form->setElement($elements->checkbox("agenda[]",$agenda["nome"],value:$agenda["id"]));
        }

        $form->setButton($elements->buttonHtmx("Adicionar","adcionar",$this->url."encontrar/actionModal","#form-modal-".$empresa->id,class:"btn btn-primary w-100"));

        return $form;
    }

    private function loadMap():string
    {
        $map = new map();
        $empresas = (new empresa())->getByFilter(asArray:false);

        $elements = new elements;

        foreach ($empresas as $empresa){
            $modal = new modal("empresa-".$empresa->id,"Detalhe da Empresa",$this->loadFormModal($empresa)->parse(),"modal fade");
            $modal->show();
            $map->addMarker($empresa->latitude,$empresa->longitude,$elements->buttonModal($empresa->nome,$empresa->nome,"#empresa-".$empresa->id,class:"btn btn-link"),true);
        }
           
        return $map->parse();
    }

    public function actionModal(array $parameters = []):void
    {
        $user = login::getLogged();

        $agendas = $this->getValue("agenda");

        if($agendas){
            foreach ($agendas as $id){

                $agendaUsuario = new agendaUsuario;
                $agendaUsuario->id_agenda = $id;
                $agendaUsuario->id_usuario = $user->id;

                if($agendaUsuario->set())
                    mensagem::setSucesso("Agenda vinculada com sucesso");
                else 
                    mensagem::setErro("Agenda não encontrada");
            }
        }
        else 
            mensagem::setErro("Nenhuma agenda informada");

        $this->loadFormModal((new empresa)->get($this->getValue("empresa_id")))->show();
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
                mensagem::setErro("Agenda não encontrada");
        }

        $this->index();
    }
}