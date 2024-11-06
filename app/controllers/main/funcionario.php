<?php

namespace app\controllers\main;

use app\view\layout\form;
use app\view\layout\elements;
use app\helpers\functions;
use app\controllers\abstract\controller;
use app\helpers\logger;
use app\view\layout\consulta;
use app\view\layout\filter;
use app\helpers\mensagem;
use app\view\layout\tabela;
use app\view\layout\tabelaMobile;
use app\view\layout\modal;
use diogodg\neoorm\connection;
use app\models\agenda;
use app\models\agendaFuncionario;
use app\models\funcionario as funcionarioModel;
use app\models\funcionarioGrupoFuncionario;
use app\models\grupoFuncionario;
use app\models\login;
use app\models\usuario;
use app\view\layout\pagination;

final class funcionario extends controller
{
    public const headTitle = "Funcionario";

    public function index(array $parameters = [])
    {
        $cadastro = new consulta(true,"Consulta Funcionario");
        
        $user = login::getLogged();

        $elements = new elements();
        
        $nome = $this->getValue("nome");
        $id_agenda = intval($this->getValue("agenda"));
        $id_grupo_funcionarios = intval($this->getValue("grupo_funcionarios"));

        $filter = new filter($this->url . "funcionario/index/");
        $filter->addbutton($elements->button("Buscar", "buscar", "submit", "btn btn-primary pt-2"));
        $filter->addFilter(3, $elements->input("nome", "Nome:", $nome));

        $agendas = (new agenda)->getByFilter($user->id_empresa);

        if ($agendas) {
            $form = new form($this->url . "funcionario/massActionAgenda/", "massActionAgenda","#consulta-admin","#consulta-admin");

            $elements->addOption("", "Selecione/Todos");
            foreach ($agendas as $agenda) {
                $elements->addOption($agenda["id"], $agenda["nome"]);
            }
            $agenda = $elements->select("agenda","Agenda:",$id_agenda);

            $form->setElement($agenda);
            $form->setButton($elements->button("Salvar", "submitModalConsulta"));
            $modalAgenda = new modal("modalAgenda","Vincular Funcionario a Agenda",$form->parse());
            $modalAgenda->show();

            $filter->addFilter(3, $agenda);
        }

        $grupo_funcionarios = (new grupoFuncionario)->getByFilter($user->id_empresa);

        if ($grupo_funcionarios) {
            $elements->addOption("", "Selecione/Todos");
            foreach ($grupo_funcionarios as $grupo_funcionario) {
                $elements->addOption($grupo_funcionario["id"], $grupo_funcionario["nome"]);
            }

            $grupo_funcionario = $elements->select("grupo_funcionario","Grupo Funcionario", $id_grupo_funcionarios);

            $form = new form($this->url . "funcionario/massActionGrupoFuncionario/","massActionGrupoFuncionario","#consulta-admin","#consulta-admin");

            $form->setElement($grupo_funcionario);
            $form->setButton($elements->button("Salvar", "submitModalConsulta"));
            $modalGrupo = new modal("modalGrupo","Vincular Grupo de Funcionario ao Funcionario",$form->parse());
            $modalGrupo->show();

            $filter->addFilter(3, $grupo_funcionario);
        }

        $funcionario = new funcionarioModel;

        $dados = $funcionario->prepareData($funcionario->getByFilter($user->id_empresa,$nome,intval($id_agenda),intval($id_grupo_funcionarios),$this->getLimit(),$this->getOffset()));
        
        $cadastro->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."funcionario/manutencao'"))
            ->addButtons($elements->buttonModal("Vincular Agenda ao Funcionario", "massActionAgenda", "#modalAgenda"))
            ->addButtons($elements->buttonModal("Vincular Funcionario ao Grupo", "massActionGrupoFuncionario", "#modalGrupo"))
            ->addColumns("1", "Id", "id")
            ->addColumns("10", "CPF/CNPJ", "cpf_cnpj")
            ->addColumns("15", "Nome", "nome")
            ->addColumns("15", "Email", "email")
            ->addColumns("11", "Telefone", "telefone")
            ->addColumns("5", "Inicio Expediente", "hora_ini")
            ->addColumns("5", "Fim Expediente", "hora_fim")
            ->addColumns("5", "Inicio Almoço", "hora_almoco_ini")
            ->addColumns("5", "Fim Almoço", "hora_almoco_fim")
            ->addColumns("14", "Dias", "dia")
            ->addColumns("14", "Ações", "acoes")
            ->setData($this->url . "funcionario/manutencao", 
                        $this->url . "funcionario/action", 
                        $dados,
                        "id")
            ->addPagination(new pagination(
                $funcionario->getLastCount("getByFilter"),
                "funcionario/index",
                "#consulta-admin",
                limit:$this->getLimit()))
            ->addFilter($filter)
            ->show();
    }

    public function manutencao(array $parameters = [],?funcionarioModel $funcionario = null,?usuario $usuario = null):void
    {
        $form = new form($this->url . "funcionario/action");
        
        $id = $parameters[0] ?? "";

        $user = login::getLogged();

        $dadoFuncionario = $funcionario ?: (new funcionarioModel)->get($id);
        $dado = $usuario ?: (new usuario)->get($dadoFuncionario->id);
        
        $form->setHidden("cd", $dado->id);
        $form->setHidden("id_funcionario", $dadoFuncionario->id);
        $form->setHidden("id_empresa", $user->id_empresa);

        $elements = new elements();
        $form
        ->setElement($elements->titulo(1,"Manutenção Funcionario"))
        ->setTwoElements(
            $elements->input("nome","Nome",$dado->nome,true),
            $elements->input("cpf_cnpj","CPF/CNPJ:",$dado->cpf_cnpj ? functions::formatCnpjCpf($dado->cpf_cnpj) : "",true),
            ["nome", "cpf_cnpj"]
        );

        $form->setThreeElements(
            $elements->input("email","Email",$dado->email,false,false,type:"email"),
            $elements->input("senha","Senha","",$dado->senha?false:true,false,type:"password"),
            $elements->input("telefone","Telefone",functions::formatPhone($dado->telefone),true,type:"tel"),
            ["email", "senha", "telefone"]
        );

        $this->isMobile() ? $table = new tabelaMobile() : $table = new tabela();

        if ($dadoFuncionario->id && $agendas = (new agendaFuncionario)->getAgendaByFuncionario($dadoFuncionario->id)) {

            $form->setElement($elements->label("Agendas Vinculadas"));

            $table->addColumns("1", "ID", "id");
            $table->addColumns("90", "Nome", "nome");
            $table->addColumns("10", "Ações", "acoes");

            foreach ($agendas as $agenda) {
                $agenda->acoes = $elements->buttonHtmx("Desvincular", "desvincular",$this->url."funcionario/desvincularAgenda/".$agenda->id. "/".$dadoFuncionario->id,"#form-manutencao");
                $table->addRow($agenda->getArrayData());
            }

            $form->setElement($table->parse());
        }

        $agendas = (new agenda)->getByFilter($user->id_empresa);

        $elements->addOption("", "Nenhum");
        foreach ($agendas as $agenda) {
            $elements->addOption($agenda["id"], $agenda["nome"]);
        }

        $select_agenda = $elements->select("id_agenda","Agenda");

        $form->setTwoElements(
            $select_agenda,
            $elements->input("espacamento_agenda","Subdivisão de horario em minutos",$dadoFuncionario->espacamento_agenda?:30,type:"number",min:10,max:500)
        );

        $grupos_funcionario = (new grupoFuncionario);

        $grupos_funcionarios = [];

        if ($dadoFuncionario->id && $grupos_funcionarios = $grupos_funcionario->getVinculos($dadoFuncionario->id)) {

            $form->setElement($elements->label("Grupos de Funcionario Vinculados"));

            $table->addColumns("1", "ID", "id");
            $table->addColumns("90", "Nome", "nome");
            $table->addColumns("10", "Ações", "acoes");

            foreach ($grupos_funcionarios as $grupo_funcionario) {
                $grupo_funcionario->acoes = $elements->buttonHtmx("Desvincular", "desvincular",$this->url."funcionario/desvincularGrupo/".$grupo_funcionario->id."/".$dadoFuncionario->id,"#form-manutencao");
                $table->addRow($grupo_funcionario->getArrayData());
            }

            $form->setElement($table->parse());
        }

        $grupos_funcionarios = $grupos_funcionario->getByFilter($user->id_empresa);

        $elements->addOption("", "Nenhum");
        foreach ($grupos_funcionarios as $grupo_funcionario) {
            $elements->addOption($grupo_funcionario["id"], $grupo_funcionario["nome"]);
        }
        $id_grupo_funcionario = $elements->select("id_grupo_funcionario","Grupo de Funcionarios");

        $form->setElement($id_grupo_funcionario);

        $form->setTwoElements(
            $elements->input("hora_ini", "Hora Inicial de Trabalho", functions::removeSecondsTime($dadoFuncionario->hora_ini ?: "08:00"), true, false,type:"time"),
            $elements->input("hora_fim", "Hora Final de Trabalho", functions::removeSecondsTime($dadoFuncionario->hora_fim ?: "18:00"), true, false,type:"time"),
            ["hora_ini", "hora_fim"]
        );

        $form->setTwoElements(
            $elements->input("hora_almoco_ini", "Hora Inicial de Almoço", functions::removeSecondsTime($dadoFuncionario->hora_almoco_ini ?: "12:00"), true, false,type:"time"),
            $elements->input("hora_almoco_fim", "Hora Final de Almoço", functions::removeSecondsTime($dadoFuncionario->hora_almoco_fim ?: "13:30"), true, false,type:"time"),
            ["hora_almoco_ini", "hora_almoco_fim"]
        );

        $form->setElement($elements->label("Dias de trabalho na Semana"));

        $checkDias = explode(",", $dadoFuncionario->dias ?: "");
        $diasSemana = ["dom" => "Domingo", "seg" => "Segunda", "ter" => "Terça", "qua" => "Quarta", "qui" => "Quinta", "sex" => "Sexta", "sab" => "Sábado"];

        foreach ($diasSemana as $key => $value) {
            $form->addCustomElement(2, $elements->checkbox($key, $value, false, in_array($key, $checkDias), value: $key), $key);
        }

        $form->setCustomElements();
        $form->setButton($elements->button("Salvar", "submit"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='" . $this->url . "funcionario/index/'"));
        $form->show();
    }

    public function desvincularGrupo(array $parameters = []):void
    {
        $id_grupo = ($parameters[0] ?? '');
        $id_funcionario = ($parameters[1] ?? '');

        if ($id_grupo && $id_funcionario) {
            $grupoFuncionario = new funcionarioGrupoFuncionario;
            $grupoFuncionario->id_grupo = $id_grupo;
            $grupoFuncionario->id_funcionario = $id_funcionario;
            $grupoFuncionario->remove();
            $this->manutencao([$id_funcionario]);
            return;
        }

        mensagem::setErro("Grupo ou Funcionario não informados");
        $this->manutencao([$id_funcionario]);
    }

    public function desvincularAgenda(array $parameters = []):void
    {
        $id_agenda = $parameters[0] ?? '';
        $id_funcionario = $parameters[1] ?? '';

        if ($id_agenda && $id_funcionario) {
            $agendaFuncionario = new agendaFuncionario;
            $agendaFuncionario->id_agenda = $id_agenda;
            $agendaFuncionario->id_funcionario = $id_funcionario;
            $agendaFuncionario->remove();
            $this->manutencao([$id_funcionario]);
            return;
        }

        mensagem::setErro("Agenda ou Funcionario não informados");
        $this->manutencao([$id_funcionario]);
    }

    public function action(array $parameters = []):void
    {
        $id_grupo_funcionario = $this->getValue('id_grupo_funcionario');
        $id_agenda = $this->getValue('id_agenda');
        $cpf_cnpj = $this->getValue('cpf_cnpj');
        $email = $this->getValue('email');
       
        $dias = implode(",", [$this->getValue("dom"), $this->getValue("seg"), $this->getValue("ter"), $this->getValue("qua"), $this->getValue("qui"), $this->getValue("sex"), $this->getValue("sab")]);

        $usuario = new usuario;
        $usuario->id           = intval($this->getValue('cd'));
        $usuario->nome         = $this->getValue('nome');
        $usuario->cpf_cnpj     = $cpf_cnpj;
        $usuario->senha        = $this->getValue('senha');
        $usuario->email        = $email;
        $usuario->telefone     = $this->getValue('telefone');
        $usuario->tipo_usuario = 2;
        $usuario->id_empresa   = $this->getValue("id_empresa");
        $usuario->ativo        = 1;
        
        $funcionario                       = new funcionarioModel;
        $funcionario->id                   = intval($this->getValue("id_funcionario"));
        $funcionario->hora_ini             = $this->getValue('hora_ini');
        $funcionario->hora_fim             = $this->getValue('hora_fim');
        $funcionario->hora_almoco_ini      = $this->getValue('hora_almoco_ini');
        $funcionario->hora_almoco_fim      = $this->getValue('hora_almoco_fim');
        $funcionario->dias                 = $dias;
        $funcionario->espacamento_agenda   = $this->getValue('espacamento_agenda')?:30;
        $funcionario->nome                 = $this->getValue('nome');
        $funcionario->cpf_cnpj             = $cpf_cnpj;
        $funcionario->email                = $email;
        $funcionario->telefone             = $this->getValue('telefone');

        connection::beginTransaction();

        try {
            if ($usuario->set()) 
            {
                $funcionario->id_usuario = $usuario->id;
                if ($funcionario->set())
                {
                    if ($id_grupo_funcionario){
                        $grupoFuncionario = new funcionarioGrupoFuncionario;
                        $grupoFuncionario->id_grupo = $id_grupo_funcionario;
                        $grupoFuncionario->id_funcionario = $funcionario->id;
                        $grupoFuncionario->set();
                    }
                    if ($id_agenda){
                        $agendaFuncionario = new agendaFuncionario;
                        $agendaFuncionario->id_agenda = $id_agenda;
                        $agendaFuncionario->id_funcionario = $funcionario->id;
                        $agendaFuncionario->set();
                    }

                    mensagem::setSucesso("Funcionario salvo com sucesso");
                    connection::commit();

                    $this->manutencao([$funcionario->id],$funcionario,$usuario);
                    return;
                }
            }
        } catch (\Exception $e) {
            logger::error($e->getMessage());
            mensagem::setSucesso(false);
            connection::rollback();
            $this->manutencao([$funcionario->id],$funcionario,$usuario);
            return;
        }

        mensagem::setSucesso(false);
        connection::rollback();
        $this->manutencao([$funcionario->id],$funcionario,$usuario);
    }

    public function massActionAgenda(array $parameters = [])
    {
        try {

            connection::beginTransaction();

            $ids = $this->getValue("massaction")??[];
            $id_agenda = $this->getValue("agenda");

            $mensagem = "Funcionario vinculados com sucesso: ";
            $mensagem_erro = "Funcionario não vinculados: ";

            if ($ids && $id_agenda) {
                foreach ($ids as $id_funcionario){
                    $agendaFuncionario = new agendaFuncionario;
                    $agendaFuncionario->id_agenda = $id_agenda;
                    $agendaFuncionario->id_funcionario = $id_funcionario;
                    if ($agendaFuncionario->set())
                        $mensagem .= $id_funcionario . " <br> ";
                    else
                        $mensagem_erro .= $id_funcionario . " <br> ";
                }

                if ($mensagem != "Funcionario vinculados com sucesso: ")
                    mensagem::setSucesso($mensagem);
                if ($mensagem_erro != "Funcionario não vinculados: ")
                    mensagem::setErro($mensagem_erro);

                connection::commit();
            } else {
                mensagem::setErro("Não foi informado a agenda");
            }
        } catch (\Exception $e) {
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente");
            connection::rollback();
        }

        $this->index();
    }

    public function massActionGrupoFuncionario(array $parameters = [])
    {
        try {

            connection::beginTransaction();

            $ids = $this->getValue("massaction")??[];
            $id_grupo_funcionario = $this->getValue("grupo_funcionario");

            $mensagem = "Funcionario vinculados com sucesso: ";
            $mensagem_erro = "Funcionario não vinculados: ";

            if ($ids && $id_grupo_funcionario) {
                foreach ($ids as $id_funcionario){
                    $agendaFuncionario = new funcionarioGrupoFuncionario;
                    $agendaFuncionario->id_funcionario = $id_funcionario;
                    $agendaFuncionario->id_grupo_funcionario = $id_grupo_funcionario;
                    if ($agendaFuncionario->set())
                        $mensagem .= $id_funcionario . " <br> ";
                    else
                        $mensagem_erro .= $id_funcionario . " <br> ";
                }

                if ($mensagem != "Funcionario vinculados com sucesso: ")
                    mensagem::setSucesso($mensagem);
                if ($mensagem_erro != "Funcionario não vinculados: ")
                    mensagem::setErro($mensagem_erro);

                connection::commit();
            } else {
                mensagem::setErro("Não foi informado a agenda");
            }
        } catch (\Exception $e) {
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente");
            connection::rollback();
        }

        $this->index();
    }
}
