<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\view\layout\elements;
use app\view\layout\tabela;
use app\view\layout\tabelaMobile;
use app\helpers\mensagem;
use app\view\layout\filter;
use diogodg\neoorm\connection;
use app\helpers\logger;
use app\models\feriado as ModelsFeriado;
use app\models\funcionarioFeriado;
use app\models\funcionario;
use app\models\login;
use app\view\layout\pagination;

final class feriado extends controller{

    public const headTitle = "Feriados/Folgas";

    public function index(array $parameters = []):void
    {
        $nome = $this->getValue("nome");

        $elements = new elements;

        $filter = new filter($this->url."index/");

        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"))
                ->addFilter(3,$elements->input("nome","Nome:",$nome));

        $user = login::getLogged();

        $feriadoModel = new ModelsFeriado;

        $feriado = new consulta(false,"Consulta Agenda");

        $feriado->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."feriado/manutencao'"));
        $feriado->addButtons($elements->button("Voltar","voltar","button","btn btn-primary","location.href='".$this->url."home'"));

        $feriado->addColumns("1","Id","id")
            ->addColumns("50","Nome","nome")
            ->addColumns("11","Ações","acoes")
            ->setData($this->url."agenda/manutencao",
                    $this->url."agenda/action/",
                    $feriadoModel->getByFilter($user->id_empresa,$nome,$this->getLimit(),$this->getOffset()),
                    "id")
            ->addPagination(new pagination(
                $feriadoModel::getLastCount("getByFilter"),
                "#consulta-admin",
                limit:$this->getLimit()))
            ->addFilter($filter)
            ->show();
    }

    public function manutencao(array $parameters = [],?ModelsFeriado $feriado = null):void
    {
        $id = "";
        
        $form = new form($this->url."feriado/action/");

        $elements = new elements;

        if ($parameters && array_key_exists(0,$parameters)){
            $form->setHidden("cd",$parameters[0]);
            $id = $parameters[0];
        }
        
        $dado = $feriado?:(new ModelsFeriado)->get($id);
        
        $form->setElement($elements->titulo(1,"Manutenção Agenda"));
        $form->setElement($elements->input("nome","Nome:",$dado->nome,true));

        $user = login::getLogged();

        $funcionarios = (new agendaFuncionario)->getFuncionarioByAgenda($dado->id);

        if($funcionarios){

            $form->setElement($elements->label("Funcionarios Vinculados"));

            if ($this->isMobile()){
                $table = new tabelaMobile();
            }else {
                $table = new tabela();
            }
            $table->addColumns("1","ID","id");
            $table->addColumns("90","Nome","nome");
            $table->addColumns("10","Ações","acoes");

            foreach ($funcionarios as $funcionario){
                $funcionario->acoes = $elements->buttonHtmx("Desvincular","desvincular",$this->url."funcionario/desvincularFuncionario/".$dado->id."/".$funcionario->id,"#form-manutencao");
                $table->addRow($funcionario->getArrayData());
            }

            $form->setElement($table->parse());
        }

        $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);

        $elements->addOption("","Nenhum");
        foreach ($funcionarios as $funcionario){
            $elements->addOption($funcionario->id,$funcionario->nome);
        }
        $form->setElement($elements->select("funcionario","Funcionario:",""));

        $form->setElement($elements->input("codigo","Codigo:",$dado->codigo,false,true));

        $form->setButton($elements->button("Salvar","submit"));
        if($dado->id)
            $form->setButton($elements->link($this->url."qrCode/agendaQrCode/".$dado->id,"Gerar QrCode","_blank","btn btn-primary w-100 btn-block"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."agenda'"));
        $form->show();
    }

    public function desvincularFuncionario($parameters = []):void
    {
        $id_agenda = ($parameters[0] ?? '');
        $id_funcionario = ($parameters[1] ?? '');

        if($id_agenda && $id_funcionario){
            $agendaFuncionario = (new agendaFuncionario);
            $agendaFuncionario->id_agenda = $id_agenda;
            $agendaFuncionario->id_funcionario = $id_funcionario;
            $agendaFuncionario->remove();
        }

        mensagem::setErro("Agenda ou Funcionario não informados");
        $this->manutencao([$id_agenda]);
        return;
    }

    public function action(array $parameters):void
    {
        $user = login::getLogged();

        $agenda = new ModelsAgenda;
       
        if (isset($parameters[0])){
            $agenda->id = ($parameters[0]);
            $agenda->remove();
            $this->index(); 
            return;
        }
       
        $agenda->id               = intval($this->getValue('cd'));
        $agenda->id_funcionario   = intval($this->getValue('funcionario'));
        $agenda->id_empresa       = intval($user->id_empresa);
        $agenda->codigo           = $this->getValue('codigo');
        $agenda->nome             = $this->getValue('nome');

        try{
            
            connection::beginTransaction();

            if ($agenda->set()){ 

                $agendaUsuario = new agendaUsuario;
                $agendaUsuario->id_usuario = $user->id;
                $agendaUsuario->id_agenda = $agenda->id;
                $agendaUsuario->set();

                if($agenda->id_funcionario){
                    $agendaFuncionario = new agendaFuncionario;
                    $agendaFuncionario->id_funcionario = $agenda->id_funcionario;
                    $agendaFuncionario->id_agenda = $agenda->id;
                    $agendaFuncionario->set();
                }

                mensagem::setSucesso("Agenda salva com sucesso");
                connection::commit();
                $this->manutencao([$agenda->id],$agenda); 
                return;
            }

        }catch (\exception $e){
            mensagem::setSucesso(false);
            connection::rollBack();
            logger::error($e->getMessage()." ".$e->getTraceAsString());
            mensagem::setErro("Erro ao cadastrar agenda, tente novamente");
            $this->manutencao([$agenda->id],$agenda); 
            return;
        }

        mensagem::setSucesso(false);
        $this->manutencao([$agenda->id],$agenda); 
        return;
    }
}