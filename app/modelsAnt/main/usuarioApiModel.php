<?php 
namespace app\models\main;
use app\models\usuarioApi;
use app\helpers\functions;
use app\helpers\mensagem;
use app\models\abstract\model;
use app\models\main\loginModel;
use core\session;

/**
 * Classe usuarioModel
 * 
 * Esta classe fornece métodos para interagir com os dados de usuários.
 * Ela utiliza a classe usuario para realizar operações de consulta, inserção e exclusão no banco de dados.
 * 
 * @package app\models\main
*/
final class usuarioApiModel extends model{

    /**
     * Obtém um usuário pelo ID.
     * 
     * @param int|null|string $value O Valor do usuário a ser buscado.
     * @param int|null|string $column A Coluna do usuário a ser buscado.
     * @return object Retorna os dados do usuário ou objeto se não encontrado.
    */
    public static function get(int|null|string $value = null,string $column = "id"):object
    {
        return (new usuarioApi)->get($value,$column);
    }

    /**
     * Obtém o usuário logado.
     * 
     * @return object|bool Retorna os dados do usuário logado ou null se não houver usuário logado.
    */
    public static function getLogged():object|bool
    {
        if($user = session::get("userApi"))
            return $user;

        loginModel::deslogar();
        return false;
    }

    /**
     * Insere ou atualiza um usuário.
     * 
     * @param string $nome O nome do usuário.
     * @param string $senha A senha do usuário.
     * @param string $id O ID do usuário (opcional).
     * @param int $tipo_usuario O tipo de usuário (padrão é 3).
     * @param int $id_empresa O ID da empresa associada (opcional, padrão é "null").
     * @return int|bool Retorna o ID do usuário inserido ou atualizado se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function set(string $nome,string|null $senha = null,int|null $id = null,int $tipo_usuario = 2,int|null $id_empresa = null):int|bool
    {
        $values = new usuarioApi;

        $mensagens = [];

        if(!($this->nome = htmlspecialchars((trim($nome))))){
            $mensagens[] = "Nome é invalido";
        }

        if(!($this->tipo_usuario = $tipo_usuario) || $this->tipo_usuario < 1 || $this->tipo_usuario > 2){
            $mensagens[] = "Tipo de Usuario Invalido";
        }

        if(($this->id_empresa = $id_empresa) && !empresaModel::get($this->id_empresa)->id){
            $mensagens[] = "Empresa não existe";
        }

        $usuario = self::get($this->id);
        if(($this->id = $id) && !$usuario->id){
            $mensagens[] = "Usuario da Api não existe";
        }

        if(!$this->id && !$senha){
            $mensagens[] = "Senha obrigatoria para usuario não cadastrados";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        $this->senha = $senha ? password_hash(trim($senha),PASSWORD_DEFAULT) : $usuario->senha;

        $retorno = $this->store();
        
        if ($retorno == true){
            mensagem::setSucesso("Salvo com sucesso");
            return $this->id;
        }

        mensagem::setErro("Erro ao cadastrar usuario");
        return False;
    }

    /**
     * Exclui um registro de usuário.
     * 
     * @param int $id O ID do usuário a ser excluído.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function delete(int $id):bool
    {
        return (new usuarioApi)->delete($id);
    }

}