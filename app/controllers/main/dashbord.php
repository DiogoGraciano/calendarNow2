<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\helpers\functions;
use app\models\agendamento;
use app\models\agendamentoItem;
use app\models\login;
use app\view\layout\dashbord as LayoutDashbord;
use app\view\layout\grafico;

class dashbord extends controller
{
    public function index(){

        $agendamentos = [];
        $agendamento = new agendamento();

        $dt_ini = (new \DateTimeImmutable("now"))->format("Y-m")."-01";
        $dt_fim = (new \DateTimeImmutable("last day of this month"))->format("Y-m-d");

        $user = (new login)->getLogged();

        $agendamentos = $agendamento->getByfilter($user->id_empresa,dt_ini:$dt_ini,dt_fim:$dt_fim);

        $servicos = [];
        $valor = 0;
        foreach ($agendamentos as $age){
            $itens = (new agendamentoItem)->countItens($age->id);
            foreach ($itens as $item){
                if(isset($servicos[$item["nome"]]))
                    $servicos[$item["nome"]] += $item["qtd"];
                else
                    $servicos[$item["nome"]] = $item["qtd"];
                $valor += $item["valor"];
            }
        };

        $nome = "";
        $qtd = 0;
        foreach ($servicos as $key => $servico){
            if($servico > $qtd){
                $nome = $key;
                $qtd = $servico;
            }
        }

        $agendamentos = $agendamento->getQtdValorByDia($user->id_empresa,$dt_ini,$dt_fim);

        $Lastday = intval((new \DateTimeImmutable($dt_fim))->format("d"));

        $arrayQtd = [];
        $arrayValor = [];
        $arrayDias = [];
        for ($i=1; $i <= $Lastday; $i++) { 
            $arrayDias[] = $i;
            if(isset($agendamentos[$i - 1])){
                $arrayValor[] = $agendamentos[$i - 1]["total_por_dia"];
                $arrayQtd[] = $agendamentos[$i - 1]["qtd"];
            }
            else{
                $arrayValor[] = 0;
                $arrayQtd[] = 0;
            }
            
        }

        $dashbord = new LayoutDashbord;
        $dashbord->addCard("Total de Agendamentos desse Mês",count($agendamentos), "Dados do dia ".functions::dateBr($dt_ini)." até ".functions::dateBr($dt_fim),"fas fa-calendar")
                 ->addCard("Servico mais utilizado desse Mês",$nome." ({$qtd})", "Dados do dia ".functions::dateBr($dt_ini)." até ".functions::dateBr($dt_fim),"fa-solid fa-bell-concierge")
                 ->addCard("Faturamento Total desse Mês",functions::formatCurrency($valor), "Dados do dia ".functions::dateBr($dt_ini)." até ".functions::dateBr($dt_fim),"fa-solid fa-money-bill-wave")
                 ->addGrafico(new grafico($arrayDias,$arrayQtd,"agendamentos","bar",label:"Agendamentos Diário"),"Agendamentos Diários")
                 ->addGrafico(new grafico($arrayDias,$arrayValor,"faturamento","bar",label:"Faturamento Diário"),"Faturamento Diário")
                 ->addGrafico(new grafico(array_keys($servicos),array_values($servicos),"servicos","pie"),"Serviços");
        

        $dashbord->show();
    }
}
