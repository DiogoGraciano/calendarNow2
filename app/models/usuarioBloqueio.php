<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;

final class usuarioBloqueio extends model {
    public const table = "usuario_bloqueio";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de usuários bloqueados"))
                ->addColumn((new column("id", "INT"))->isPrimary()->setComment("ID do bloqueio"))
                ->addColumn((new column("id_usuario", "INT"))->isForeingKey(usuario::table())->isNotNull()->setComment("ID do usuário"))
                ->addColumn((new column("id_empresa", "INT"))->isForeingKey(empresa::table())->isNotNull()->setComment("ID da empresa"));
    }

    public function set():bool
    {
        if(!($this->id_usuario = (new usuario)->get($this->id_usuario )->id)){
            $mensagens[] = "Usuario não existe";
        }
        if(!($this->id_agenda = (new agenda)->get($this->id_agenda)->id)){
            $mensagens[] = "Agenda não existe";
        }

        if($this->store()){
            return true;
        }

        return false;
    }

   
    public function remove():bool
    {
        return $this->addFilter("id_usuario","=",$this->id_usuario)
                    ->addFilter("id_agenda","=",$this->id_agenda)
                    ->deleteByFilter();
    }
}