<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\mensagem;

class cliente extends model {
    public const table = "cliente";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de clientes"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID do cliente"))
                ->addColumn((new column("nome","VARCHAR",300))->isNotNull()->setComment("Nome do cliente"))
                ->addColumn((new column("id_funcionario","INT"))->isForeingKey(funcionario::table())->setComment("id funcionario"));
    }

    /**
     * Obtém clientes pelo ID do funcionário associado.
     * 
     * @param int $id_funcionario O ID do funcionário associado aos clientes.
     * @return array Retorna um array de clientes ou um array vazio se não encontrado.
     */
    public function getByFuncionario(int $id_funcionario):array
    {
        return $this->addFilter("cliente.id_funcionario", "=", $id_funcionario)->selectAll();
    }

    public function getByEmpresa(int $id_empresa,?int $id_usuario,?int $limit = null,?int $offset = null):array
    {
        $this->addJoin("funcionario","funcionario.id","cliente.id_funcionario")->addJoin("usuario","usuario.id","funcionario.id_usuario")->addFilter("usuario.id_empresa", "=", $id_empresa);
        
        if($id_usuario)
        {
            $this->addFilter("funcionario.id_usuario", "=", $id_usuario);
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

        return $this->selectColumns("cliente.id","cliente.nome","cliente.id_funcionario");
    }

    public function set():int|bool
    {
        $mensagens = [];

        if($this->id && !($this->id = self::get($this->id)->id))
            $mensagens[] = "Cliente não encontrado";

        if(!($this->id_funcionario = (new funcionario)->get($this->id_funcionario)->id))
            $mensagens[] = "Funcionario informado não encontrado";

        if(!($this->nome = htmlspecialchars(trim($this->nome))))
            $mensagens[] = "Nome é obrigatorio";

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->store()){
            mensagem::setSucesso("Cliente salvo com sucesso");
            return $this;
        } 

        return false;
    }

    public function remove(int $id):bool
    {
        return $this->delete($id);
    }
}