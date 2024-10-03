<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\mensagem;

final class usuarioApi extends model {
    public const table = "usuario_api";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de usuários da api"))
                ->addColumn((new column("id", "INT"))->isPrimary()->isNotNull()->setComment("ID do usuário"))
                ->addColumn((new column(usuario::table, "VARCHAR", 50))->isNotNull()->setComment("Nome do usuário"))
                ->addColumn((new column("senha", "VARCHAR", 100))->isNotNull()->setComment("Senha do usuário"))
                ->addColumn((new column("tipo_usuario", "INT"))->isNotNull()->setComment("Tipo de usuário: 1 -> programa, 2 -> empresa"))
                ->addColumn((new column("id_empresa", "INT"))->isForeingKey(empresa::table())->setComment("ID da empresa"));
    }

    public function set():usuarioApi|null
    {
        $mensagens = [];

        if(!($this->nome = htmlspecialchars((trim($this->nome))))){
            $mensagens[] = "Nome é invalido";
        }

        if(!($this->tipo_usuario) || $this->tipo_usuario < 1 || $this->tipo_usuario > 2){
            $mensagens[] = "Tipo de Usuario Invalido";
        }

        if(($this->id_empresa) && !(new empresa)->get($this->id_empresa)->id){
            $mensagens[] = "Empresa não existe";
        }

        $usuario = self::get($this->id);
        if(($this->id) && !$usuario->id){
            $mensagens[] = "Usuario da Api não existe";
        }

        if(!$this->id && !$this->senha){
            $mensagens[] = "Senha obrigatoria para usuario não cadastrados";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        $this->senha = $this->senha ? password_hash(trim($this->senha),PASSWORD_DEFAULT) : $usuario->senha;

        $retorno = $this->store();
        
        if ($retorno == true){
            mensagem::setSucesso("Salvo com sucesso");
            return $this->id;
        }

        mensagem::setErro("Erro ao cadastrar usuario");
        return False;
    }
}