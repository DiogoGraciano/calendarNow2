<?php

namespace app\models;

use app\helpers\functions;
use app\helpers\mensagem;
use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\column;
use diogodg\neoorm\migrations\table;

class feriado extends model
{
    public const table = "feriado";

    public function __construct()
    {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de feriados"))
                ->addColumn((new column("id", "INT"))->isPrimary()->setComment("ID do feriados"))
                ->addColumn((new column("nome", "VARCHAR", 250))->isNotNull()->setComment("Nome do Feriado"))
                ->addColumn((new column("dt_ini", "TIMESTAMP"))->isNotNull()->setComment("Data inicial do feriado"))
                ->addColumn((new column("dt_fim", "TIMESTAMP"))->isNotNull()->setComment("Data final do feriado"))
                ->addColumn((new column("repetir", "BOOLEAN"))->setDefaut(0)->isNotNull()->setComment("Repete"))
                ->addColumn((new column("id_empresa", "INT"))->isForeingKey(empresa::table())->isNotNull()->setComment("Id da Empresa"));
    }

    public function getByfilter(int $id_empresa,?string $nome = null,?string $dt_ini = null,?string $dt_fim = null,?int $limit = null,?int $offset = null):array
    {
        
        $this->addFilter(feriado::table.".id_empresa","=",$id_empresa);

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }
             
        if($dt_ini){
            $this->addFilter(agendamento::table.".dt_fim",">=",functions::dateTimeBd($dt_ini));
        }

        if($dt_fim){
            $this->addFilter(agendamento::table.".dt_fim","<=",functions::dateTimeBd($dt_fim));
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

        return $this->selectAll();
    }

    public function set():self|null
    {
        $mensagens = [];

        if($this->id && !(new self)->get($this->id)->id){
            $mensagens[] = "Feriado não encontrada";
        }

        if(!$this->nome = htmlspecialchars(ucwords(strtolower(trim($this->nome))))){
            $mensagens[] = "Nome deve ser informado";
        }

        if(!$this->dt_ini = functions::dateTimeBd($this->dt_ini)){
            $mensagens[] = "Data inicial invalida";
        }

        if(!$this->dt_fim = functions::dateTimeBd($this->dt_fim)){
            $mensagens[] = "Data final invalida";
        }

        if($this->repetir > 1 || $this->repetir < 0){
            $mensagens[] = "Valor de repetir invalido";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Feriado salvo com sucesso");
            return $this;
        }
            
        return null;
    }
}
