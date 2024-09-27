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
                ->addColumn((new column("id_segmento","INT"))->isForeingKey(segmento::table())->isNotNull()->setComment("ID do segmento"))
                ->addColumn((new column("nome","VARCHAR",300))->isNotNull()->isUnique()->setComment("Nome da empresa"))
                ->addColumn((new column("email","VARCHAR",300))->isNotNull()->setComment("Email da empresa"))
                ->addColumn((new column("telefone","VARCHAR",13))->isNotNull()->setComment("Telefone da empresa"))
                ->addColumn((new column("cnpj","VARCHAR",14))->isNotNull()->setComment("CNPJ da empresa"))
                ->addColumn((new column("razao","VARCHAR",300))->isNotNull()->isUnique()->setComment("Razão social da empresa"))
                ->addColumn((new column("fantasia","VARCHAR",300))->isNotNull()->setComment("Nome fantasia da empresa"));
    }

    public function get($value="",string $column="id",int $limit = 1):array|object{
        $retorno = false;

        if($limit){
            $this->addLimit($limit);
        }

        $this->addJoin(endereco::table,endereco::table.".id_empresa",self::table.".id");

        if ($value && in_array($column,$this->getColumns()))
            $retorno = $this->addFilter(self::table.".".$column,"=",$value)->selectColumns(self::table.".id","id_segmento","nome","email","telefone","cnpj","razao","fantasia","id_usuario","cep","id_cidade","id_estado","bairro","rua","numero","complemento","latitude","longitude");
        
        if (is_array($retorno) && count($retorno) == 1)
            return $retorno[0];

        return $retorno?:$this->setObjectNull();
    }
    
    public function getAll():array
    {
        $this->addJoin(endereco::table,endereco::table.".id_empresa",self::table.".id");

        return $this->selectColumns(self::table.".id","id_segmento","nome","email","telefone","cnpj","razao","fantasia","id_usuario","cep","id_cidade","id_estado","bairro","rua","numero", "complemento,latitude,longitude");
    }

    public function getByFilter(?string $nome = null,?int $id_segmento = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array
    {
        $this->addJoin(endereco::table,endereco::table.".id_empresa",self::table.".id");

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($id_segmento){
            $this->addFilter("id_segmento","=",$id_segmento);
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

        return $this->selectColumns(self::table.".id","id_segmento","nome","email","telefone","cnpj","razao","fantasia","id_usuario","id_empresa","cep","id_cidade","id_estado","bairro","rua","numero","complemento","latitude","longitude");
    }

    public function prepareData(array $dados):array
    {
        $dadosFinal = [];
        if ($dados){
            $segmentos = (new segmento)->getAll();
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
        $empresa = $this->addJoin(agenda::table,agenda::table.".id_empresa",self::table.".id")
                        ->addJoin(endereco::table,endereco::table.".id_empresa",self::table.".id")
                        ->addFilter(agenda::table.".id","=",$id_agenda)
                        ->selectColumns(self::table.".id","id_segmento",self::table.".nome","email","telefone","cnpj","razao","fantasia","id_usuario","cep","id_cidade","id_estado","bairro","rua","numero","complemento","latitude","longitude");

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

        if(!$this->id_segmento || !(new segmento)->get($this->id_segmento)->id){
            $mensagens[] = "Segmento informado invalido";
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