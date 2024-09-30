<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

class calendarNow extends model {

    public const table = "calendarnow";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela do calendarnow"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID calendar"))
                ->addColumn((new column("telefone","VARCHAR",15))->setComment("Telefone"))
                ->addColumn((new column("celular","VARCHAR",15))->setComment("Celular"))
                ->addColumn((new column("descricao","VARCHAR",300))->setComment("Meta Descrição"))
                ->addColumn((new column("keywords","VARCHAR",300))->setComment("Meta Palavras Chaves"))
                ->addColumn((new column("nome","VARCHAR",150))->setComment("Nome empresa"))
                ->addColumn((new column("cep","VARCHAR",8))->isNotNull()->setComment("CEP"))
                ->addColumn((new column("id_cidade","INT"))->isForeingKey(cidade::table())->setComment("ID da tabela estado"))
                ->addColumn((new column("id_estado","INT"))->isForeingKey(estado::table())->setComment("ID da tabela cidade"))
                ->addColumn((new column("bairro","VARCHAR",300))->isNotNull()->setComment("Bairro"))
                ->addColumn((new column("rua","VARCHAR",300))->isNotNull()->setComment("Rua"))
                ->addColumn((new column("numero","INT"))->isNotNull()->setComment("Numero"))
                ->addColumn((new column("complemento","VARCHAR",300))->setComment("Complemento do endereço"))
                ->addColumn((new column("latitude","DECIMAL","10,6"))->setComment("Latitude"))
                ->addColumn((new column("longitude","DECIMAL","10,6"))->setComment("longitude"))
                ->addColumn((new column("horario_atendimento","VARCHAR",250))->setComment("Horario"))
                ->addColumn((new column("contato_sac","VARCHAR",150))->setComment("Contato Sac"))
                ->addColumn((new column("contato_email","VARCHAR",150))->setComment("Contato Email"))
                ->addColumn((new column("contato_comercial","VARCHAR",150))->setComment("Contato Comercial"))
                ->addColumn((new column("smtp_servidor","VARCHAR",150))->setComment("SMTP Servidor"))
                ->addColumn((new column("smtp_port","SMALLINT"))->setComment("SMTP Port"))
                ->addColumn((new column("smtp_encryption","VARCHAR",3))->setComment("SMTP Encryption"))
                ->addColumn((new column("smtp_usuario","VARCHAR",150))->setComment("SMTP Usuario"))
                ->addColumn((new column("smtp_senha","VARCHAR",150))->setComment("SMTP Senha"))
                ->addColumn((new column("recaptcha_site_key","VARCHAR",150))->setComment("Chave Site Recapcha"))
                ->addColumn((new column("recaptcha_secret_key","VARCHAR",150))->setComment("Chave Secreta Recapcha"))
                ->addColumn((new column("recaptcha_minimal_score","TINYINT"))->setComment("Score Minimo Recapcha"))
                ->addColumn((new column("ativo","TINYINT"))->setDefaut(1)->setComment("Ativo"));
    }

    public function set():null|self
    {
        $mensagens = [];
        
        if(!functions::validaCep($this->cep = functions::onlynumber($this->cep))){
            $mensagens[] = "CEP é invalido";
        }

        if(!((new estado)->get($this->id_estado)->id)){
            $mensagens[] = "Estado é invalido";
        }

        if(!((new cidade)->get($this->id_cidade)->id)){
            $mensagens[] = "Cidade é invalida";
        }

        $this->smtp_encryption   = htmlspecialchars(trim($this->smtp_encryption));

        if($this->smtp_encryption !== "tls" && $this->smtp_encryption !== "ssl"){
            $mensagens[] = "Criptografia SMTP Invalida";
        }

        $this->recaptcha_minimal_score = intval($this->recaptcha_minimal_score);

        if($this->recaptcha_minimal_score < 0 && $this->recaptcha_minimal_score > 10){
            $mensagens[] = "Recapcha Score Minimo deve ser maior que 0 e menor que 10";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        $this->telefone = functions::formatPhone($this->telefone);
        $this->celular = functions::formatPhone($this->celular);
        $this->horario_atendimento = htmlspecialchars(trim($this->horario_atendimento));
        $this->latitude = floatval($this->latitude);
        $this->longitude = floatval($this->longitude);
        $this->contato_email = htmlspecialchars(trim($this->contato_email));

        if(str_contains($this->contato_sac,"@"))
            $this->contato_sac = htmlspecialchars(trim($this->contato_sac));
        else 
            $this->contato_sac = functions::formatPhone($this->contato_sac);

        if(str_contains($this->contato_comercial,"@"))
            $this->contato_comercial = htmlspecialchars(trim($this->contato_comercial));
        else 
            $this->contato_comercial = functions::formatPhone($this->contato_comercial);

        $this->bairro      = htmlspecialchars(trim($this->bairro));
        $this->rua         = htmlspecialchars(trim($this->rua));
        $this->numero      = htmlspecialchars(trim($this->numero));
        $this->complemento = htmlspecialchars(trim($this->complemento));
        $this->nome        = htmlspecialchars(trim($this->nome));
        $this->keywords    = htmlspecialchars(trim($this->keywords));
        $this->descricao   = htmlspecialchars(trim($this->descricao));
        $this->smtp_servidor = htmlspecialchars(trim($this->smtp_servidor));
        $this->smtp_port   = intval($this->smtp_port);
        $this->smtp_usuario = htmlspecialchars(trim($this->smtp_usuario));
        $this->smtp_senha    = htmlspecialchars(trim($this->smtp_senha));
        $this->recaptcha_site_key = htmlspecialchars(trim($this->recaptcha_site_key));
        $this->recaptcha_secret_key = htmlspecialchars(trim($this->recaptcha_secret_key));
        

        if ($this->store()){
            mensagem::setSucesso("Empresa salva com sucesso");
            return $this;
        }
        
        return null;
    }

    public static function seed(){
        $calendarNow = new self;
        if(!$calendarNow->addLimit(1)->selectColumns("id")){
            $calendarNow->ativo = 1;
            $calendarNow->store();
        }
    }
}