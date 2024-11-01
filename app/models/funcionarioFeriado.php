<?php

namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\column;
use diogodg\neoorm\migrations\table;

class funcionarioFeriado extends model
{
    public const table = "funcionario_feriado";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de usuários bloqueados"))
                ->addColumn((new column("id", "INT"))->isPrimary()->setComment("ID do bloqueio"))
                ->addColumn((new column("id_funcionario", "INT"))->isForeingKey(funcionario::table())->isNotNull()->setComment("ID do funcionario"))
                ->addColumn((new column("id_feriado", "INT"))->isForeingKey(feriado::table())->isNotNull()->setComment("ID da feriado"));
    }

    public function set():bool
    {
        if(!($this->id_funcionario = (new funcionario)->get($this->id_funcionario)->id)){
            $mensagens[] = "Usuario não existe";
        }
        if(!($this->id_feriado = (new feriado)->get($this->id_feriado)->id)){
            $mensagens[] = "Agenda não existe";
        }

        if($this->store()){
            return true;
        }

        return false;
    }

    public function remove():bool
    {
        return $this->addFilter("id_feriado","=",$this->id_feriado)
                    ->addFilter("id_funcionario","=",$this->id_funcionario)
                    ->deleteByFilter();
    }
}
