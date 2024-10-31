<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\helpers\functions;
use app\models\agendamento;
use app\models\agendamentoItem;
use app\view\layout\dashbord as LayoutDashbord;

class dashbord extends controller
{
    public function index(){

        $agendamentos = [];
        $agendamento = new agendamento();

        $dt_ini = (new \DateTimeImmutable("now"))->format("Y-m")."-01";
        $dt_fim = (new \DateTimeImmutable("last day of this month"))->format("Y-m-d");

        $agendamentos = $agendamento->getByfilter(dt_ini:$dt_ini,dt_fim:$dt_fim);

        $servicos = [];
        foreach ($agendamentos as $age){
            $itens = (new agendamentoItem)->countItens($age->id);
            foreach ($itens as $item){
                if(isset($servicos[$item["id_servico"]]))
                    $servicos[$item["id_servico"]] += $item["qtd"];
                else
                    $servicos[$item["id_servico"]] = $item["qtd"];
            }
        }

        $dashbord = new LayoutDashbord;
        $dashbord
        ->addCard("Total de Agendamentos desse Mês",count($agendamentos), "Dados do dia ".functions::dateBr($dt_ini)." até ".functions::dateBr($dt_fim),"fas fa-calendar")
        ->addCard("Servico mais utilizado desse Mês",count($agendamentos), "Dados do dia ".functions::dateBr($dt_ini)." até ".functions::dateBr($dt_fim),"fas fa-calendar");
        
        $dashbord->show();
    }
}
