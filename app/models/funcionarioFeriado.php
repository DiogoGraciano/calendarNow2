<?php

namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\column;
use diogodg\neoorm\migrations\table;
use app\helpers\functions;

class funcionarioFeriado extends model
{
    public const table = "funcionario_feriado";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de usuários bloqueados"))
                ->addColumn((new column("id_funcionario", "INT"))->isPrimary()->isForeingKey(funcionario::table())->isNotNull()->setComment("ID do funcionario"))
                ->addColumn((new column("id_feriado", "INT"))->isPrimary()->isForeingKey(feriado::table())->isNotNull()->setComment("ID da feriado"));
    }

    public function set():bool
    {
        $result = $this->addFilter("id_feriado","=",$this->id_feriado)
                    ->addFilter("id_funcionario","=",$this->id_funcionario)
                    ->selectAll();

        if ($result){
            return true;
        }

        if(!($this->id_funcionario = (new funcionario)->get($this->id_funcionario)->id)){
            $mensagens[] = "Usuario não existe";
        }
        if(!($this->id_feriado = (new feriado)->get($this->id_feriado)->id)){
            $mensagens[] = "Agenda não existe";
        }

        if($this->storeMutiPrimary()){
            return true;
        }

        return false;
    }

    public function getFeriadoByFuncionario(int|null $id_funcionario = null,?string $dt_ini = null,?string $dt_fim = null){
        if(!$id_funcionario){
            return [];
        }

        if($dt_ini)
            $this->addFilter("dt_ini",">=",functions::dateTimeBd($dt_ini));
        if($dt_fim)
            $this->addFilter("dt_fim","<=",functions::dateTimeBd($dt_fim));

        return $this->addJoin(feriado::table,feriado::table.".id","id_feriado")
                    ->addFilter("id_funcionario","=",$id_funcionario)
                    ->selectColumns(feriado::table.".id",feriado::table.".nome","dt_ini","dt_fim","repetir");
    }

    public function getFuncionarioByFeriado(int|null $id_feriado = null):array
    {
        if(!$id_feriado){
            return [];
        }

        return $this->addJoin(funcionario::table,funcionario::table.".id","id_funcionario")
                    ->addFilter("id_feriado","=",$id_feriado)
                    ->selectColumns(funcionario::table.".id",funcionario::table.".nome");
    }

    public function remove():bool
    {
        return $this->addFilter("id_feriado","=",$this->id_feriado)
                    ->addFilter("id_funcionario","=",$this->id_funcionario)
                    ->deleteByFilter();
    }
}
