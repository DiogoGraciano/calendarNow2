<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

final class cidade extends model {
    public const table = "cidade";

    public function __construct() {
        parent::__construct(self::table,$this::class);
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de cidades"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID da cidade"))
                ->addColumn((new column("nome","VARCHAR",120))->isNotNull()->setComment("Nome da cidade"))
                ->addColumn((new column("uf","INT"))->isNotNull()->isForeingKey(estado::table())->setComment("id da Uf da cidade"))
                ->addColumn((new column("ibge","INT"))->setComment("id do IBJE da cidade"));
    }

    public function getByNome(string $nome):array
    {
        return $this->addFilter("nome", "LIKE", "%" . $nome . "%")->addLimit(1)->selectAll();
    }

    public function getByNomeIdUf(string $nome,string $uf):array
    {
        return $this->addFilter("nome", "LIKE", "%" . $nome . "%")->addFilter("uf", "=", $uf)->addLimit(1)->selectAll();
    }

    public function getByIbge(string $ibge):array
    {
        return $this->addFilter("ibge", "=", $ibge)->selectAll();
    }

    public function getByEstado(string $uf):array {
        return $this->addFilter("uf", "=", $uf)->selectAll();
    }
}