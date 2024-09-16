<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;

final class agendaUsuario extends model {
    public const table = agenda::table."_usuario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de vinculo entre agendamentos e usuarios"))
                ->addColumn((new column("id_agenda","INT"))->isPrimary()->isForeingKey(agenda::table())->setComment("ID agenda"))
                ->addColumn((new column("id_usuario","INT"))->isPrimary()->isForeingKey(usuario::table())->setComment("ID Usuario"));
    }

    public function set():bool
    {
        $result = $this->addFilter($this::table.".id_usuario","=",$this->id_usuario)
                    ->addFilter($this::table.".id_agenda","=",$this->id_agenda)
                    ->selectAll();

        if (!$result){
            return $this->storeMutiPrimary();
        }

        return true;
    }

    public function removeByAgenda():bool
    {
        return $this->addFilter(agendaUsuario::table.".id_agenda","=",$this->id_agenda)->deleteByFilter();  
    }

    public function removeByUsuario():bool
    {
        return $this->addFilter(agendaFuncionario::table."id_funcionario","=",$this->id_usuario)
                    ->deleteByFilter();
    }

    public function remove():bool
    {
        return $this->addFilter(agendaFuncionario::table."id_agenda","=",$this->id_agenda)
                    ->addFilter(agendaFuncionario::table."id_usuario","=",$this->id_usuario)
                    ->deleteByFilter();
    }

}