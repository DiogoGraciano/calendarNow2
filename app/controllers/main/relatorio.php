<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\helpers\functions;
use app\models\agenda;
use app\models\agendamento;
use app\models\empresa;
use app\models\funcionario;
use app\models\login;
use app\view\layout\consulta;
use app\view\layout\div;
use app\view\layout\elements;
use app\view\layout\filter;
use app\view\layout\footer;
use app\view\layout\header;
use app\view\layout\lista;
use app\view\layout\pagination;
use core\session;

final class relatorio extends controller{

    public const headTitle = "Relatorios";

    public const addHeader = false;

    public const addFooter = false;

    public function index(array $parameters = []){

        (new header())->show();

        $lista = new lista();

        $lista->addObjeto($this->url."relatorio/faturamentoAgendamentoList/","Faturamento por Agendamento");

        $lista->setLista("Relatorios");
        $lista->show();

        (new footer())->show();
    }

    public function faturamentoAgendamentoList(array $parameters = []){
        (new header())->show();

        $this->faturamentoAgendamento($parameters);

        (new footer())->show();
    }

    public function faturamentoAgendamento(array $parameters = [])
    {
        $relatorio = new div("relatorio");

        $consulta = new consulta(false,"Faturamento por Agendamento");

        $elements = new elements;

        $user = login::getLogged();

        $id_agenda = intval($this->getValue("agenda"));
        $id_funcionario = intval($this->getValue("funcionario"));
        $dt_ini = $this->getValue("dt_ini");
        $dt_fim = $this->getValue("dt_fim");

        if($id_agenda || $id_funcionario || $dt_ini || $dt_fim){
            session::set("filter_id_agenda",$id_agenda);
            session::set("filter_id_funcionario",$id_funcionario);
            session::set("filter_dt_ini",$dt_ini);
            session::set("filter_dt_fim",$dt_fim);
        }
        
        if(isset($parameters[0]) && $parameters[0] == "imprimir"){

            $id_agenda = session::get("filter_id_agenda");
            $id_funcionario = session::get("filter_id_funcionario");
            $dt_ini = session::get("filter_dt_ini");
            $dt_fim = session::get("filter_dt_fim");

            $consulta = new consulta(false,"");

            $agenda = (new agenda)->get($id_agenda);
            $funcionario = (new funcionario)->get($id_funcionario);
            $empresa = (new empresa)->get($user->id_empresa);
            $div = new div("empresa","col-md-12 p-2");
            $div->addContent($elements->titulo(1,"Faturamento por Agendamento","fw-normal text-title mb-2"));
            $div->addContent("<hr>");
            $div->addContent($elements->p("Empresa: ".$empresa->nome,"text-left mb-2"));
            $div->addContent($elements->p("Usuario: ".$user->nome,"text-left mb-2"));
            $div->addContent($elements->p("Data de Geração: ".functions::dateTimeBr("now"),"text-left mb-2"));
            $div->addContent("<hr>");
            $div->addContent($elements->titulo(4,"Filtros","fw-normal text-title mb-2"));
            $div->addContent("<hr>");
            $div->addContent($elements->p("Agenda: ".($agenda->nome?:"Todas"),"text-left mb-2"));
            $div->addContent($elements->p("Funcionario: ".($funcionario->nome?:"Todos"),"text-left mb-2"));
            $div->addContent($elements->p("Data Inicial: ".($dt_ini?functions::dateBr($dt_ini?:""):"Não Informado"),"text-left mb-2"));
            $div->addContent($elements->p("Data Final: ".($dt_fim?functions::dateBr($dt_fim?:""):"Não Informado"),"text-left mb-0"));
            $div->addContent("<script>window.print();</script>");
            $div->addContent("<hr>");
            $relatorio->addContent($div->parse());

            session::set("filter_id_agenda",0);
            session::set("filter_id_funcionario",0);
            session::set("filter_dt_ini",null);
            session::set("filter_dt_fim",null);
        }

        $consulta->addColumns("1","ID","id")
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

        $agendamento = new agendamento;
        $dados = $agendamento->prepareList($agendamento->getByFilter($user->id_empresa,$user->id != 3?null:$user->id,$dt_ini,$dt_fim,true,$id_agenda,$id_funcionario,$this->getLimit(),$this->getOffset()));

        if(!isset($parameters[0]) || $parameters[0] != "imprimir"){

            if ($user->tipo_usuario == 3){
                $funcionarios = (new funcionario)->getByUsuario($user->id);
                $agendas = (new agenda)->getByUsuario($user->id);
            }else{
                $funcionarios = (new funcionario)->getByEmpresa($user->id_empresa);
                $agendas = (new agenda)->getByFilter($user->id_empresa,asArray:false);
            }

            $filter = new filter($this->url."relatorio/faturamentoAgendamento","#relatorio");
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

            $consulta->addButtons($elements->button("Voltar","voltar","button","btn btn-primary","location.href='".$this->url."relatorio'")); 
            $consulta->addButtons($elements->link($this->url."relatorio/faturamentoAgendamento/imprimir","Imprimir","_blank","btn btn-primary")); 

            $consulta->addFilter($filter);

            $consulta->addPagination(new pagination(
                $agendamento::getLastCount("getByFilter"),
                "relatorio/faturamentoAgendamento",
                "#consulta-admin",
                limit:$this->getLimit()));
        }


        $consulta->setData($this->url."agendamento/manutencao",
                          $this->url."agendamento/action",
                          $dados,
                          "id");

        $relatorio->addContent($consulta->parse());

        $div = new div("totais","col-md-12 p-3");
        $div->addContent("<hr>");
        $div->addContent($elements->p("Numero de Agendamentos: ".$dados["total_agendamentos"]));
        if(isset($dados["ticket_medio"]))
            $div->addContent($elements->p("Ticket Medio: ".$dados["ticket_medio"]));
        $div->addContent($elements->titulo(4,"Total: ".$dados["total_geral"],"fw-normal text-title text-right"));
        $div->addContent("<hr>");

        $relatorio->addContent($div->parse());    
        $relatorio->show(); 
    }
}