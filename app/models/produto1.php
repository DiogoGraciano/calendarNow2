<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;

class produto extends model {
    public const table = "produto";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de produtos"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID agenda"))
                ->addColumn((new column("id_marca","INT"))->isNotNull()->isForeingKey(marca::table())->setComment("ID marca"))
                ->addColumn((new column("nome","VARCHAR",250))->isNotNull()->setComment("Nome do Produto"))
                ->addColumn((new column("descricao","VARCHAR",1000))->isNotNull()->setComment("Descrição do Produto"))
                ->addColumn((new column("ordem","INT"))->isNotNull()->setComment("Ordem banner"))
                ->addColumn((new column("ativo","TINYINT"))->setDefaut(1)->setComment("Produto Ativo?"));
    }
}