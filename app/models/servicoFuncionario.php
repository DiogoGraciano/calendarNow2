<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

final class servicoFuncionario extends model {
    public const table = "servico_funcionario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de relacionamento entre serviços e funcionários"))
                ->addColumn((new column("id_funcionario","INT"))->isPrimary()->isNotNull()->setComment("ID do funcionário")->isForeingKey(funcionario::table()))
                ->addColumn((new column("id_servico","INT"))->isPrimary()->isNotNull()->setComment("ID do serviço")->isForeingKey(servico::table()));
    }

    public function set():servicoFuncionario
    {
        $result = $this->addFilter("id_servico","=",$this->id_servico)
                    ->addFilter("id_funcionario","=",$this->id_funcionario)
                    ->selectAll();

        if (!$result){
            if ($this->storeMutiPrimary()){
                return $this;
            }
            return false;
        }

        return $this;
    }

    public function removeByServico():bool
    {
        return $this->addFilter(self::table.".id_servico","=",$this->id_servico)->deleteByFilter();  
    }

    public function removeByFuncionario():bool
    {
        return $this->addFilter(self::table.".id_funcionario","=",$this->id_funcionario)
                    ->deleteByFilter();
    }

    public function getByFuncionario($id_servico){
        return $this->addJoin(funcionario::table,funcionario::table.".id",self::table.".id_funcionario")
                    ->addFilter("id_servico","=",$id_servico)
                    ->selectColumns(funcionario::table.".id","nome");
    }

    public function remove():bool
    {
        return $this->addFilter(self::table.".id_servico","=",$this->id_servico)
                    ->addFilter(self::table.".id_funcionario","=",$this->id_funcionario)
                    ->deleteByFilter();
    }
}