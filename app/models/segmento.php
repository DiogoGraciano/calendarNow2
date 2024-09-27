<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\mensagem;

final class segmento extends model {
    public const table = "segmento";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de segmento"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID segmento"))
                ->addColumn((new column("nome","VARCHAR",250))->isNotNull()->setComment("Nome Segmento"));
    }


    public function set():status|null
    {
        $this->nome = htmlspecialchars(trim($this->nome));
        
        if ($this->store()){
            mensagem::setSucesso("Segmento salvo com sucesso");
            return $this;
        }

        return null;
    }

    public static function seed(){
        $object = new self;
        if(!$object->addLimit(1)->selectColumns("id")){
            $object->nome = "Beleza e Bem-estar";
            $object->store();
            $object = new self;
            $object->nome = "Educação e Treinamento";
            $object->store();
            $object = new self;
            $object->nome = "Saúde";
            $object->store();
            $object = new self;
            $object->nome = "Serviços Automotivos";
            $object->store();
            $object = new self;
            $object->nome = "Hospitalidade (Hoteis)";
            $object->store();
            $object = new self;
            $object->nome = "Restaurantes e Eventos";
            $object->store();
            $object = new self;
            $object->nome = "Serviços Domésticos";
            $object->store();
            $object = new self;
            $object->nome = "Fitness e Esportes";
            $object->store();
            $object = new self;
            $object->nome = "Tecnologia e Suporte Técnico";
            $object->store();
        }
    }
}