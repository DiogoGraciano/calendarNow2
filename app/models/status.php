<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\mensagem;

final class status extends model {
    public const table = "status";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de status"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID agenda"))
                ->addColumn((new column("nome","VARCHAR",250))->isNotNull()->setComment("Status do agendamento"));
    }


    public function getUsuarioStatus():array|object
    {
        return $this->addFilter("id","IN",[1,4])->selectAll();
    }

    public function set(string $nome,int|null $id = null):status|null
    {
        $this->nome = htmlspecialchars(trim($this->nome));
        
        if ($this->store()){
            mensagem::setSucesso("Status salvo com sucesso");
            return $this;
        }

        return null;
    }

    public static function seed(){
        $object = new self;
        if(!$object->addLimit(1)->selectColumns("id")){
            $object->nome = "Agendado";
            $object->store();
            $object = new self;
            $object->nome = "Finalizado";
            $object->store();
            $object = new self;
            $object->nome = "Não atendido";
            $object->store();
            $object = new self;
            $object->nome = "Cancelado";
            $object->store();
        }
    }
}