<?php
namespace app\models\main;

use app\models\grupoFuncionario;
use app\models\funcionario;
use app\helpers\mensagem;
use app\models\funcionarioGrupoFuncionario;
use app\models\abstract\model;

/**
 * Classe grupoFuncionarioModel
 * 
 * Esta classe fornece métodos para interagir com os dados de grupos de funcionários.
 * Ela utiliza a classe grupoFuncionario para realizar operações de consulta, inserção e exclusão no banco de dados.
 * 
 * @package app\models\main
 */
final class grupoFuncionarioModel extends model
{

    /**
     * Obtém um grupo de funcionário pelo ID.
     * 
     * @param int $id O ID do grupo de funcionário a ser buscado.
     * @return object Retorna os dados do grupo de funcionário ou null se não encontrado.
     */
    public static function get(int $id = null):object{
        return (new grupoFuncionario)->get($id);
    }

    /**
     * Obtém grupos de funcionários por ID da empresa.
     * 
     * @param int $id_empresa O ID da empresa dos grupos de funcionários a serem buscados.
     * @param string $nome para filtrar por nome.
     * @param int $limit limit querry (opcional).
     * @param int $offset offset querry (opcional).
     * @return array Retorna um array com os grupos de funcionários da empresa especificada.
     */
    public static function getByEmpresa(int $id_empresa,string $nome = null,?int $limit = null,?int $offset = null):array{
        $this = new grupoFuncionario;

        $this->addFilter("id_empresa", "=", $id_empresa);

        if($nome){
            $this->addFilter("nome", "like", "%".$nome."%");
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

        $values = $this->selectColumns("id","nome");

        return $values;
    }

    /**
     * Busca todos os grupos vinculados a um funcionario
     * 
     * @param int $id_funcionario O ID do funcionário.
     * @return array Retorna array com os registros encontrados.
    */
    public static function getByFuncionario(int $id_funcionario):array
    {

        $this = new funcionarioGrupoFuncionario;

        $this->addJoin(grupoFuncionario::table,"id","id_grupo_funcionario")
        ->addFilter("id_funcionario","=",$id_funcionario);

        return $this->selectAll();
    }

    /**
     * Busca todos os funcionarios vinculados a um grupo
     * 
     * @param int $id_grupo_funcionario O ID do grupo de funcionario.
     * @return array Retorna array com os registros encontrados.
    */
    public static function getVinculados(int $id_grupo_funcionario):array
    {
        $this = new funcionarioGrupoFuncionario;

        $this->addJoin(funcionario::table,"id","id_funcionario")
        ->addFilter("id_grupo_funcionario","=",$id_grupo_funcionario);

        return $this->selectColumns("funcionario.id","funcionario.nome");
    }

    /**
     * Desvincula um funcionario de um grupo de funcionarios
     * 
     * @param int $id_grupo O ID do grupo de funcionário.
     * @param int $id_funcionario O ID do funcionário.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function detachFuncionario(int $id_grupo,int $id_funcionario):bool
    {
        $this = new funcionarioGrupoFuncionario;

        if($this->addFilter("id_grupo_funcionario","=",$id_grupo)->addFilter("id_funcionario","=",$id_funcionario)->deleteByFilter()){
            mensagem::setSucesso("Funcionario Desvinculado Com Sucesso");
            return true;
        }

        mensagem::setErro("Erro ao Desvincular Funcionario");
        return false;
    }

    /**
     * Insere ou atualiza um grupo de funcionário.
     * 
     * @param string $nome O nome do grupo de funcionário.
     * @param int $id O ID do grupo de funcionário (opcional).
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function set(string $nome,int $id_empresa,int $id = null):bool{
        $values = new grupoFuncionario;
        
        $mensagens = [];

        if($this->id = $id && !self::get($this->id)->id){
            $mensagens[] = "Grupo de Funcionarios não encontrada";
        }
        
        if(!empresaModel::get($this->id_empresa = $id_empresa)->id){
            $mensagens[] = "Empresa não encontrada";
        }

        if(!$this->nome = htmlspecialchars((trim($nome)))){
            $mensagens[] = "Nome invalido";
        }

        $retorno = $this->store();

        if ($retorno == true){
            mensagem::setSucesso("Grupo de funcionarios salvo com sucesso");
            return true;
        }
        
        mensagem::setErro("Erro ao salvar grupo de funcionarios");
        return false;
    }

    /**
     * Exclui um grupo de funcionário pelo ID.
     * 
     * @param string $id O ID do grupo de funcionário a ser excluído.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function delete(int $id):bool{
        return (new grupoFuncionario)->delete($id);
    }

}
