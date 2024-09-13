<?php 
namespace app\models\main;

use app\helpers\functions;
use app\helpers\mensagem;
use app\models\funcionario;
use app\models\agenda;
use app\models\funcionarioGrupoFuncionario;
use app\models\agendaFuncionario;
use app\models\abstract\model;

/**
 * Classe funcionarioModel
 * 
 * Esta classe fornece métodos para interagir com os funcionários.
 * Ela utiliza as classes funcionario, funcionarioGrupoFuncionario e agendaFuncionario
 * para realizar operações de consulta, inserção, atualização e exclusão no banco de dados.
 * 
 * @package app\models\main
*/
final class funcionarioModel extends model{

    /**
     * Obtém um funcionário pelo ID.
     * 
     * @param string $id O ID do funcionário.
     * @return object Retorna o objeto do funcionário ou null se não encontrado.
    */
    public static function get(string|int|null $value = null,string $column = "id"):object
    {
        return (new funcionario)->get($value,$column);
    }

    /**
     * Lista os funcionários por empresa, nome, agenda e grupo de funcionários.
     * 
     * @param int $id_empresa O ID da empresa.
     * @param string $nome O nome do funcionário (opcional).
     * @param int $id_agenda O ID da agenda (opcional).
     * @param int $id_grupo_funcionarios O ID do grupo de funcionários (opcional).
     * @param int $id_agenda O ID da agenda (opcional).
     * @param int $id_grupo_funcionarios O ID do grupo de funcionários (opcional).
     * @return array Retorna um array com os funcionários filtrados.
     */
    public static function getListFuncionariosByEmpresa(int $id_empresa,string $nome = null,int $id_agenda = null,int $id_grupo_funcionarios = null,?int $limit = null,?int $offset = null):array
    {

        $this = new funcionario;

        $this->addJoin("funcionario_grupo_funcionario","funcionario.id","funcionario_grupo_funcionario.id_funcionario","LEFT")
            ->addJoin("agenda_funcionario","agenda_funcionario.id_funcionario","funcionario.id","LEFT")  
            ->addJoin("usuario","usuario.id","funcionario.id_usuario")
           ->addFilter("usuario.id_empresa","=",$id_empresa);

        if($id_grupo_funcionarios)
            $this->addFilter("funcionario_grupo_funcionario.id_grupo_funcionario","=",$id_grupo_funcionarios);

        if($id_agenda)
            $this->addFilter("agenda_funcionario.id_agenda","=",$id_agenda);

        if($nome)
            $this->addFilter("funcionario.nome","LIKE","%".$nome."%");

        if($limit && $offset){
            self::setLastCount($this);
            $this->addLimit($limit);
            $this->addOffset($offset);
        }
        elseif($limit){
            self::setLastCount($this);
            $this->addLimit($limit);
        }
                    
        $funcionarios = $this->selectColumns("funcionario.id,funcionario.cpf_cnpj,funcionario.nome,funcionario.email,funcionario.telefone,hora_ini,hora_fim,hora_almoco_ini,hora_almoco_fim,dias");
        
        $funcionarioFinal = [];
        if ($funcionarios){
            foreach ($funcionarios as $funcionario){
                if ($funcionario->cpf_cnpj){
                    $funcionario->cpf_cnpj = functions::formatCnpjCpf($funcionario->cpf_cnpj);
                }
                if ($funcionario->telefone){
                    $funcionario->telefone = functions::formatPhone($funcionario->telefone);
                }
                if ($funcionario->dias){
                    $funcionario->dias = functions::formatDias($funcionario->dias);
                }
                $funcionarioFinal[] = $funcionario;
            }
        }
        
        return $funcionarioFinal;
    }

    /**
     * Obtém os funcionários por agenda.
     * 
     * @param int $id_agenda O ID da agenda.
     * @return array Retorna um array com os funcionários associados à agenda.
    */
    public static function getByAgenda(int $id_agenda):array
    {
        $this = new agendaFuncionario;

        $values = $this->addJoin("agenda","agenda.id","agenda_funcionario.id_agenda")
                ->addJoin("funcionario","funcionario.id","agenda_funcionario.id_funcionario")
                ->addFilter("agenda_funcionario.id_agenda","=",$id_agenda)
                ->selectColumns("funcionario.id","funcionario.nome","agenda.nome as age_nome","funcionario.cpf_cnpj","funcionario.email","funcionario.telefone","hora_ini","hora_fim","dias");
                
        return $values;
    }

    /**
     * Obtém os funcionários por empresa.
     * 
     * @param int $id_empresa O ID da empresa.
     * @return array Retorna um array com os funcionários associados à empresa.
    */
    public static function getByEmpresa(int $id_empresa):array
    {
        $this = new funcionario;

        $values = $this->addJoin("usuario","usuario.id","funcionario.id_usuario")
                ->addFilter("usuario.id_empresa","=",$id_empresa)
                ->selectColumns("funcionario.id","funcionario.nome","funcionario.cpf_cnpj","funcionario.email","funcionario.telefone","hora_ini","hora_fim","dias");

        return $values;
    }

    /**
     * Obtém os funcionários por empresa.
     * 
     * @param int $id_empresa O ID da empresa.
     * @return array Retorna um array com os funcionários associados à empresa.
    */
    public static function getByUsuario(int $id_usuario):array
    {
        $this = new funcionario;

        return $this
                ->addJoin("agendamento","agendamento.id_funcionario","funcionario.id")
                ->addGroup("funcionario.id")
                ->selectColumns("funcionario.id","funcionario.nome","funcionario.cpf_cnpj","funcionario.email","funcionario.telefone","hora_ini","hora_fim","dias");
    }

    /**
     * Busca todos os grupos vinculados a um funcionario
     * 
     * @param int $id_funcionario O ID do funcionário.
     * @return array Retorna array com os registros encontrados.
    */
    public static function getAgendaByFuncionario(int $id_funcionario):array
    {
        $this = new agendaFuncionario;

        $this->addJoin(agenda::table,"id","id_agenda")
           ->addFilter("id_funcionario","=",$id_funcionario);

        return $this->selectColumns(agenda::table.".id",agenda::table.".nome");
    }

    /**
     * Desvincula um funcionario de um grupo de funcionarios
     * 
     * @param int $id_agenda O ID da agenda.
     * @param int $id_funcionario O ID do funcionário.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function detachAgendaFuncionario(int $id_agenda,int $id_funcionario):bool
    {
        $this = new agendaFuncionario;

        if($this->addFilter("id_agenda","=",$id_agenda)->addFilter("id_funcionario","=",$id_funcionario)->deleteByFilter()){
            mensagem::setSucesso("Funcionario Desvinculado Com Sucesso");
            return true;
        }

        mensagem::setErro("Erro ao Desvincular Funcionario");
        return false;
    }

    /**
     * Desvincula um funcionario de todos as agendas 
     *      
     * @param int $id_funcionario O ID do funcionário.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function detachAllAgendaFuncionario(int $id_funcionario):bool
    {
        $this = new agendaFuncionario;

        if($this->addFilter("id_funcionario","=",$id_funcionario)->deleteByFilter()){
            mensagem::setSucesso("Funcionario Desvinculado Com Sucesso");
            return true;
        }

        mensagem::setErro("Erro ao Desvincular Funcionario");
        return false;
    }

    /**
     * Insere ou atualiza um funcionário.
     * 
     * @param int $id_usuario O ID do usuário associado ao funcionário.
     * @param string $nome O nome do funcionário.
     * @param string $cpf_cnpj O CPF/CNPJ do funcionário.
     * @param string $email O email do funcionário.
     * @param string $telefone O telefone do funcionário.
     * @param string $hora_ini O horário de início de trabalho.
     * @param string $hora_fim O horário de término de trabalho.
     * @param string $hora_almoco_ini O horário de início do almoço.
     * @param string $hora_almoco_fim O horário de término do almoço.
     * @param int    $espacamento_agenda espaçamento em minutos do agendamento
     * @param string $dias Os dias de trabalho do funcionário.
     * @param string $id O ID do funcionário (opcional).
     * @return int|bool Retorna o ID do funcionário se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function set(int $id_usuario,string $nome,string $cpf_cnpj,string $email,string $telefone,string $hora_ini,string $hora_fim,string $hora_almoco_ini,string $hora_almoco_fim,int $espacamento_agenda, string $dias,int $id = null):int|bool
    {
        $values = new funcionario;

        $mensagens = [];

        if($id && !$this->id = self::get($id)->id){
            $mensagens[] = "Funcionario não encontrada";
        }

        if(!$id_usuario || !$this->id_usuario = usuarioModel::get($id_usuario)->id){
            $mensagens[] = "Usuario não encontrada";
        }

        if(!($this->nome = htmlspecialchars(ucwords(strtolower(trim($nome)))))){
            $mensagens[] = "Nome deve ser informado";
        }

        if(!($this->cpf_cnpj = functions::onlynumber($cpf_cnpj))){
            $mensagens[] = "CPF/CNPJ deve ser informado";
        }

        if(!(functions::validaCpfCnpj($cpf_cnpj))){
            $mensagens[] = "CPF/CNPJ invalido";
        }

        if(!($this->email = htmlspecialchars($email))){
            $mensagens[] = "Email não informado";
        }

        if(!functions::validaEmail($email)){
            $mensagens[] = "Email é invalido";
        }

        if(!$this->telefone = functions::onlynumber($telefone)){
            $mensagens[] = "Telefone deve ser informado";
        }

        if(!functions::validaTelefone($telefone)){
            $mensagens[] = "Telefone invalido";
        }

        if(!$this->hora_ini = functions::formatTime($hora_ini)){
            $mensagens[] = "Horario inicial deve ser informado";
        }

        if(!functions::validaHorario($this->hora_ini)){
            $mensagens[] = "Horario inicial invalido";
        }

        if(!$this->hora_fim = functions::formatTime($hora_fim)){
            $mensagens[] = "Horario final deve ser informado";
        }

        if(!functions::validaHorario($this->hora_fim)){
            $mensagens[] = "Horario final invalido";
        }

        if(!$this->hora_almoco_ini = functions::formatTime($hora_almoco_ini)){
            $mensagens[] = "Horario inicial de almoço deve ser informado";
        }

        if(!functions::validaHorario($this->hora_almoco_ini)){
            $mensagens[] = "Horario inicial de almoço invalido";
        }

        if(!$this->hora_almoco_fim = functions::formatTime($hora_almoco_fim)){
            $mensagens[] = "Horario final de almoço deve ser informado";
        }

        if(!functions::validaHorario($this->hora_almoco_fim)){
            $mensagens[] = "Horario final de almoço invalido";
        }

        $this->espacamento_agenda = $espacamento_agenda;
        if($this->espacamento_agenda < 0 || $this->espacamento_agenda > 480){
            $mensagens[] = "Espaçamento entre os slots da agenda deve ser entre 0 e 480 minutos";
        }

        if(!($this->dias = $dias)){
            $mensagens[] = "Os dias devem ser informados";
        }

        if(!functions::validarDiasSemana($this->dias)){
            $mensagens[] = "Um ou mais dias estão no formato invalido";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        $retorno = $this->store();
        
        if ($retorno == true){
            mensagem::setSucesso("Funcionario salvo com sucesso");
            return $this->id;
        }
        
        return False;
    }

    /**
     * Associa um funcionário a uma agenda.
     * 
     * @param int $id_funcionario O ID do funcionário.
     * @param int $id_agenda O ID da agenda.
     * @return int|bool Retorna o ID da associação se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function setAgendaFuncionario(int $id_funcionario,int $id_agenda):bool
    {
        $values = new agendaFuncionario;

        $result = $this->addFilter("id_agenda","=",$id_agenda)
                    ->addFilter("id_funcionario","=",$id_funcionario)
                    ->selectAll();

        if (!$result){
        
            $this->id_agenda = $id_agenda;
            $this->id_funcionario = $id_funcionario;

            if ($values)
                $retorno = $this->storeMutiPrimary($values);

            if ($retorno == true)
                return true;
            else 
                return False;
            
        }
        return True;
    }

    /**
     * Associa um funcionário a um grupo de funcionários.
     * 
     * @param int $id_funcionario O ID do funcionário.
     * @param int $id_grupo_funcionario O ID do grupo de funcionários.
     * @return int|bool Retorna o ID da associação se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function setFuncionarioGrupoFuncionario(int $id_funcionario,int $id_grupo_funcionario):bool
    {
        $values = new funcionarioGrupoFuncionario;

        $result = $this->addFilter("id_grupo_funcionario","=",$id_grupo_funcionario)
                    ->addFilter("id_funcionario","=",$id_funcionario)
                    ->selectAll();

        if (!$result){
            

            $this->id_grupo_funcionario = $id_grupo_funcionario;
            $this->id_funcionario = $id_funcionario;

            if ($values)
                $retorno = $this->storeMutiPrimary($values);

            if ($retorno == true){
                return true;
            }
            else {
                return False;
            }
        }
        return True;
    }

    /**
     * Exclui todas as associações de um serviço com uma funcionarios.
     * 
     * @param int $id_funcionario O ID do funcionario.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function deleteAllServicoFuncionario(int $id_funcionario):bool
    {
        $this = new servicoFuncionario;

        return $this->addFilter("servico_funcionario.id_funcionario","=",$id_funcionario)->deleteByFilter();
    }
    
    /**
     * Exclui um funcionário pelo ID.
     * 
     * @param int $id O ID do funcionário a ser excluído.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
     */
    public static function delete(int $id):bool
    {
        try {
            transactionManeger::init();
            transactionManeger::beginTransaction();

            self::deleteAllServicoFuncionario($id);
            self::detachAllAgendaFuncionario($id);
            grupo(new funcionario)->detachAllFuncionario($id);

            if((new funcionario)->delete($id)){
                mensagem::setSucesso("funcionario deletado com sucesso");
                transactionManeger::commit();
                return true;
            }

            mensagem::setErro("Erro ao deletar funcionario");
            transactionManeger::rollBack();
            return false;
        }catch (\exception $e){
            mensagem::setErro("Erro ao deletar funcionario");
            transactionManeger::rollBack();
            return false;
        }
    }
}