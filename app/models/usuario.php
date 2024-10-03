<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

final class usuario extends model {
    public const table = "usuario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de usuários"))
                ->addColumn((new column("id","INT"))->isPrimary()->isNotNull()->setComment("ID do usuário"))
                ->addColumn((new column("nome", "VARCHAR", 500))->isNotNull()->setComment("Nome do usuário"))
                ->addColumn((new column("cpf_cnpj", "VARCHAR", 14))->isUnique()->isNotNull()->setComment("CPF ou CNPJ do usuário"))
                ->addColumn((new column("telefone", "VARCHAR", 11))->isNotNull()->setComment("Telefone do usuário"))
                ->addColumn((new column("senha", "VARCHAR", 150))->isNotNull()->setComment("Senha do usuário"))
                ->addColumn((new column("email", "VARCHAR", 200))->isUnique()->setComment("Email do usuário"))
                ->addColumn((new column("tipo_usuario","INT"))->isNotNull()->setComment("Tipo de usuário: 0 -> ADM, 1 -> empresa, 2 -> funcionario, 3 -> usuário, 4 -> cliente cadastrado"))
                ->addColumn((new column("id_empresa","INT"))->isForeingKey(empresa::table())->setComment("ID da empresa"));
    }

    public function getByCpfEmail(string $cpf_cnpj,string $email):object|bool
    {
        $usuario = $this->addFilter("cpf_cnpj", "=", functions::onlynumber($cpf_cnpj))->addFilter("email", "=", $email)->addLimit(1)->selectAll();

        return $usuario[0] ?? false;
    }

    public function getByFilter(int $id_empresa,?string $nome = null,?int $id_funcionario = null,?int $tipo_usuario = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array
    {
        $this->addFilter("id_empresa", "=", $id_empresa);

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($id_funcionario){
            $this->addJoin("funcionario","funcionario.id_funcionario",$id_funcionario);
        }

        if($tipo_usuario !== null){
            $this->addFilter("tipo_usuario", "=", $tipo_usuario);
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

        return $this->selectColumns(self::table.'.id',self::table.'.nome',self::table.'.cpf_cnpj',self::table.'.telefone',self::table.'.senha',self::table.'.email',self::table.'.tipo_usuario',self::table.'.id_empresa');
    }

    public function prepareData(array $dados){
        $dadosFinal = [];
        if ($dados){
            foreach ($dados as $dado){

                if(is_subclass_of($dado,"diogodg\neoorm\db")){
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

    public function getByTipoUsuarioAgenda(int $tipo_usuario,string $id_agenda):array
    {
        return $this->addJoin(agendaUsuario::table,usuario::table.".id",agendaUsuario::table.".id_usuario")
                    ->addFilter(usuario::table.".tipo_usuario","=",$tipo_usuario)
                    ->addFilter(agendaUsuario::table.".id_agenda","=",$id_agenda)
                    ->addGroup(usuario::table.".id")
                    ->selectColumns(self::table.'.id',self::table.'.nome',self::table.'.cpf_cnpj',self::table.'.telefone',self::table.'.senha',self::table.'.email',self::table.'.tipo_usuario',self::table.'.id_empresa');
    }

    public function set(bool $valid_fk = true):usuario|null
    {
        $mensagens = [];

        $usuario = (new self);

        if(!($this->nome = htmlspecialchars((trim($this->nome))))){
            $mensagens[] = "Nome é invalido";
        }

        if(!($this->cpf_cnpj = functions::onlynumber($this->cpf_cnpj)) || !functions::validaCpfCnpj($this->cpf_cnpj)){
            $mensagens[] = "CPF/CNPJ invalido";
        }

        if(!$this->id && ($usuario->get($this->cpf_cnpj,"cpf_cnpj")->id)){
            $mensagens[] = "CPF/CNPJ já Cadastrado";
        }

        if(!($this->email = htmlspecialchars(filter_var(trim($this->email), FILTER_VALIDATE_EMAIL)))){
            $mensagens[] = "E-mail Invalido";
        }

        if(!$this->id && ($usuario->get($this->email,"email")->id)){
            $mensagens[] = "Email já Cadastrado";
        }

        if(!($this->telefone = functions::onlynumber($this->telefone)) || !functions::validaTelefone($this->telefone)){
            $mensagens[] = "Telefone Invalido";
        }

        if(!($this->tipo_usuario) || $this->tipo_usuario < 0 || $this->tipo_usuario  > 3){
            $mensagens[] = "Tipo de Usuario Invalido";
        }

        if(($this->tipo_usuario == 2 || $this->tipo_usuario == 1) && !$this->id_empresa){
            $mensagens[] = "Informar a empresa é obrigatorio para esse tipo de usuario";
        }

        if(($this->id_empresa) && $valid_fk && !(new empresa)->get($this->id_empresa)->id){
            $mensagens[] = "Empresa não existe";
        }

        $usuario = $usuario->get($this->id);
        if($this->id && !$usuario->id){
            $mensagens[] = "Usuario não existe";
        }

        if(!$this->id && !$this->senha){
            $mensagens[] = "Senha obrigatoria para usuario não cadastrados";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        $this->senha = $this->senha ? password_hash(trim($this->senha),PASSWORD_DEFAULT) : $usuario->senha;

        if ($this->store()){
            mensagem::setSucesso("Salvo com sucesso");
            return $this;
        }

        mensagem::setErro("Erro ao cadastrar usuario");
        return null;
    }
}