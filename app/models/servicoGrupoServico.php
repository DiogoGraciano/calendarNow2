<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;

final class servicoGrupoServico extends model {
    public const table = servico::table."_grupo_servico";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de relacionamento entre grupos de serviços e serviços"))
                ->addColumn((new column("id_grupo_servico","INT"))->isPrimary()->isNotNull()->setComment("ID do grupo de serviço")->isForeingKey(grupoServico::table()))
                ->addColumn((new column("id_servico","INT"))->isPrimary()->isNotNull()->setComment("ID do serviço")->isForeingKey(servico::table()));
    }

    public function set():servicoGrupoServico
    {
        $result = $this->addFilter("id_servico","=",$this->id_servico)
                    ->addFilter("id_grupo_servico","=",$this->id_grupo_servico)
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

    public function removeByGrupo():bool
    {
        return $this->addFilter(self::table."id_grupo_servico","=",$this->id_grupo_servico)
                    ->deleteByFilter();
    }

    public function remove():bool
    {
        return $this->addFilter(self::table."id_servico","=",$this->id_servico)
                    ->addFilter(self::table."id_grupo_servico","=",$this->id_grupo_servico)
                    ->deleteByFilter();
    }
}