<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\mensagem;

final class grupoServico extends model {
    public const table = "grupo_servico";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de grupos de serviços"))
                ->addColumn((new column("id","INT"))->isPrimary()->isNotNull()->setComment("ID do grupo de serviços"))
                ->addColumn((new column("id_empresa","INT"))->isForeingKey(empresa::table())->isNotNull()->setComment("ID da empresa"))
                ->addColumn((new column("nome", "VARCHAR", 250))->isNotNull()->setComment("Nome do grupo de serviços"));
    }

    public function getByFilter(int $id_empresa,string $nome = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array{

        $this->addFilter("id_empresa", "=", $id_empresa);

        if($nome){
            $this->addFilter("nome", "like", "%".$nome."%");
        }

        if($limit && $offset){
            self::setLastCount($this);
            $this->addLimit($limit);
            $this->addOffset($offset);
        }
        elseif($limit){
            self::setLastCount($this);
            $this->addLimit($limit);
        }
        
        if($asArray){
            $this->asArray();
        }

        $values = $this->selectColumns("id","nome");

        return $values;
    }

    public function getVinculos(int $id_servico):array
    {
        return $this->addJoin(servicoGrupoServico::table,"id","id_grupo_servico")
                    ->addFilter("id_servico","=",$id_servico)
                    ->selectAll();
    }

    public function set():bool{

        $mensagens = [];

        if($this->id&& !self::get($this->id)->id){
            $mensagens[] = "Grupo de Funcionarios não encontrada";
        }
        
        if(!(new empresa)->get($this->id_empresa)->id){
            $mensagens[] = "Empresa não encontrada";
        }

        if(!$this->nome = htmlspecialchars((trim($this->nome)))){
            $mensagens[] = "Nome invalido";
        }

        if ($this->store()){
            mensagem::setSucesso("Grupo de funcionarios salvo com sucesso");
            return true;
        }
        
        mensagem::setErro("Erro ao salvar grupo de funcionarios");
        return false;
    }
}