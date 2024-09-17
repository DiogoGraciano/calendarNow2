<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\db\transactionManeger;
use app\helpers\functions;
use app\helpers\logger;
use app\helpers\mensagem;

final class agenda extends model {

    public const table = "agenda";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de agendas"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID agenda"))
                ->addColumn((new column("id_empresa","INT"))->isNotNull()->isForeingKey(empresa::table())->setComment("ID da tabela empresa"))
                ->addColumn((new column("nome","VARCHAR",250))->isNotNull()->setComment("Nome da agenda"))
                ->addColumn((new column("codigo","VARCHAR",7))->isNotNull()->setComment("Codigo da agenda"));
    }

    public function getByEmpresa(int $id_empresa,?string $nome = null,?string $codigo = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        $this->addFilter("id_empresa","=",$id_empresa);

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($codigo){
            $this->addFilter("codigo","LIKE","%".$codigo."%");
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

        return $this->selectColumns("id",agenda::table.".nome",agenda::table.".codigo");
    }

    public function getByCodigo(string $codigo = ""):array
    {
        return $this->addFilter(agenda::table.".codigo","=",$codigo)->selectColumns("id",agenda::table.".nome",agenda::table.".codigo");
    }

    public function getByUsuario(int $id_usuario):array
    {
        return $this->addJoin(agendaUsuario::table,agendaUsuario::table.".id_agenda",$this::table.".id")
                    ->addJoin(empresa::table,$this::table.".id_empresa",empresa::table.".id")
                    ->addFilter(agendaUsuario::table.".id_usuario","=",$id_usuario)
                    ->selectColumns(agenda::table.".id",agenda::table.".nome",empresa::table.".nome as emp_nome");
    }

    public function set():self
    {
        $mensagens = [];

        if($this->id && !(new self)->get($this->id)->id)
            $mensagens[] = agenda::table." não encontrada";
        
        if(!((new empresa)->get($this->id_empresa)->id))
            $mensagens[] = "Empresa não encontrada"; 

        if(!($this->nome = htmlspecialchars(trim($this->nome))))
            $mensagens[] = "Nome é obrigatorio";

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->codigo)
            $this->codigo = htmlspecialchars($this->codigo);
        else 
            $this->codigo = functions::genereteCode(7);

        if ($this->store()){
            mensagem::setSucesso(agenda::table." salvo com sucesso");
            return $this->id;
        }
        
        return False;
    }

    public function getByFuncionario(int $id_funcionario):array
    {
        return $this->addJoin(agendaFuncionario::table,"id","id_agenda")
                    ->addFilter("id_funcionario","=",$this->id_funcionario)
                    ->selectColumns(agenda::table.".id",agenda::table.".nome");
    }

    public function remove():bool
    {
        try {

            transactionManeger::init();
            transactionManeger::beginTransaction();

            $agendaUsuario = (new agendaUsuario);
            $agendaUsuario->id_agenda = $this->id;
            $agendaUsuario->removeByAgenda();

            $agendaFuncionario = (new agendaFuncionario);
            $agendaFuncionario->id_agenda = $this->id;
            $agendaFuncionario->removeByAgenda($this->id);

            if($this->delete($this->id)){
                mensagem::setSucesso(agenda::table." deletada com sucesso");
                transactionManeger::commit();
                return true;
            }

            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;

        }catch (\exception $e){
            logger::error(agenda::table."Model->remove(): ".$e->getMessage()." ".$e->getTraceAsString());
            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }
    }
}