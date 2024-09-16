<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

final class endereco extends model {
    public const table = "endereco";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de endereços"))
            ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID do estado"))
            ->addColumn((new column("id_usuario","INT"))->isForeingKey(usuario::table())->setComment("ID da tabela usuario"))
            ->addColumn((new column("id_empresa","INT"))->isForeingKey(empresa::table())->setComment("ID da tabela empresa"))
            ->addColumn((new column("cep","VARCHAR",8))->isNotNull()->setComment("CEP"))
            ->addColumn((new column("id_cidade","INT"))->isForeingKey(cidade::table())->setComment("ID da tabela estado"))
            ->addColumn((new column("id_estado","INT"))->isForeingKey(estado::table())->setComment("ID da tabela cidade"))
            ->addColumn((new column("bairro","VARCHAR",300))->isNotNull()->setComment("Bairro"))
            ->addColumn((new column("rua","VARCHAR",300))->isNotNull()->setComment("Rua"))
            ->addColumn((new column("numero","INT"))->isNotNull()->setComment("Numero"))
            ->addColumn((new column("complemento","VARCHAR",300))->setComment("Complemento do endereço"));
    }

    public function getbyIdUsuario($id_usuario = ""):array
    {
        return $this->addFilter("id_usuario","=",$id_usuario)->selectAll();
    }

    public function set($valid_fk = true){

        $mensagens = [];

        if(!functions::validaCep(functions::onlynumber($this->cep))){
            $mensagens[] = "CEP é invalido";
        }

        if(!((new estado)->get($this->id_estado)->id)){
            $mensagens[] = "Estado é invalido";
        }

        if(!((new cidade)->get($this->id_cidade)->id)){
            $mensagens[] = "Cidade é invalida";
        }

        if(!($this->bairro = htmlspecialchars(trim($this->bairro)))){
            $mensagens[] = "Bairro é Invalido";
        }

        if(!($this->rua = htmlspecialchars(trim($this->rua)))){
            $mensagens[] = "Rua é Invalido";
        }

        if(!($this->numero = htmlspecialchars(trim($this->numero)))){
            $mensagens[] = "Numero é Invalido";
        }

        $this->complemento = htmlspecialchars(trim($this->complemento));
       
        if(!$this->id_usuario && !$this->id_empresa){
            $mensagens[] = usuario::table." ou Empresa precisa ser informado para cadastro";
        }

        if(($this->id_empresa) && $valid_fk && !(new empresa)->get($this->id_empresa)->id){
            $mensagens[] = "Empresa não existe";
        }

        if(($this->id_usuario) && $valid_fk && !(new usuario)->get($this->id_usuario)->id){
            $mensagens[] = usuario::table." não existe";
        }

        if(($this->id) && !(new self)->get($this->id)->id){
            $mensagens[] = "Endereço não existe";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->store()){
            mensagem::setSucesso("Endereço salva com sucesso");
            return $this;
        }

        mensagem::setErro("Erro ao cadastrar a endereço");
        return False;
    }
} 