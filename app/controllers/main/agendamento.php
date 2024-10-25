<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\view\layout\agenda;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\controllers\main\usuario as MainUsuario;
use diogodg\neoorm\connection;
use app\view\layout\elements;
use app\view\layout\filter;
use app\view\layout\tabela;
use app\view\layout\tabelaMobile;
use app\helpers\functions;
use app\helpers\mensagem;
use app\models\agenda as ModelsAgenda;
use app\models\agendamento as ModelsAgendamento;
use app\models\agendamentoItem;
use app\models\funcionario;
use app\models\login;
use app\models\servico;
use app\models\status;
use app\models\usuario;
use app\view\layout\modal;
use app\view\layout\pagination;

class agendamento extends controller{

    public const headTitle = "Agendamento";

    public const methods = ["loadEventos" => ["addHead" => false,"addHeader" => false,"addFooter" => false]];
  
    public function index(array $parameters = []):void
    {
        $id_agenda = "";
        $id_funcionario = intval($this->getValue("funcionario"));

        if (array_key_exists(0,$parameters))
            $id_agenda = ($parameters[0]);
        else
            $this->go("home");

        $elements = new elements;

        $filter = new filter($this->url."agendamento/index/".$parameters[0]);
        $filter->addbutton($elements->buttonHtmx("Buscar","buscar",$this->url."agendamento/index/".$parameters[0],"#agenda"));

        $funcionarioModel = new funcionario;

        $user = login::getLogged();

        if ($user->tipo_usuario == 3){
            $funcionarios = (new funcionario)->getByFilter(id_agenda:$id_agenda,asArray:false);
        }else{
            $funcionarios = (new funcionario)->getByFilter($user->id_empresa,id_agenda:$id_agenda,asArray:false);
        }

        if(!$funcionarios){
            mensagem::setErro("Nenhum funcionario cadastrado na agenda");
            if ($user->tipo_usuario == 3){
                $this->go("encontrar");
            }
            elseif($user->tipo_usuario == 1)
            {
                $this->go("funcionario");
            }
            else{
                $this->go("home");
            }
        }

        $i = 1;
        $firstFuncionario = "";
        foreach ($funcionarios as $funcionario){
            if ($i == 1){
                $firstFuncionario = $funcionario->id;
                $i++;
            }
            $elements->addOption($funcionario->id,$funcionario->nome);
        }

        $Dadofuncionario = $funcionarioModel->get(!$id_funcionario?$firstFuncionario:$id_funcionario);

        $filter->addFilter(6,$elements->select("funcionario","Funcionario",$Dadofuncionario->id));

        $agenda = new agenda();
        $agenda->addButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."home'"));
        $agenda->set(
            $this->url."agendamento/manutencao/".$parameters[0]."/".(!$id_funcionario?$firstFuncionario:$id_funcionario)."/",
            "agendamento/loadEventos/".$id_agenda."/".$Dadofuncionario->id,
            $Dadofuncionario->dias?:"seg,ter,qua,qui,sex",
            $Dadofuncionario->hora_ini?:"08:00",
            $Dadofuncionario->hora_fim?:"18:00"
        )->addFilter($filter)->show();
    }

    public function loadEventos(array $parameters = []):void
    {
        if(isset($this->urlQuery["start"],$this->urlQuery["end"],$parameters[0],$parameters[1])){
            $eventos = (new ModelsAgendamento)->getEventsbyFilter(
                        functions::dateTimeBd($this->urlQuery["start"]),
                        functions::dateTimeBd($this->urlQuery["end"]),
                        $parameters[0],
                        $parameters[1]
                    );
                    
            echo json_encode($eventos);
            return;
        }

        echo json_encode([]);
    }

    public function listagem(array $parameters = []):void
    {
        $elements = new elements;

        $cadastro = new consulta(true,"Consulta Agendamentos");

        $user = login::getLogged();

        $id_agenda = intval($this->getValue("agenda"));
        $id_funcionario = intval($this->getValue("funcionario"));
        $dt_ini = $this->getValue("dt_ini");
        $dt_fim = $this->getValue("dt_fim");

        if ($user->tipo_usuario == 3){
            $funcionarios = (new funcionario)->getByUsuario($user->id);
            $agendas = (new ModelsAgenda)->getByUsuario($user->id);
        }else{
            $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);
            $agendas = (new ModelsAgenda)->getByFilter($user->id_empresa,asArray:false);
        }

        $filter = new filter($this->url."agendamento/listagem");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));

        if ($agendas) {
            $elements->addOption("", "Selecione/Todos");
            foreach ($agendas as $agenda) {
                $elements->addOption($agenda->id, $agenda->nome);
            }
            $agenda = $elements->select("agenda","Agenda:",$id_agenda);

            $filter->addFilter(3, $agenda);
        }

        if ($funcionarios) {
            $elements->addOption("", "Selecione/Todos");
            foreach ($funcionarios as $funcionario){
                $elements->addOption($funcionario->id,$funcionario->nome);
            }

            $filter->addFilter(3,$elements->select("funcionario","Funcionario:",$id_funcionario));
        }

        $filter->addFilter(3, $elements->input("dt_ini","Data Inicial:",$dt_ini,false,false,type:"datetime-local",class:"form-control form-control-date"));
        $filter->addFilter(3, $elements->input("dt_fim","Data Final:",$dt_fim,false,false,type:"datetime-local",class:"form-control form-control-date"));

        $cadastro->addButtons($elements->button("Voltar","voltar","button","btn btn-primary","location.href='".$this->url."home'")); 
        $cadastro->addButtons($elements->buttonHtmx("Cancelar Agendamento","agendamentocancel","massCancel","#consulta-admin",confirmMessage:"Tem certeza que deseja cancelar?",includes:"#consulta-admin"));

        $cadastro->addColumns("1","Id","id")
                ->addColumns("10","CPF/CNPJ","cpf_cnpj")
                ->addColumns("15","Nome","nome")
                ->addColumns("15","Email","email")
                ->addColumns("10","Telefone","telefone")
                ->addColumns("10","Agenda","agenda")
                ->addColumns("10","Funcionario","fun_nome")
                ->addColumns("12","Data Inicial","dt_ini")
                ->addColumns("12","Data Final","dt_fim")
                ->addColumns("12","Total","total")
                ->addColumns("10","Status","status");

        $agendamento = new ModelsAgendamento;
        $dados = $agendamento->prepareList($agendamento->getByfilter($user->tipo_usuario == 3?null:$user->id_empresa,$user->tipo_usuario == 1?null:$user->id,$dt_ini,$dt_fim,false,$id_agenda,$id_funcionario,$this->getLimit(),$this->getOffset()));
       
        $cadastro->setData($this->url."agendamento/manutencao",$this->url."agendamento/action",$dados,"id")
        ->addPagination(new pagination(
            $agendamento::getLastCount("getByFilter"),
            "#consulta-admin",
            limit:$this->getLimit()))
        ->addFilter($filter)
        ->show();
    }

    public function massCancel(array $parameters = []):void
    {
        try{

            

            connection::beginTransaction();

            $ids = $this->getValue("massaction");

            $mensagem = "";
            $mensagem_erro = "";

            if ($ids){
                foreach ($ids as $id) {
                    $agendamento = (new ModelsAgendamento)->get($id);
                    if($agendamento->cancel())
                        $mensagem .= $agendamento->id." <br> ";
                    else
                        $mensagem_erro .= $agendamento->id." <br> ";
                }
                $mensagem_erro = rtrim($mensagem_erro," <br> ");
                $mensagem = rtrim($mensagem," <br> ");
            }
            else{
                mensagem::setErro("Selecione ao menos um agendamento");
                $this->listagem();
                return;
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente",$e->getMessage());
            connection::rollback();
        }

        if($mensagem)
            mensagem::setSucesso("Agendamentos cancelados com sucesso: <br>".$mensagem);

        if($mensagem_erro)
            mensagem::setErro("Agendamentos não cancelados: <br>".$mensagem_erro);

        connection::commit();

        $this->listagem();
    }
    
    public function manutencao(array $parameters = [],?ModelsAgendamento $agendamento = null,?agendamentoItem $agendamentoItem = null):void
    {
        $id = "";
        $dt_fim = "";
        $dt_ini = "";
        $id_funcionario = "";
        $id_agenda = "";

        $modal = new modal("modalUsuario","",(new MainUsuario)->formUsuario(tipo_usuario:4)->parse(),"modal modal-xl fade");
        $modal->show();

        $form = new form($this->url."agendamento/action/","agendamento");
    
        if (array_key_exists(3,$parameters)){
            $dt_fim = substr(base64_decode(str_replace("@","/",$parameters[3])),0,34);
            $dt_ini = substr(base64_decode(str_replace("@","/",$parameters[2])),0,34);
        }
        elseif (!array_key_exists(3,$parameters) && array_key_exists(2,$parameters)){
           $id = ($parameters[2]);
           $form->setHidden("cd",$parameters[2]);
        }
        if (array_key_exists(1,$parameters) && array_key_exists(0,$parameters)){
            $form->setHidden("id_funcionario",$parameters[1]);
            $id_funcionario = ($parameters[1]);
            $id_agenda = ($parameters[0]);
            $form->setHidden("id_agenda",$id_agenda);
        }else{
            $this->go("home");
        }

        $elements = new elements;

        $dado = $agendamento?:(new ModelsAgendamento)->get($id);

        $user = login::getLogged();

        if($user->tipo_usuario == 3)
            $statuses = (new status)->getUsuarioStatus();

        if ($user->tipo_usuario != 3){

            $statuses = (new status)->getAll();

            $usuarios = (new usuario)->getByTipoUsuarioAgenda([3,4],$id_agenda);

            $elements->addOption("","Selecionar/Vazio");
            foreach ($usuarios as $usuario){
                $elements->addOption($usuario->id,$usuario->nome);
            }

            $usuario = $elements->select("usuario","Usuario",$dado->id_usuario);

            $agendas = [];

            if ($user->tipo_usuario < 2)
                $agendas = (new ModelsAgenda)->getByFilter($user->id_empresa,asArray:false);
            else 
                $agendas = (new ModelsAgenda)->getByUsuario($user->id);

            foreach ($agendas as $agenda){
                $elements->addOption($agenda->id,$agenda->nome);
            }

            $agenda = $elements->select("agenda","Agenda",$dado->id_agenda?:$id_agenda);

            $form->addCustomElement("1 col-sm-12 mb-2",$elements->input("cor","Cor:",$dado->cor?:"#4267b2",false,false,"",type:"color",class:"form-control form-control-color"))
                ->addCustomElement("9 col-sm-12 usuario mb-2",$usuario)
                ->addCustomElement("2 col-sm-12 d-flex align-items-end mb-2",$elements->buttonModal("Novo","novoCliente","#modalUsuario","btn btn-primary w-100"),"w-100")
                ->addCustomElement(12,$agenda)
                ->setCustomElements();
        }

        foreach ($statuses as $status){
            $elements->addOption($status->id,$status->nome);
        }
        $status = $elements->select("status","Status",$dado->id_status);


        $form->addCustomElement(12,$status)->setCustomElements()
        ->addCustomElement("6",$elements->input("dt_ini","Data Inicial:",functions::dateTimeBd($dado->dt_ini?:$dt_ini),true,true,"",type:"datetime-local",class:"form-control form-control-date"),"dt_ini")
        ->addCustomElement("6",$elements->input("dt_fim","Data Final:",functions::dateTimeBd($dado->dt_fim?:$dt_fim),true,true,"",type:"datetime-local",class:"form-control form-control-date"),"dt_fim")
        ->setCustomElements();

        $Dadofuncionario = (new funcionario)->get($id_funcionario);
        
        $form->setElement($elements->label("Serviços"));

        $i = 0;
        $servicos = (new servico)->getByFuncionario($Dadofuncionario->id);
        if ($this->isMobile()){
            $table = new tabelaMobile();
            $table->addColumns("1","Selecionar","massaction");
        }else {
            $table = new tabela();
            $table->addColumns("1","","massaction");
        }
        $table->addColumns("68","Nome","nome");
        $table->addColumns("10","Quantidade","qtd_item");
        $table->addColumns("10","Tempo","tempo_item");
        $table->addColumns("12","Total","total_item");

        if($servicos){
            foreach ($servicos as $servico){
                
                if($dado->id && $servico->id)
                    $agendaItem = $agendamentoItem?:(new agendamentoItem)->getItemByServico($dado->id,$servico->id);
                else 
                    $agendaItem = null;

                if (isset($agendaItem->id_servico) && $agendaItem->id_servico == $servico->id){
                    $form->setHidden("id_item_".$i,$agendaItem->id);
                    $servico->massaction =  $elements->checkbox("servico_index_".$i,"",false,$agendaItem->id_servico?true:false,false,$agendaItem->id_servico,"checkbox","form-check-input check_item",'data-index-check="'.$i.'"');
                    $servico->qtd_item = $elements->input("qtd_item_".$i,"",$agendaItem->qtd_item,false,false,100,1,type:"number",class:"form-control qtd_item",extra:'data-index-servico="'.$i.'"');
                    $servico->tempo_item =  $elements->input("tempo_item_".$i,"",$agendaItem->tempo_item,false,true,class:"form-control",extra:'data-vl-base="'.$servico->tempo.'"');
                    $servico->total_item =   $elements->input("total_item_".$i,"",functions::formatCurrency($agendaItem->total_item),false,true,class:"form-control",extra:'data-vl-base="'.$servico->valor.'" data-vl-atual="'.$agendaItem->total_item.'"');
                    $table->addRow($servico->getArrayData());
                }
                else{
                    $servico->massaction = $elements->checkbox("servico_index_".$i,"",false,false,false,$servico->id,"checkbox","form-check-input check_item",'data-index-check="'.$i.'"');
                    $servico->qtd_item = $elements->input("qtd_item_".$i,"",1,false,false,type:"number",class:"form-control qtd_item",min:1,max:100,extra:'data-index-servico="'.$i.'"');
                    $servico->tempo_item = $elements->input("tempo_item_".$i,"",$servico->tempo,false,true,class:"form-control",extra:'data-vl-base="'.$servico->tempo.'"');
                    $servico->total_item = $elements->input("total_item_".$i,"",functions::formatCurrency($servico->valor),false,true,class:"form-control",extra:'data-vl-base="'.$servico->valor.'" data-vl-atual="'.$servico->valor.'"');
                    $table->addRow($servico->getArrayData()); 
                }
                $i++;
            }
        }else{
            mensagem::setErro("Nenhum serviço vinculado ao funcionario");
            if ($user->tipo_usuario == 3)
            {
                $this->go("encontrar");
            }
            elseif($user->tipo_usuario == 1)
            {
                $this->go("servico");
            }
            else
            {
                $this->go("home");
            }
        }
        $form->setElement($table->parse());

        $form->setHidden("qtd_servico",$i);

        $form->setElement($elements->textarea("obs","Observações:",$dado->obs,false,false,"","3","12"));

        $total = $dado->total?:0;

        $form->addCustomElement("1 col-2 d-flex align-items-end mb-2",$elements->label("Total"),"total");
        $form->addCustomElement("11 col-10",$elements->input("total","",$dado->total?functions::formatCurrency($dado->total):"R$ 0.00",false,true,"","text","form-control",'data-vl-total="'.$total.'"'));
        $form->setCustomElements();

        $form->setButton($elements->button("Salvar","submit"));
        $form->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."agendamento/index/".$parameters[0]."/".$parameters[1]."'",));
        
        $form->show();
    }

    public function action(array $parameters = []):void
    {
        if ($parameters){
            $agendamentoItem = (new agendamentoItem);
            $agendamentoItem->id_agendamento = $parameters[0];
            $agendamentoItem->removeByIdAgendamento();
            $agendamento = (new ModelsAgendamento);
            $agendamento->id = $parameters[0];
            $agendamento->remove();
            $this->listagem([]);
            return;
        }

        $id = ($this->getValue('cd'));
        $dt_ini = $this->getValue('dt_ini');
        $dt_fim = $this->getValue('dt_fim');
        $qtd_servico = intval($this->getValue("qtd_servico"));
        $status = intval($this->getValue("status"))?:1;
        $id_agenda = $this->getValue("id_agenda"); 
        $id_funcionario = ($this->getValue("id_funcionario"));
        $cor = $this->getValue('cor');
        $obs = $this->getValue('obs');
        $exists = false;

        $agendamento = new ModelsAgendamento;
        $agendamento->id                = $id;
        $agendamento->dt_ini            = $dt_ini;
        $agendamento->dt_fim            = $dt_fim;
        $agendamento->id_agenda         = $id_agenda;
        $agendamento->id_funcionario    = $id_funcionario;
        $agendamento->qtd_servico       = $qtd_servico;
        $agendamento->id_status         = $status;
        $agendamento->cor               = $cor;
        $agendamento->obs               = $obs;
        $agendamento->total             = 1;
        
        $user = login::getLogged();

        if($user->tipo_usuario == 3) {
            $agendamento->id_usuario = $user->id;
            $agendamento->titulo     = $user->nome;
            $agendamento->set();
        }
        elseif($usuarioId = $this->getValue('usuario')){
            $usuario = (new usuario)->get($usuarioId);
            $agendamento->id_usuario = $usuario->id;
            $agendamento->titulo     = $usuario->nome;
            $agendamento->set();
        }
        else{
            mensagem::setErro("Selecione um usuario ou cliente");
        }

        $agendamentoItem = new agendamentoItem();

        if ($agendamento->id && $qtd_servico){
            for ($i = 0; $i <= $qtd_servico; $i++) {
                $id_servico = $this->getValue('servico_index_'.$i);
                $qtd_item = $this->getValue('qtd_item_'.$i);
                $tempo_item = $this->getValue('tempo_item_'.$i);
                $id_agendamento_item = $this->getValue('id_item_'.$i);
                if($id_servico && $qtd_item && $tempo_item){
                    $agendamentoItem = new agendamentoItem();
                    $agendamentoItem->id = $id_agendamento_item;
                    $agendamentoItem->qtd_item = $qtd_item;
                    $agendamentoItem->tempo_item = $tempo_item;
                    $agendamentoItem->id_agendamento = $agendamento->id;
                    $agendamentoItem->id_servico = $id_servico;
                    $agendamentoItem->set();
                    $exists = true;
                }
                elseif ($id_agendamento_item && !$id_servico){
                    $agendamentoItem = new agendamentoItem();
                    $agendamentoItem->id = $id_agendamento_item;
                    $agendamentoItem->remove();
                }
                $id_servico = $qtd_item = $tempo_item = $id_agendamento_item = null;
            }

            if(!$agendamento->setTotal()){
                mensagem::setSucesso(false);
                $this->manutencao([$id_agenda,$id_funcionario,$agendamento->id],$agendamento,$agendamentoItem);
            }
    
            if (!$exists){
                mensagem::setErro("Selecione ao menos um serviço");
                mensagem::setSucesso(false);
                $this->manutencao([$id_agenda,$id_funcionario,$agendamento->id],$agendamento,$agendamentoItem);
            }
        }

        if(!mensagem::getErro()){
            mensagem::setSucesso("Agendamento Concluido");
        }else
            mensagem::setSucesso(false);

        $this->go("agendamento/index/".($id_agenda)."/".($id_funcionario));
    }
}