<?php 
namespace app\models\main;
use app\models\usuario;
use app\models\usuarioBloqueio;
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
final class usuarioModel extends model{

    /**
     * Obtém um usuário pelo ID.
     * 
     * @param int|null|string $value O Valor do usuário a ser buscado.
     * @param int|null|string $column A Coluna do usuário a ser buscado.
     * @return object Retorna os dados do usuário ou objeto se não encontrado.
    */
    public static function get(int|null|string $value = null,string $column = "id",int $limit = 1):object
    {
        return (new usuario)->get($value,$column,$limit);
    }

    /**
     * Obtém o usuário logado.
     * 
     * @return object|bool Retorna os dados do usuário logado ou null se não houver usuário logado.
    */
    public static function getLogged():object|bool
    {
        if($user = session::get("user"))
            return $user;

        loginModel::deslogar();
        return false;
    }

    /**
     * Obtém um usuário pelo CPF/CNPJ e e-mail.
     * 
     * @param string $cpf_cnpj O CPF ou CNPJ do usuário.
     * @param string $email O e-mail do usuário.
     * @return object|bool Retorna um array com os dados do usuário ou um array vazio se não encontrado.
    */
    public static function getByCpfEmail(string $cpf_cnpj,string $email):object|bool
    {
        $this = new usuario;

        $usuario = $this->addFilter("cpf_cnpj", "=", functions::onlynumber($cpf_cnpj))->addFilter("email", "=", $email)->addLimit(1)->selectAll();

        return $usuario[0] ?? false;
    }

    /**
     * Obtém um usuário pelo CPF/CNPJ.
     * 
     * @param string $cpf_cnpj O CPF ou CNPJ do usuário.
     * @return array Retorna um array com os dados do usuário ou um array vazio se não encontrado.
    */
    public static function getByCpfCnpj(string $cpf_cnpj):array
    {

        $this = new usuario;

        $usuario = $this->addFilter("cpf_cnpj", "=", $cpf_cnpj)->selectAll();

        return $usuario;
    }

    /**
     * Obtém um usuário pelo e-mail.
     * 
     * @param string $email O e-mail do usuário.
     * @return array Retorna um array com os dados do usuário ou um array vazio se não encontrado.
    */
    public static function getByEmail(string $email):array
    {

        $this = new usuario;

        $usuario = $this->addFilter("email", "=", $email)->selectAll();

        return $usuario;
    }

     /**
     * Obtém um usuário pelo id da empresa.
     * 
     * @param int $id_empresa O id da empresa.
     * @param int $tipo_usuario O id da empresa.
     * @param int $limit limit da query (opcional).
     * @param int $offset offset da query(opcional).
     * @return array Retorna um array com os dados do usuário ou um array vazio se não encontrado.
    */
    public static function getByEmpresa(int $id_empresa,?string $nome = null,?int $id_funcionario = null,?int $tipo_usuario = null,?int $limit = null,?int $offset = null):array
    {
        $this = new usuario;

        $this->addFilter("id_empresa", "=", $id_empresa);

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($id_funcionario){
            $this->addJoin("cliente","cliente.id_funcionario",$id_funcionario);
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

        return $this->selectColumns('usuario.id','usuario.nome','usuario.cpf_cnpj','usuario.telefone','usuario.senha','usuario.email','usuario.tipo_usuario','usuario.id_empresa');
    }

    /**
     * Obtém usuários pelo tipo de usuário e ID da agenda.
     * 
     * @param int $tipo_usuario O tipo de usuário.
     * @param string $id_agenda O ID da agenda.
     * @return array Retorna um array de usuários.
    */
    public static function getByTipoUsuarioAgenda(int $tipo_usuario,string $id_agenda):array
    {
        $this = new usuario;
        $usuarios = $this->addJoin("agendamento","usuario.id","agendamento.id_usuario")
                        ->addFilter("tipo_usuario","=",$tipo_usuario)
                        ->addFilter("agendamento.id_agenda","=",$id_agenda)
                        ->addFilter("usuario.tipo_usuario","=",$tipo_usuario)
                        ->addGroup("usuario.id")
                        ->selectColumns('usuario.id','usuario.nome','usuario.cpf_cnpj','usuario.telefone','usuario.senha','usuario.email','usuario.tipo_usuario','usuario.id_empresa');
                        
        return $usuarios;
    }

    /**
     * Bloqueia um usuario em uma agenda.
     * 
     * @param int $id_usuario O id do usuario.
     * @param int $id_agenda O id da agenda.
     * @return bool true caso sucesso.
    */
    public static function setBloqueio(int $id_usuario,int $id_agenda):bool
    {
        $this = new usuarioBloqueio;

        if(!($this->id_usuario = self::get($id_usuario)->id)){
            $mensagens[] = "Usuario não existe";
        }
        if(!($this->id_agenda = agendaModel::get($id_agenda)->id)){
            $mensagens[] = "Agenda não existe";
        }

        if($this->store()){
            return true;
        }

        return false;
    }

    /**
     * Bloqueia um usuario em uma agenda.
     * 
     * @param int $id_usuario O id do usuario.
     * @param int $id_agenda O id da agenda.
     * @return bool true caso sucesso.
    */
    public static function deleteBloqueio(int $id_usuario,int $id_agenda):bool
    {
        $this = new usuarioBloqueio;

        $this->addFilter($id_usuario,"=",$id_usuario);

        $this->addFilter($id_agenda,"=",$id_agenda);

        $usuarioBloqueio = $this->selectAll();

        if(isset($usuarioBloqueio[0]->id) && $usuarioBloqueio[0]->id){
            return $this->delete($usuarioBloqueio[0]->id);
        }

        return false;
    }


    /**
     * Insere ou atualiza um usuário.
     * 
     * @param string $nome O nome do usuário.
     * @param string $cpf_cnpj O CPF ou CNPJ do usuário.
     * @param string $email O e-mail do usuário.
     * @param string $telefone O telefone do usuário.
     * @param string $senha A senha do usuário.
     * @param string $id O ID do usuário (opcional).
     * @param int $tipo_usuario O tipo de usuário (padrão é 3).
     * @param int $id_empresa O ID da empresa associada (opcional, padrão é "null").
     * @param bool $valid_fk valida outras tabelas vinculadas.
     * @return int|bool Retorna o ID do usuário inserido ou atualizado se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function set(string $nome,string $cpf_cnpj,string $email,string $telefone,string $senha,int|null $id = null,int $tipo_usuario = 3,int|null $id_empresa = null,bool $valid_fk = true):int|bool
    {

        $values = new usuario;

        $mensagens = [];

        if(!($this->nome = htmlspecialchars((trim($nome))))){
            $mensagens[] = "Nome é invalido";
        }

        if(!($this->cpf_cnpj = functions::onlynumber($cpf_cnpj)) || !functions::validaCpfCnpj($cpf_cnpj)){
            $mensagens[] = "CPF/CNPJ invalido";
        }

        if(!($this->email = htmlspecialchars(filter_var(trim($email), FILTER_VALIDATE_EMAIL)))){
            $mensagens[] = "E-mail Invalido";
        }

        if(!($this->telefone = functions::onlynumber($telefone)) || !functions::validaTelefone($telefone)){
            $mensagens[] = "Telefone Invalido";
        }

        if(!($this->tipo_usuario = $tipo_usuario) || $this->tipo_usuario < 0 || $this->tipo_usuario  > 3){
            $mensagens[] = "Tipo de Usuario Invalido";
        }

        if(($this->tipo_usuario == 2 || $this->tipo_usuario == 1) && !$id_empresa){
            $mensagens[] = "Informar a empresa é obrigatorio para esse tipo de usuario";
        }

        if(($this->id_empresa = $id_empresa) && $valid_fk && !empresaModel::get($this->id_empresa)->id){
            $mensagens[] = "Empresa não existe";
        }

        $usuario = self::get($id);
        if(($this->id = $id) && !$usuario->id){
            $mensagens[] = "Usuario não existe";
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
        return (new usuario)->delete($id);
    }

}