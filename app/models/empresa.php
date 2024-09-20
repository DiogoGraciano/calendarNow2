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

    public function getByFilter(?string $nome = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array
    {
        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
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

        return $this->selectAll();
    }

    public function prepareData(array $dados):array
    {
        $dadosFinal = [];
        if ($dados){
            foreach ($dados as $dado){

                if(is_subclass_of($dado,"app\db\db")){
                    $dado = $dado->getArrayData();
                }

                if ($dado["cpf_cnpj"]){
                    $dado["cpf_cnpj"] = functions::formatCnpjCpf($dado["cpf_cnpj"]);
                }
                if ($dado["telefone"]){
                    $dado["telefone"] = functions::formatPhone($dado["telefone"]);
                }

                $dadosFinal[] = $dado;
            }
        }
        
        return $dadosFinal;
    }

    public function getByAgenda($id_agenda):object|bool
    {
        $empresa = $this->addJoin(agenda::table,agenda::table.".id",$id_agenda)->addLimit(1)->selectColumns("empresa.id,empresa.nome,empresa.email,empresa.telefone,empresa.cnpj,empresa.razao,empresa.fantasia");

        if(isset($empresa[0]) && $empresa[0]){
            $empresa = $empresa[0];

            $configuracoes = (new configuracoes)->getByEmpresa($empresa->id);

            $empresa->configuracoes = new \stdClass;

            foreach ($configuracoes as $configuracao){
                $identificador = $configuracao->identificador;
                $empresa->configuracoes->$identificador = $configuracao->valor;
            }

            return $empresa;
        }
        
        return false;
    }

    public function set():empresa|null
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

        if((new self)->get($this->cnpj = functions::onlynumber($this->cpf_cnpj?:""),"cpf_cnpj")->id){
            $mensagens[] = "CPF/CNPJ já cadastrado";
        }
  
        if(!($this->email = htmlspecialchars(filter_var(trim($this->email), FILTER_VALIDATE_EMAIL)))){
            $mensagens[] = "E-mail Invalido";
        }

        if(!$this->id && (new self)->get($this->email,"email")->id){
            $mensagens[] = "Email já cadastrado";
        }

        if(!($this->telefone = functions::onlynumber($this->telefone?:"")) || !functions::validaTelefone($this->telefone)){
            $mensagens[] = "Telefone Invalido";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Empresa salva com sucesso");
            return $this;
        }

        mensagem::setErro("Erro ao cadastrar a empresa");
        return null;
    }
}