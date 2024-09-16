<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;

final class funcionarioGrupoFuncionario extends model {
    public const table = "funcionario_grupo_funcionario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de relacionamento entre funcionarios e grupos de funcionarios"))
                ->addColumn((new column("id_funcionario","INT"))->isNotNull()->setComment("ID do funcionario")->isForeingKey(funcionario::table()))
                ->addColumn((new column("id_grupo_funcionario","INT"))->isNotNull()->setComment("ID do grupo de funcionarios")->isForeingKey(grupoFuncionario::table()));
    }
    

    public function removeByFuncionario():bool
    {
        return $this->addFilter(self::table.".id_funcionario","=",$this->id_funcionario)->deleteByFilter();
    }

    public function set():funcionarioGrupoFuncionario
    {
        $result = $this->addFilter("id_grupo_funcionario","=",$this->id_grupo_funcionario)
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
}