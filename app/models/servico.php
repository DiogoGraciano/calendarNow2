<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\db\transactionManeger;
use app\helpers\functions;
use app\helpers\mensagem;

final class servico extends model {
    public const table = "servico";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de serviços"))
                ->addColumn((new column("id","INT"))->isPrimary()->isNotNull()->setComment("ID do serviço"))
                ->addColumn((new column("nome", "VARCHAR", 250))->isNotNull()->setComment("Nome do serviço"))
                ->addColumn((new column("valor", "DECIMAL", "14,2"))->isNotNull()->setComment("Valor do serviço"))
                ->addColumn((new column("tempo", "TIME"))->isNotNull()->setComment("Tempo do serviço"))
                ->addColumn((new column("id_empresa","INT"))->isNotNull()->setComment("ID da empresa"));
    }

   
    public function getByfilter(int $id_empresa,string $nome = null,int $id_funcionario = null,int $id_grupo_servico = null,?int $limit = null,?int $offset = null):array
    {
        $this->addFilter(servico::table.".id_empresa","=",$id_empresa);

        if($nome){
            $this->addFilter(servico::table.".nome","like","%".$nome."%");
        }

        if($id_funcionario){
            $this->addJoin(servicoFuncionario::table,servicoFuncionario::table.".id_servico",servico::table.".id");
            $this->addFilter(servicoFuncionario::table.".id_funcionario","=",$id_funcionario);
        }

        if($id_grupo_servico){
            $this->addJoin(servicoGrupoServico::table,servicoGrupoServico::table.".id_servico",servico::table.".id");
            $this->addFilter(servicoGrupoServico::table.".id_grupo_servico","=",$id_grupo_servico);
        }

        $this->addGroup(servico::table.".id");

        if($limit && $offset){
            self::setLastCount($this);
            $this->addLimit($limit);
            $this->addOffset($offset);
        }
        elseif($limit){
            self::setLastCount($this);
            $this->addLimit($limit);
        }
        
        return $this->selectColumns(servico::table.".id",servico::table.".nome",servico::table.".tempo",servico::table.".valor");
    }

    public function prepareData(array $values):array
    {
        $valuesFinal = [];

        if ($values){
            foreach ($values as $value){
                if(is_subclass_of($value,"app\db\db")){
                    $value = $value->getArrayData();
                }

                if ($value["valor"]){
                    $value["valor"] = functions::formatCurrency($value["valor"]);
                }

                $valuesFinal[] = $value;
            }

            return $valuesFinal;
        }

        return [];
    }

    public function getByFuncionario(int $id_funcionario):array
    {
        $this->addJoin(servico::table."_funcionario",servicoFuncionario::table.".id_servico",servico::table.".id");
        $this->addFilter(servicoFuncionario::table.".id_funcionario","=",$id_funcionario);
        $this->addGroup(servico::table.".id");
        
        return $this->selectColumns(servico::table.".id",servico::table.".nome",servico::table.".tempo",servico::table.".valor");
    }

    public function set():servico|null
    {
        $mensagens = [];

        if(!$this->nome = htmlspecialchars((trim($this->nome)))){
            $mensagens[] = "Nome é invalido";
        }

        if(($this->valor) <= 0){
            $mensagens[] = "Valor do serviço invalido";
        }

        if(!functions::validaHorario($this->tempo = functions::formatTime($this->tempo))){
            $mensagens[] = "Tempo do serviço invalido";
        }

        if(($this->id_empresa) && !(new empresa)->get($this->id_empresa)){
            $mensagens[] = "Empresa não existe";
        }

        if($this->id && !self::get($this->id)){
            $mensagens[] = "Serviço não existe";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->store()){
            mensagem::setSucesso("Serviço salvo com sucesso");
            return $this;
        }

        return False;
    }

    public function remove():bool{
        try {
            transactionManeger::init();
            transactionManeger::beginTransaction();

            $servicoFuncionario = new servicoFuncionario;
            $servicoFuncionario->id_servico = $this->id;
            $servicoFuncionario->removeByServico();

            $servicoGrupoServico = new servicoGrupoServico;
            $servicoGrupoServico->id_servico = $this->id;
            $servicoFuncionario->removeByServico();

            if($this->delete($this->id)){
                mensagem::setSucesso("Servico deletado com sucesso");
                transactionManeger::commit();
                return true;
            }

            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }catch (\exception $e){
            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }
    }
}