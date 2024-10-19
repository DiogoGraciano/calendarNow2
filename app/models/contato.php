<?php
namespace app\models;

use app\helpers\functions;
use app\helpers\mensagem;
use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

class contato extends model {
    public const table = "contato";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de contatos via site"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID Contato"))
                ->addColumn((new column("nome","VARCHAR",250))->isNotNull()->setComment("Nome da Contato"))
                ->addColumn((new column("email","VARCHAR",150))->isNotNull()->setComment("Email do Contato"))
                ->addColumn((new column("telefone","VARCHAR",15))->setComment("Telefone do Contato"))
                ->addColumn((new column("assunto","VARCHAR",250))->setComment("Assunto Do Contato"))
                ->addColumn((new column("mensagem","VARCHAR",1000))->isNotNull()->setComment("Mensagem do Contato"))
                ->addColumn((new column("enviado","TINYINT"))->isNotNull()->setComment("Contato Enviado? (1 = Sim/0 = Não)"));
    }

    public function getByFilter(?string $nome = null,?string $email = null,?string $telefone = null,?int $enviado = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($email){
            $this->addFilter("email","LIKE","%".$email."%");
        }

        if($telefone){
            $this->addFilter("telefone","LIKE","%".$telefone."%");
        }

        if($enviado || $enviado === 0){
            $this->addFilter("enviado","=",$enviado);
        }

        $this->addOrder("id","DESC");

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

        $result = $this->selectAll();
        
        if($result)
            return $result;
        
        return [];
    }

    public static function prepareData(array $dados){
        $finalResult = [];
        foreach ($dados as $dado){

            if(is_subclass_of($dado,"app\db\db")){
                $dado = $dado->getArrayData();
            }

            $dado["enviado"] = $dado["enviado"]?"Sim":"Não";
            $dado["telefone"] = $dado["telefone"]?functions::formatPhone($dado["telefone"]):"";

            $finalResult[] = $dado;
        }

        return $finalResult;
    }

    public function set():self|null
    {
        $mensagens = [];

        if($this->id && !(new self)->get($this->id)->id)
            $mensagens[] = "Contato não encontrada";

        if(!($this->nome = htmlspecialchars(trim($this->nome))))
            $mensagens[] = "Nome é obrigatorio";

        $this->telefone = functions::onlynumber($this->telefone);

        if(!(functions::validaTelefone($this->telefone)))
            $mensagens[] = "Telefone é invalido";

        if(!functions::validaEmail($this->email))
            $mensagens[] = "Email é invalido";

        if(!($this->assunto = htmlspecialchars(trim($this->assunto))))
            $mensagens[] = "Assunto é obrigatorio";

        if(!($this->mensagem = htmlspecialchars(trim($this->mensagem))))
            $mensagens[] = "Mensagem é obrigatorio";

        if($this->enviado < 0 || $this->enviado > 1){
            $mensagens[] = "O valor de enviado deve ser entre 1 e 0"; 
        }
        
        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Contato salvo com sucesso");
            return $this;
        }
        
        return null;
    }
}