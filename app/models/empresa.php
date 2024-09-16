<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

final class empresa extends model {
    public const table = "empresa";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de empresas"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID do cliente"))
                ->addColumn((new column("nome","VARCHAR",300))->isNotNull()->isUnique()->setComment("Nome da empresa"))
                ->addColumn((new column("email","VARCHAR",300))->isNotNull()->setComment("Email da empresa"))
                ->addColumn((new column("telefone","VARCHAR",13))->isNotNull()->setComment("Telefone da empresa"))
                ->addColumn((new column("cnpj","VARCHAR",14))->isNotNull()->setComment("CNPJ da empresa"))
                ->addColumn((new column("razao","VARCHAR",300))->isNotNull()->isUnique()->setComment("Razão social da empresa"))
                ->addColumn((new column("fantasia","VARCHAR",300))->isNotNull()->setComment("Nome fantasia da empresa"));
    }

    public function getByAgenda($id_agenda):object|bool
    {
        $empresa = $this->addJoin(agenda::table,self::table.".empresa",agenda::table.".id",$id_agenda)->addLimit(1)->selectColumns("empresa.id,empresa.nome,empresa.email,empresa.telefone,empresa.cnpj,empresa.razao,empresa.fantasia");

        if(isset($empresa[0]) && $empresa[0]){
            $empresa = $empresa[0];

            $configuracoes = (new configuracoes)->getByEmpresa($empresa->id);

            $empresa->configuracoes = new \stdClass;

            foreach ($configuracoes as $configuracao){
                $identificador = $configuracao->identificador;
                $empresa->configuracoes->$identificador = $configuracao->configuracao;
            }

            return $empresa;
        }
        
        return false;
    }

    public function set():int|bool
    {
        $mensagens = [];

        if($this->id && !(new self)->get($this->id)->id){
            $mensagens[] = "Empresa não existe";
        }

        if(!($this->nome = htmlspecialchars(trim($this->nome)))){
            $mensagens[] = "Nome da Empresa é obrigatorio";
        }

        if(!($this->razao = htmlspecialchars(trim($this->razao)))){
            $mensagens[] = "Razão Social é obrigatorio";
        }

        if(!($this->fantasia = htmlspecialchars(trim($this->fantasia)))){
            $mensagens[] = "Nome da Fantasia é obrigatorio";
        }

        if(!functions::validaCpfCnpj($this->cpf_cnpj)){
            $mensagens[] = "CPF/CNPJ invalido";
        }

        if((new self)->get($this->cnpj = functions::onlynumber($this->cpf_cnpj),"cpf_cnpj")->id){
            $mensagens[] = "CPF/CNPJ já cadastrado";
        }
  
        if(!($this->email = htmlspecialchars(filter_var(trim($this->email), FILTER_VALIDATE_EMAIL)))){
            $mensagens[] = "E-mail Invalido";
        }

        if(!$this->id && (new self)->get($this->email,"email")->id){
            $mensagens[] = "Email já cadastrado";
        }

        if(!($this->telefone = functions::onlynumber($this->telefone)) || !functions::validaTelefone($this->telefone)){
            $mensagens[] = "Telefone Invalido";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->store()){
            mensagem::setSucesso("Empresa salva com sucesso");
            return $this->id;
        }

        mensagem::setErro("Erro ao cadastrar a empresa");
        return false;
    }
}