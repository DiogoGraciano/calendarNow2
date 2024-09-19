<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;

final class agendaFuncionario extends model {
    public const table = "agenda_funcionario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de vinculo entre agendamentos e funcionarios"))
                ->addColumn((new column("id_agenda","INT"))->isPrimary()->isForeingKey(agenda::table())->setComment("ID agenda"))
                ->addColumn((new column("id_funcionario","INT"))->isPrimary()->isForeingKey(funcionario::table())->setComment("ID Funcionario"));
    }

    public function getFuncionarioByAgenda(int|null $id_agenda = null):array
    {
        if(!$id_agenda){
            return [];
        }

        return $this->addJoin(funcionario::table,"id","id_funcionario")
                    ->addFilter("id_agenda","=",$id_agenda)
                    ->selectColumns(funcionario::table.".id",funcionario::table.".nome");
    }

    public function getAgendaByFuncionario(int|null $id_funcionario = null):array
    {
        if(!$id_funcionario){
            return [];
        }

        return $this->addJoin(agenda::table,"id","id_agenda")
                    ->addFilter("id_funcionario","=",$id_funcionario)
                    ->selectColumns(agenda::table.".id",agenda::table.".nome");
    }

    public function set():bool
    {
        $result = $this->addFilter($this::table.".id_funcionario","=",$this->id_funcionario)
                        ->addFilter($this::table.".id_agenda","=",$this->id_agenda)
                        ->selectAll();

        if (!$result){
            return $this->storeMutiPrimary();
        }

        return true;
    }

    public function removeByAgenda():bool
    {
        return $this->addFilter(agendaFuncionario::table.".id_agenda","=",$this->id_agenda)
                    ->deleteByFilter();
    }

    public function removeByFuncionario():bool
    {
        return $this->addFilter(agendaFuncionario::table.".id_funcionario","=",$this->id_funcionario)
                    ->deleteByFilter();
    }

    public function remove():bool
    {
        return $this->addFilter(agendaFuncionario::table.".id_agenda","=",$this->id_agenda)
                    ->addFilter(agendaFuncionario::table.".id_funcionario","=",$this->id_funcionario)
                    ->deleteByFilter();
    }
}