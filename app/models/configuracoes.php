<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\mensagem;

final class configuracoes extends model {
    public const table = "configuracoes";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de selfurações"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID self"))
                ->addColumn((new column("id_empresa","INT"))->isNotNull()->isForeingKey(empresa::table(),"id")->setComment("ID da tabela empresa"))
                ->addColumn((new column("identificador","VARCHAR",30))->isNotNull()->isUnique()->setComment("Identificador da selfuração"))
                ->addColumn((new column("valor","BLOB"))->isNotNull()->setComment("selfuração"));
    }

    public function getByEmpresa(int $id_empresa):array
    {
        return $this->addFilter(self::table.".id_empresa", "=", $id_empresa)->selectAll();
    }

    public function getConfiguracao(string $identificador, int $id_empresa):bool|string|int|float
    {
        $self = $this->addFilter(self::table.".identificador", "=", $identificador)
                      ->addFilter(self::table.".id_empresa", "=", $id_empresa)
                      ->addLimit(1)->selectColumns("valor");

        if($self)
            return $self[0]->valor;
         
        return false;
    }

    public function getConfiguracaoStore(string $identificador, int $id_empresa):self
    {
        $self = $this->addFilter(self::table.".identificador", "=", $identificador)
                      ->addFilter(self::table.".id_empresa", "=", $id_empresa)
                      ->addLimit(1)->selectAll();

        if($self)
            return $self[0];
         
        return new self;
    }

    public function set():bool|int
    {
        $mensagens = [];

        if(!($this->id = $this->getConfiguracaoStore($this->identificador,$this->id_empresa)->id)){

            if(!(new empresa)->get($this->id_empresa)->id)
                $mensagens[] = "Empresa não existe";

            if(!($this->identificador = htmlspecialchars($this->identificador)))
                $mensagens[] = "Identificador é obrigatorio";
        }

        if(!($this->valor = htmlspecialchars($this->valor)))
            $mensagens[] = "Valor é obrigatorio";
        
        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->store()){
            mensagem::setSucesso("Configuração salvo com sucesso");
            return $this->id;
        }
        
        return False;
    }
}