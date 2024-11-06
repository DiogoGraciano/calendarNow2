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

        $filter = new filter($this->url."feriado/index");

        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"))
                ->addFilter(3,$elements->input("nome","Nome:",$nome));

        $user = login::getLogged();

        $feriadoModel = new ModelsFeriado;

        $feriado = new consulta(false,"Consulta Feriados/Folgas");

        $feriado->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."feriado/manutencao'"));

        $feriado->addColumns("1","Id","id")
            ->addColumns("50","Nome","nome")
            ->addColumns("11","Ações","acoes")
            ->setData($this->url."feriado/manutencao",
                    $this->url."feriado/action/",
                    $feriadoModel->getByFilter($user->id_empresa,$nome,limit:$this->getLimit(),offset:$this->getOffset()),
                    "id")
            ->addPagination(new pagination(
                $feriadoModel::getLastCount("getByFilter"),
                "feriado/index",
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
        
        $form->setElement($elements->titulo(1,"Manutenção Feriado/Folgas"));
        $form->setElement($elements->input("nome","Nome:",$dado->nome,true));
        $form->setTwoElements($elements->input("dt_ini","Data Inicial:",$dado->dt_ini,true,false,type:"datetime-local",class:"form-control form-control-date"),
                              $elements->input("dt_fim","Data Final:",$dado->dt_fim,true,false,type:"datetime-local",class:"form-control form-control-date"));

        $user = login::getLogged();

        $funcionarios = (new funcionarioFeriado)->getFuncionarioByFeriado($dado->id);

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
                $funcionario->acoes = $elements->buttonHtmx("Desvincular","desvincular",$this->url."feriado/desvincularFuncionario/".$dado->id."/".$funcionario->id,"#form-manutencao");
                $table->addRow($funcionario->getArrayData());
            }

            $form->setElement($table->parse());
        }

        $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);

        $elements->addOption("todos","Todos");
        foreach ($funcionarios as $funcionario){
            $elements->addOption($funcionario->id,$funcionario->nome);
        }
        $form->setElement($elements->select("funcionario","Funcionario:",""));
        $form->setElement($elements->checkbox("repetir","Repetir",false,$dado->repetir));

        $form->setButton($elements->button("Salvar","submit"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."feriado'"));
        $form->show();
    }

    public function desvincularFuncionario($parameters = []):void
    {
        $id_feriado = ($parameters[0] ?? '');
        $id_funcionario = ($parameters[1] ?? '');

        if($id_feriado && $id_funcionario){
            $funcionarioFeriado = (new funcionarioFeriado);
            $funcionarioFeriado->id_feriado = $id_feriado;
            $funcionarioFeriado->id_funcionario = $id_funcionario;
            $funcionarioFeriado->remove();
        }

        mensagem::setErro("Feriado ou Funcionario não informados");
        $this->manutencao([$id_feriado]);
        return;
    }

    public function action(array $parameters):void
    {
        $user = login::getLogged();

        $feriado = new ModelsFeriado;
       
        if (isset($parameters[0])){
            $feriado->id = ($parameters[0]);
            $feriado->remove();
            $this->index(); 
            return;
        }
       
        $feriado->id               = intval($this->getValue('cd'));
        $feriado->id_funcionario   = $this->getValue('funcionario');
        $feriado->id_empresa       = intval($user->id_empresa);
        $feriado->nome             = $this->getValue('nome');
        $feriado->dt_ini           = $this->getValue('dt_ini');
        $feriado->dt_fim           = $this->getValue('dt_fim');
        $feriado->repetir          = $this->getValue('repetir') ?: 0;

        try{
            
            connection::beginTransaction();

            if ($feriado->set()){ 

                if($feriado->id_funcionario == "todos"){
                    $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);

                    foreach ($funcionarios as $funcionario){
                        $funcionarioFeriado = new funcionarioFeriado;
                        $funcionarioFeriado->id_funcionario = $funcionario->id;
                        $funcionarioFeriado->id_feriado = $feriado->id;
                        $funcionarioFeriado->set();
                    }
                }
                else{
                    $funcionarioFeriado = new funcionarioFeriado;
                    $funcionarioFeriado->id_funcionario = $feriado->id_funcionario;
                    $funcionarioFeriado->id_feriado = $feriado->id;
                    $funcionarioFeriado->set();
                }

                mensagem::setSucesso("Feriado salva com sucesso");
                connection::commit();
                $this->manutencao([$feriado->id],$feriado); 
                return;
            }

        }catch (\exception $e){
            mensagem::setSucesso(false);
            connection::rollBack();
            logger::error($e->getMessage()." ".$e->getTraceAsString());
            mensagem::setErro("Erro ao cadastrar feriado, tente novamente");
            $this->manutencao([$feriado->id],$feriado); 
            return;
        }

        mensagem::setSucesso(false);
        $this->manutencao([$feriado->id],$feriado); 
        return;
    }
}