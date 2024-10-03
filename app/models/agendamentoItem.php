<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

final class agendamentoItem extends model {
    public const table = "agendamento_item";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de itens agendamentos"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID do item"))
                ->addColumn((new column("id_agendamento","INT"))->isNotNull()->isForeingKey(agendamento::table())->setComment("ID agendamento"))
                ->addColumn((new column("id_servico","INT"))->isNotNull()->isForeingKey(servico::table())->setComment("ID serviço"))
                ->addColumn((new column("qtd_item","INT"))->isNotNull()->setComment("QTD de serviços"))
                ->addColumn((new column("tempo_item","TIME"))->isNotNull()->setComment("Tempo total do serviço"))
                ->addColumn((new column("total_item","DECIMAL","10,2"))->isNotNull()->setComment("Valor do serviço"));
    }

    public  function getItens(int $id_agendamento):array
    {
        $result = $this->addJoin(servico::table."",servico::table.".id",agendamento::table."_item.id_servico")
                    ->addFilter("id_agendamento","=",$id_agendamento)
                    ->selectAll();
        
        return $result;
    }

    public function getItemByServico(int $id_agendamento,int $id_servico):object|null
    {
        $result = $this->addJoin(servico::table."",servico::table.".id",agendamento::table."_item.id_servico")
                    ->addFilter("id_agendamento","=",$id_agendamento)
                    ->addFilter("id_servico","=",$id_servico)
                    ->addLimit(1)
                    ->selectColumns(agendamento::table."_item.id","id_agendamento","id_servico","qtd_item","tempo_item","total_item","nome","valor","tempo","id_empresa");

        if ($result)
            return $result[0];
        
        return null;
    }

    public function set():agendamentoItem|null
    {
        $mensagens = [];
        
        $servico = (new servico)->get($this->id_servico);

        if(!$servico->id){
            mensagem::setErro("Serviço não existe");
            return false;
        }

        if(!($this->qtd_item || $this->qtd_item <= 0)){
            $mensagens[] = "Quantidade invalida";
        }

        if(!($this->total_item = ($servico->valor * $this->qtd_item))){
            $mensagens[] = "Total do item do agendamento invalido";
        }

        if(!($this->tempo_item = functions::multiplicarTempo($servico->tempo,$this->qtd_item))){
            $mensagens[] = "Tempo do item do agendamento invalido";
        }

        if(!($this->id_agendamento) || !(new agendamento)->get($this->id_agendamento)->id){
            $mensagens[] = agendamento::table." não existe";
        }

        if(($this->id) && !self::get($this->id)->id){
            $mensagens[] = "Item não existe";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Item salvo com sucesso");
            return $this;
        }
        else {
            return null;
        }
    }

    public function removeByIdAgendamento(){
        return $this->addFilter("id_agendamento","=",$this->id_agendamento)->deleteByFilter();
    }

}