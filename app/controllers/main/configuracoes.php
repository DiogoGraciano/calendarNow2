<?php 
namespace app\controllers\main;
use app\view\layout\elements;
use app\helpers\mensagem;
use app\helpers\functions;
use app\controllers\abstract\controller;
use app\view\layout\form;
use diogodg\neoorm\connection;
use app\models\configuracoes as ModelsConfiguracoes;
use app\models\login;
use core\request;

final class configuracoes extends controller{

    public const headTitle = "Configurações";

    public function index(array $parameters = []):void
    {
        $elements = new elements;

        $user = login::getLogged();

        $configuracoes = new ModelsConfiguracoes;

        $form = new form($this->url."configuracoes/action");

        $form->setElement($elements->titulo(1,"Configurações da Empresa"))->
        setThreeElements($elements->input("max_agendamento_dia","Maximo de Agendamentos por Dia",
                        $configuracoes->getConfiguracao("max_agendamento_dia",$user->id_empresa)?:2,true,type:"number"),
                        $elements->input("max_agendamento_semana","Maximo de Agendamentos por Semana",
                        $configuracoes->getConfiguracao("max_agendamento_semana",$user->id_empresa)?:3,true,type:"number"),
                        $elements->input("max_agendamento_mes","Maximo de Agendamentos por Mês",
                        $configuracoes->getConfiguracao("max_agendamento_mes",$user->id_empresa)?:3,true,type:"number")
                    );

        $form->setTwoElements(
            $elements->input("hora_ini", "Hora Inicial de Abertura", functions::removeSecondsTime($configuracoes->getConfiguracao("hora_ini",$user->id_empresa)?:"08:00"), true, false,type:"time"),
            $elements->input("hora_fim", "Hora Final de Abertura", functions::removeSecondsTime($configuracoes->getConfiguracao("hora_fim",$user->id_empresa)?:"18:00"), true, false,type:"time"),
            ["hora_ini", "hora_fim"]
        );

        $form->setTwoElements(
            $elements->input("hora_almoco_ini", "Hora Inicial de Almoço", functions::removeSecondsTime($configuracoes->getConfiguracao("hora_almoco_ini",$user->id_empresa)?:"12:00"), true, false,type:"time"),
            $elements->input("hora_almoco_fim", "Hora Final de Almoço", functions::removeSecondsTime($configuracoes->getConfiguracao("hora_almoco_fim",$user->id_empresa)?:"14:00"), true, false,type:"time"),
            ["hora_almoco_ini", "hora_almoco_fim"]
        );

        $elements->addOption("N","Não");
        $elements->addOption("S","Sim");
        $form->setElement($elements->select("mostrar_endereco","Mostrar Endereço",$configuracoes->getConfiguracao("mostrar_endereco",$user->id_empresa)));

        $form->setButton($elements->button("Salvar","submit"));
        $form->show();
    }

    public function action(array $parameters = []):void
    {
        $user = login::getLogged();

        try {
            

            connection::beginTransaction();

            $request = new request;

            foreach ($request->post() as $key => $value){
                if($key == "submit"){
                    continue;
                }
                $configuracao = new ModelsConfiguracoes;
                $configuracao->id_empresa = $user->id_empresa;
                $configuracao->identificador = $key;
                $configuracao->valor = $value;
                $configuracao->set();
            }

            connection::commit();
            mensagem::setSucesso("Configuracões salvas com sucesso");
        } catch (\Exception $e) {
            connection::rollBack();
            mensagem::setSucesso(false);
            mensagem::setErro("Erro ao salvar configuracões");
        }

        $this->index();
    }
}