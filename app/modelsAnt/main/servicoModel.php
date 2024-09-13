<?php 
namespace app\models\main;

use app\helpers\functions;
use app\models\servico;
use app\models\servicoFuncionario;
use app\models\servicoGrupoServico;
use app\helpers\mensagem;
use app\models\funcionario;
use app\db\transactionManeger;
use app\models\abstract\model;

/**
 * Classe servicoModel
 * 
 * Esta classe fornece métodos para interagir com os serviços.
 * Ela utiliza a classe servico para realizar operações de consulta, inserção, atualização e exclusão no banco de dados.
 * 
 * @package app\models\main
*/
final class servicoModel extends model{

    /**
     * Obtém um serviço pelo ID.
     * 
     * @param int $id O ID do serviço.
     * @return object Retorna o objeto do serviço ou null se não encontrado.
    */
    public static function get(null|int|string $value = null,string $column = "id"):object
    {
        return (new servico)->get($value,$column);
    }

    /**
     * Obtém uma lista de serviços por empresa, podendo filtrar por nome, funcionário ou grupo de serviço.
     * 
     * @param int $id_empresa O ID da empresa.
     * @param string|null $nome O nome do serviço (opcional).
     * @param int|null $id_funcionario O ID do funcionário (opcional).
     * @param int|null $id_grupo_servico O ID do grupo de serviço (opcional).
     * @param int $limit limit da query (opcional).
     * @param int $offset offset da query(opcional).
     * @return array Retorna um array com os serviços filtrados.
    */
    public static function getListByEmpresa(int $id_empresa,string $nome = null,int $id_funcionario = null,int $id_grupo_servico = null,?int $limit = null,?int $offset = null):array
    {
        $this = new servico;

        $this->addFilter("servico.id_empresa","=",$id_empresa);

        if($nome){
            $this->addFilter("servico.nome","like","%".$nome."%");
        }

        if($id_funcionario){
            $this->addJoin("servico_funcionario","servico_funcionario.id_servico","servico.id");
            $this->addFilter("servico_funcionario.id_funcionario","=",$id_funcionario);
        }

        if($id_grupo_servico){
            $this->addJoin("servico_grupo_servico","servico_grupo_servico.id_servico","servico.id");
            $this->addFilter("servico_grupo_servico.id_grupo_servico","=",$id_grupo_servico);
        }

        $this->addGroup("servico.id");

        if($limit && $offset){
            self::setLastCount($this);
            $this->addLimit($limit);
            $this->addOffset($offset);
        }
        elseif($limit){
            self::setLastCount($this);
            $this->addLimit($limit);
        }
        
        $values = $this->selectColumns("servico.id","servico.nome","servico.tempo","servico.valor");

        $valuesFinal = [];

        if ($values){
            foreach ($values as $value){
                if ($value->valor){
                    $value->valor = functions::formatCurrency($value->valor);
                }
                $valuesFinal[] = $value;
            }

            return $valuesFinal;
        }

        return [];
    }

    /**
     * Obtém os serviços associados a um funcionário.
     * 
     * @param int $id_funcionario O ID do funcionário.
     * @return array Retorna um array com os serviços associados ao funcionário.
    */
    public static function getByFuncionario(int $id_funcionario):array
    {
        $this = new servico;

        $this->addJoin("servico_funcionario","servico_funcionario.id_servico","servico.id");
        $this->addFilter("servico_funcionario.id_funcionario","=",$id_funcionario);
        
        $this->addGroup("servico.id");
        
        $values = $this->selectColumns("servico.id","servico.nome","servico.tempo","servico.valor");

        return $values;
    }

    /**
     * Associa um serviço a um grupo de serviço.
     * 
     * @param int $id_servico O ID do serviço.
     * @param int $id_grupo_servico O ID do grupo de serviço.
     * @return bool Retorna o ID da associação se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function setServicoGrupoServico(int $id_servico,int $id_grupo_servico):bool
    {
        $values = new servicoGrupoServico;

        if(!grupo(new servico)->get($this->id_grupo_servico = $id_grupo_servico)->id){
            mensagem::setErro("Grupo de serviço não existe");
            return false;
        }

        if(!self::get($this->id_servico = $id_servico)->id){
            mensagem::setErro("Serviço não existe");
            return false;
        }

        $result = $this->addFilter("id_grupo_servico","=",$id_grupo_servico)
                        ->addFilter("id_servico","=",$id_servico)
                        ->selectAll();

        if (!$result){
           
            mensagem::setSucesso("Serviço Adicionado com Sucesso");

            return $this->storeMutiPrimary();
        }

        mensagem::setSucesso("Serviço já Adicionado");

        return True;
    }

    /**
     * Busca todos os serviços vinculados a um grupo
     * 
     * @param int $id_servico O ID do grupo de servico.
     * @return array Retorna array com os registros encontrados.
    */
    public static function getVinculados(int $id_servico):array
    {
        $this = new servicoFuncionario;

        $this->addJoin(funcionario::table,"id","id_funcionario")
            ->addFilter("id_servico","=",$id_servico);

        return $this->selectColumns("funcionario.id","funcionario.nome");
    }

    /**
     * Desvincula um funcionario de um serviço
     * 
     * @param int $id_funcionario O ID do funcionário.
     * @param int $id_servico O ID do servico.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function detachFuncionario(int $id_funcionario,int $id_servico):bool
    {
        $this = new servicoFuncionario;

        if($this->addFilter("id_servico","=",$id_servico)->addFilter("id_funcionario","=",$id_funcionario)->deleteByFilter()){
            mensagem::setSucesso("Funcionario Desvinculado Com Sucesso");
            return true;
        }

        mensagem::setErro("Erro ao Desvincular Funcionario");
        return false;
    }

    /**
     * Associa um serviço a um funcionário.
     * 
     * @param int $id_servico O ID do serviço.
     * @param int $id_funcionario O ID do funcionário.
     * @return bool Retorna o ID da associação se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function setServicoFuncionario(int $id_servico,int $id_funcionario):bool
    {
        $values = new servicoFuncionario;

        if(!(new funcionario)->get($this->id_funcionario = $id_funcionario)){
            mensagem::setErro("Funcionario não existe");
            return false;
        }
        if(!self::get($this->id_servico = $id_servico)){
            mensagem::setErro("Serviço não existe");
            return false;
        }

        $result = $this->addFilter("id_funcionario","=",$id_funcionario)
                    ->addFilter("id_servico","=",$id_servico)
                    ->selectAll();

        if (!$result){

            mensagem::setSucesso("Serviço Adicionado com Sucesso");

            return $this->storeMutiPrimary($values);
        }

        mensagem::setSucesso("Serviço já Adicionado");

        return True;
    }

    /**
     * Insere ou atualiza um serviço.
     * 
     * @param string $nome O nome do serviço.
     * @param float $valor O valor do serviço.
     * @param string $tempo O tempo estimado do serviço.
     * @param int|string $id_empresa O ID da empresa.
     * @param string $id O ID do serviço (opcional).
     * @return int|bool Retorna o ID do serviço se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function set(string $nome,float $valor,string $tempo,int|null $id_empresa = null,int|null $id = null):int|bool
    {

        $values = new servico;
        
        $mensagens = [];

        if(!$this->nome = htmlspecialchars((trim($nome)))){
            $mensagens[] = "Nome é invalido";
        }

        if(($this->valor = $valor) <= 0){
            $mensagens[] = "Valor do serviço invalido";
        }

        if(!functions::validaHorario($this->tempo = functions::formatTime($tempo))){
            $mensagens[] = "Tempo do serviço invalido";
        }

        if(($this->id_empresa = $id_empresa) && !empresaModel::get($this->id_empresa)){
            $mensagens[] = "Empresa não existe";
        }

        if($id && !self::get($id)){
            $mensagens[] = "Serviço não existe";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        $this->id = $id;

        if ($values)
            $retorno = $this->store();

        if ($retorno == true){
            mensagem::setSucesso("Serviço salvo com sucesso");
            return $this->id;
        }

        return False;
    }

    /**
     * Exclui um serviço pelo ID.
     * 
     * @param int $id O ID do serviço a ser excluído.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function delete(int $id):bool{
        try {
            transactionManeger::init();
            transactionManeger::beginTransaction();

            self::deleteAllServicoFuncionario($id);
            grupo(new servico)->detachAllServico($id);

            if((new servico)->delete($id)){
                mensagem::setSucesso("agenda deletada com sucesso");
                transactionManeger::commit();
                return true;
            }

            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }catch (\exception $e){
            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }
    }

    /**
     * Exclui a associação de um serviço com uma funcionario.
     * 
     * @param int $id_servico O ID do serviço.
     * @param int $id_funcionario O ID do funcionario.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function deleteServicoFuncionario(int $id_servico,int $id_funcionario):bool
    {
        $this = new servicoFuncionario;

        return $this->addFilter("servico_funcionario.id_servico","=",$id_servico)->addFilter("servico_funcionario.id_funcionario","=",$id_funcionario)->deleteByFilter();
    }

    /**
     * Exclui todas as associações de um serviço com uma funcionarios.
     * 
     * @param int $id_servico O ID do serviço.
     * @param int $id_funcionario O ID do funcionario.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function deleteAllServicoFuncionario(int $id_servico):bool
    {
        $this = new servicoFuncionario;

        return $this->addFilter("servico_funcionario.id_servico","=",$id_servico)->deleteByFilter();
    }

}