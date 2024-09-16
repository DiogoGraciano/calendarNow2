<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\mensagem;

final class grupoFuncionario extends model {
    public const table = "grupo_funcionario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de funcionarios"))
                ->addColumn((new column("id","INT"))->isPrimary()->isNotNull()->setComment("ID do funcionario"))
                ->addColumn((new column("id_empresa","INT"))->isNotNull()->isForeingKey(empresa::table())->setComment("ID da tabela empresa"))
                ->addColumn((new column("nome", "VARCHAR", 200))->isNotNull()->setComment("Nome do grupo de funcionarios"));
    }

    public function getByFilter(int $id_empresa,string $nome = null,?int $limit = null,?int $offset = null):array{

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

        $values = $this->selectColumns("id","nome");

        return $values;
    }

    public function getByFuncionario(int $id_funcionario):array
    {
        return $this->addJoin(funcionarioGrupoFuncionario::table,"id","id_grupo_funcionario")
                    ->addFilter("id_funcionario","=",$id_funcionario)
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