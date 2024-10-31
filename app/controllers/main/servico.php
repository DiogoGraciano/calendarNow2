<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\modal;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\view\layout\elements;
use app\helpers\functions;
use app\view\layout\filter;
use app\view\layout\tabela;
use app\view\layout\tabelaMobile;
use diogodg\neoorm\connection;
use app\helpers\mensagem;
use app\models\funcionario;
use app\models\grupoServico;
use app\models\login;
use app\models\servico as ModelsServico;
use app\models\servicoFuncionario;
use app\models\servicoGrupoServico;
use app\view\layout\pagination;

class servico extends controller{

    public const headTitle = "Serviço";

    public function index($parameters = [])
    {
        $servico = new consulta(true,"Consulta Serviço");
        
        $id_funcionario = $this->getValue("funcionario");
        $id_grupo_servico = $this->getValue("grupo_servico");
        $nome = $this->getValue("nome");

        $elements = new elements;

        $user = login::getLogged();

        $filter = new filter($this->url."servico/");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));

        $filter->addFilter(4,$elements->input("nome","Nome:",$nome));

        $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);

        if ($funcionarios){
            $elements->addOption("","Selecione/Todos");
            foreach ($funcionarios as $funcionario){
                $elements->addOption($funcionario->id,$funcionario->nome);
            }

            $funcionarios = $elements->select("funcionario","Funcionario",$id_funcionario);

            $form = new form($this->url."servico/massActionFuncionario/","massActionFuncionario","#consulta-admin","#consulta-admin");
            $form->setElement($funcionarios);
            $form->setButton($elements->button("Salvar","submitModalConsulta"));

            $modalAgenda = new modal("modalFuncionario","Vincular Funcionario a serviço",$form->parse());
            $modalAgenda->show();

            $filter->addFilter(4,$funcionarios);
        }

        $grupo_servicos = (new grupoServico)->getByFilter($user->id_empresa);

        if ($grupo_servicos){

            $elements->addOption("","Selecione/Todos");
            foreach ($grupo_servicos as $grupo_servico){
                $elements->addOption($grupo_servico["id"],$grupo_servico["nome"]);
            }

            $grupo_servico = $elements->select("grupo_servico","Grupo Serviço",$id_grupo_servico);

            $form = new form($this->url."servico/massActionGrupoServico/","massActionGrupoServico","#consulta-admin","#consulta-admin");
            $form->setElement($grupo_servico);
            $form->setButton($elements->button("Salvar","submitModalConsulta"));

            $modalAgenda = new modal("modalGrupo","Vincular Grupo de Serviço a serviço",$form->parse());
            $modalAgenda->show();

            $filter->addFilter(4,$grupo_servico);
        }

        $servicos = new ModelsServico;

        $dados = $servicos->prepareData($servicos->getByfilter(intval($user->id_empresa),$nome,intval($id_funcionario),intVal($id_grupo_servico)));

        $servico->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."servico/manutencao'"))
                ->addButtons($elements->buttonModal("Vincular  Serviço ao Funcionario", "massActionFuncionario", "#modalFuncionario"))
                ->addButtons($elements->buttonModal("Vincular Serviço ao Grupo", "massActionGrupoServico", "#modalGrupo"))
                ->addButtons($elements->button("Voltar","voltar","button","btn btn-primary","location.href='".$this->url."opcoes'"))
                ->addColumns("1","Id","id")
                ->addColumns("40","Nome","nome")
                ->addColumns("5","Tempo","tempo")
                ->addColumns("10","Valor","valor")
                ->addColumns("11","Ações","acoes")
                ->setData($this->url . "servico/manutencao", 
                        $this->url . "servico/action", 
                        $dados,
                        "id")
                ->addPagination(new pagination(
                    $servicos->getLastCount("getByFilter"),
                    "#consulta-admin",
                    limit:$this->getLimit()))
                ->addFilter($filter)
                ->show();
    }
    public function manutencao(array $parameters = [],?ModelsServico $servico = null){

        $user = login::getLogged();

        $id = "";
        
        $form = new form($this->url."servico/action/");

        $elements = new elements;

        if ($parameters && array_key_exists(0,$parameters)){
            $id = ($parameters[0]);
            $form->setHidden("cd",$parameters[0]);
        }

        $dado = $servico?:(new ModelsServico)->get($id);

        $form->setElement($elements->titulo(1,"Manutenção Serviço"))
            ->setElement($elements->input("nome","Nome:",$dado->nome,true),"nome");

        $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);
        $elements->addOption("","Nenhum");
        foreach ($funcionarios as $funcionario){
            $elements->addOption($funcionario->id,$funcionario->nome);
        }

        $select_funcionarios = $elements->select("funcionario","Funcionario:");

        $model_grupo_servico = (new grupoServico);

        $grupo_servicos = $model_grupo_servico->getByFilter($user->id_empresa);

        $elements->addOption("","Nenhum");
        foreach ($grupo_servicos as $grupo_servico){
            $elements->addOption($grupo_servico["id"],$grupo_servico["nome"]);
        }

        $select_grupo_servico = $elements->select("grupo_servico","Grupo Serviço:");

        $form->setTwoElements(
            $select_funcionarios,
            $select_grupo_servico,
            array("funcionario","grupo_servico")
        );

        $form->setTwoElements(
            $elements->input("tempo","Tempo de Trabalho:",$dado->tempo==""?"00:30":functions::removeSecondsTime($dado->tempo),true,false,type:"time"),
            $elements->input("valor","Valor:",$dado->valor?:0.00,true,false,type:"number",min:0,step:0.01),
            array("tempo","valor")
        );

        if($dado->id && $grupos_servicos = $model_grupo_servico->getVinculos($dado->id)){

            $this->isMobile() ? $table = new tabelaMobile() : $table = new tabela();

            $form->setElement($elements->label("Grupos de Serviços Vinculados"));

            $table->addColumns("1","ID","id");
            $table->addColumns("90","Nome","nome");
            $table->addColumns("10","Ações","acoes");

            foreach ($grupos_servicos as $grupos_servico){
                $grupos_servico["acoes"] = $elements->buttonHtmx("Desvincular","desvincular",$this->url."servico/desvincularGrupo/".($grupos_servico["id"])."/".($dado->id),"#form-manutencao");
                $table->addRow($grupos_servico);
            }

            $form->setElement($table->parse());
        }

        if($dado->id && $funcionarios = (new servicoFuncionario)->getByFuncionario($dado->id)){

            $this->isMobile() ? $table = new tabelaMobile() : $table = new tabela();

            $form->setElement($elements->label("Funcionarios Vinculados"));

            $table->addColumns("1","ID","id");
            $table->addColumns("90","Nome","nome");
            $table->addColumns("10","Ações","acoes");

            foreach ($funcionarios as $funcionario){
                $funcionario->acoes = $elements->buttonHtmx("Desvincular","desvincular",$this->url."servico/desvincularFuncionario/".($funcionario->id)."/".($dado->id),"#form-manutencao");
                $table->addRow($funcionario->getArrayData());
            }

            $form->setElement($table->parse());
        }

        $form->setButton($elements->button("Salvar","submit"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."servico'"));
        $form->show();
    }
    public function action(array $parameters = []):void
    {
        $user = login::getLogged();

        if (isset($parameters[0])){
            (new ModelsServico)->remove(($parameters[0]));
            $this->index();
            return;
        }

        $id = intval(($this->getValue('cd')));
        $id_grupo_servico  = intval($this->getValue('grupo_servico'));
        $id_funcionario = intval($this->getValue('funcionario'));
    
        $servico = new ModelsServico;
    
        $servico->id               = $id;
        $servico->id_grupo_servico = $id_grupo_servico;
        $servico->id_funcionario   = $id_funcionario;
        $servico->nome             = $this->getValue('nome');
        $servico->tempo            = $this->getValue('tempo');
        $servico->valor            = $this->getValue('valor');
        $servico->id_empresa       = $user->id_empresa;

        if ($servico->set()){ 
            if($id_grupo_servico){
                $servicoGrupoServico = new servicoGrupoServico();
                $servicoGrupoServico->id_grupo_servico = $id_grupo_servico;
                $servicoGrupoServico->id_servico = $servico->id;
                $servicoGrupoServico->set();
            }if($id_funcionario){
                $servicoGrupoServico = new servicoFuncionario();
                $servicoGrupoServico->id_funcionario = $id_funcionario;
                $servicoGrupoServico->id_servico = $servico->id;
                $servicoGrupoServico->set();
            }
        }

        $this->manutencao([$servico->id],$servico);
    }

    public function desvincularGrupo($parameters = []):void
    {
        $id_grupo = ($parameters[0] ?? '');
        $id_servico = ($parameters[1] ?? '');

        if($id_grupo && $id_servico){
            $servicoGrupoServico = new servicoGrupoServico();
            $servicoGrupoServico->id_grupo_servico = $id_grupo;
            $servicoGrupoServico->id_servico = $id_servico;
            $servicoGrupoServico->remove();
            $this->manutencao([$id_servico]);
            return;
        }

        mensagem::setErro("Grupo ou Servico não informados");
        $this->manutencao([$id_servico]);
    }

    public function desvincularFuncionario($parameters = []){

        $id_funcionario = ($parameters[0] ?? '');
        $id_servico = ($parameters[1] ?? '');

        if($id_funcionario && $id_servico){
            $servicoFuncionario = new servicoFuncionario();
            $servicoFuncionario->id_funcionario = $id_funcionario;
            $servicoFuncionario->id_servico = $id_servico;
            $servicoFuncionario->remove();
            $this->manutencao([$id_servico]);
            return;
        }

        mensagem::setErro("Funcionario ou Servico não informados");
        $this->manutencao([$id_servico]);
    }

    public function massActionFuncionario(){
        try{
            
            connection::beginTransaction();

            $ids = $this->getValue("massaction");
            $id_funcionario = $this->getValue("funcionario");

            $mensagem = "Funcionario vinculados com sucesso: ";
            $mensagem_erro = "Funcionario não vinculados: ";

            if ($ids && $id_funcionario){
                foreach($ids as $id) {
                    $servicoFuncionario = new servicoFuncionario;
                    $servicoFuncionario->id_servico = $id;
                    $servicoFuncionario->id_funcionario = $id_funcionario;
                    if($servicoFuncionario->set())
                        $mensagem .= $id." <br> ";
                    else
                        $mensagem_erro .= $id." <br> ";   
                }
            }
            else{
                mensagem::setErro("Não foi informado o funcionario");
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente");
            connection::rollback();
        }

        connection::commit();
        $this->index();
    }

    public function massActionGrupoServico(){
        try{

            

            connection::beginTransaction();

            $ids = $this->getValue("massaction");
            $id_grupo_servico = $this->getValue("grupo_servico");

            $mensagem = "Grupos vinculados com sucesso: ";
            $mensagem_erro = "Grupos não vinculados: ";

            if ($ids && $id_grupo_servico){
                foreach($ids as $id) {

                    $servicoGrupoServico = new servicoGrupoServico;
                    $servicoGrupoServico->id_servico = $id;
                    $servicoGrupoServico->id_grupo_servico = $id_grupo_servico;

                    if($servicoGrupoServico->set())
                        $mensagem .= $id_grupo_servico." <br> ";
                    else
                        $mensagem_erro .= $id_grupo_servico." <br> ";
                }
            }
            else{
                mensagem::setErro("Não foi informado o Grupos");
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente");
            connection::rollback();
        }

        connection::commit();
        $this->index();
    }
}