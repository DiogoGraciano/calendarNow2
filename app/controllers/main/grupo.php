<?php 
namespace app\controllers\main;
use app\view\layout\head;
use app\view\layout\form;
use app\view\layout\elements;
use app\controllers\abstract\controller;
use app\view\layout\consulta;
use app\view\layout\footer;
use app\view\layout\filter;
use app\view\layout\tabela;
use app\view\layout\tabelaMobile;
use app\models\grupoFuncionario;
use app\models\grupoServico;
use app\models\login;
use app\view\layout\pagination;

final class grupo extends controller{

    public const headTitle = "Grupo";

    public function index($parameters = array())
    {
        $nome = $this->getValue("nome");

        $tipo_grupo = null;

        if (array_key_exists(0,$parameters)){
            $tipo_grupo = ($parameters[0]);
        }

        $user = login::getLogged();

        if ($tipo_grupo == "funcionario"){
            $dados = (new grupoFuncionario);
        }elseif ($tipo_grupo == "servico"){
            $dados = (new grupoServico);
        }else    
            $this->go("home");

        $elements = new elements;

        $filter = new filter($this->url."agenda/index/");

        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"))
                ->addFilter(3,$elements->input("nome","Nome:",$nome));

        $cadastro = new consulta(false,"Consulta Grupo");

        $cadastro->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."grupo/manutencao/".($tipo_grupo)."'"))
                ->addColumns("1","Id","id")
                ->addColumns("70","Nome","nome")
                ->addColumns("15","Ações","acoes")
                ->setData($this->url . "grupo/manutencao/".($tipo_grupo), 
                        $this->url . "grupo/action/".($tipo_grupo), 
                        $dados->getByFilter($user->id_empresa,$nome,$this->getLimit(),$this->getOffset()),
                        "id")
                ->addPagination(new pagination(
                    $dados->getLastCount("getByFilter"),
                    "grupo/index",
                    "#consulta-admin",
                    limit:$this->getLimit()))
                ->addFilter($filter)
                ->show();
    }

    public function manutencao(array $parameters = [],grupoServico|grupoFuncionario $grupo = null):void
    {

        $head = new head();
        $head->show("Cadastro","");

        $id = null;
        $tipo_grupo = null;

        if (array_key_exists(0,$parameters))
            $tipo_grupo = ($parameters[0]);
        else 
            $this->go("home");

        $form = new form($this->url."grupo/action/".$parameters[0]);
        
        if (array_key_exists(1,$parameters)){
            $form->setHidden("cd",$parameters[1]);
            $id = ($parameters[1]);
        }

        if ($tipo_grupo == "funcionario"){
            $model = (new grupoFuncionario);
        }elseif ($tipo_grupo == "servico"){
            $model = (new grupoServico);
        }

        $dado = $grupo?:$model->get($id);

        $elements = new elements;

        $form->setElement($elements->titulo(1,"Manutenção Grupo"))->setElement(
            $elements->input("nome","Nome",$dado->nome,true)
        );

        if($dado->id && $vinculos = $model->getVinculos($dado->id)){

            $form->setElement($elements->label("Vinculados"));

            $this->isMobile() ? $table = new tabelaMobile() : $table = new tabela();
            
            $table->addColumns("1","ID","id");
            $table->addColumns("90","Nome","nome");
            $table->addColumns("10","Ações","acoes");

            foreach ($vinculos as $vinculo){
                $vinculo->acoes = $elements->buttonHtmx("Desvincular","desvincular",$this->url."grupo/desvincularGrupo/".$tipo_grupo."/".($vinculo->id)."/".($dado->id),"#form-manutencao");
                $table->addRow($vinculo->getArrayData());
            }

            $form->setElement($table->parse());
        }

        $form->setButton($elements->button("Salvar","submit"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 pt-2 btn-block","location.href='".$this->url."grupo/index/".$parameters[0]."'"));

        $form->show();

        $footer = new footer;
        $footer->show();
    }

    public function action(array $parameters = []):void
    {
        if (array_key_exists(0,$parameters))
            $tipo_grupo = ($parameters[0]);
        else 
            $this->go("home");

        if ($tipo_grupo == "funcionario")
            $grupo = new grupoFuncionario;
        elseif ($tipo_grupo == "servico")
            $grupo = new grupoServico;
        else
            $this->go("home");

        if (array_key_exists(1,$parameters)){
            $grupo->id = ($parameters[1]);
            $grupo->remove();
            $this->index([$tipo_grupo]);
            return;
        }

        $id = intval(($this->getValue("cd")));
        $nome  = $this->getValue('nome');

        $grupo->id   = $id;
        $grupo->nome = $nome;
        $grupo->id_empresa = login::getLogged()->id_empresa;
        $grupo->set();

        $this->manutencao([$tipo_grupo,$grupo->id],$grupo);
    }

}

