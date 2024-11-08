<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use diogodg\neoorm\connection;
use app\helpers\functions;
use app\helpers\mensagem;

final class funcionario extends model {
    public const table = "funcionario";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de funcionarios"))
                ->addColumn((new column("id","INT"))->isPrimary()->isNotNull()->setComment("ID do funcionario"))
                ->addColumn((new column("id_usuario","INT"))->isNotNull()->isForeingKey(usuario::table())->setComment("ID da tabela usuario"))
                ->addColumn((new column("nome", "VARCHAR", 200))->isNotNull()->setComment("Nome do funcionario"))
                ->addColumn((new column("cpf_cnpj", "VARCHAR", 14))->isNotNull()->setComment("CPF ou CNPJ do funcionario"))
                ->addColumn((new column("email", "VARCHAR", 200))->isNotNull()->setComment("Email do funcionario"))
                ->addColumn((new column("telefone", "VARCHAR", 13))->isNotNull()->setComment("Telefone do funcionario"))
                ->addColumn((new column("hora_ini", "TIME"))->isNotNull()->setComment("Horario inicial de atendimento"))
                ->addColumn((new column("hora_fim", "TIME"))->isNotNull()->setComment("Horario final de atendimento"))
                ->addColumn((new column("hora_almoco_ini", "TIME"))->isNotNull()->setComment("Horario inicial do almoco"))
                ->addColumn((new column("hora_almoco_fim", "TIME"))->isNotNull()->setComment("Horario final do almoco"))
                ->addColumn((new column("dias", "VARCHAR", 27))->isNotNull()->setComment("Dias de trabalho: dom,seg,ter,qua,qui,sex,sab"))
                ->addColumn((new column("espacamento_agenda", "INT"))->isNotNull()->setComment("Tamanho do Slot para selecionar na agenda em minutos"));
    }

    public function getByFilter(?int $id_empresa = null,?string $nome = null,?int $id_agenda = null,?int $id_grupo_funcionarios = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array
    {
        $this->addJoin(funcionarioGrupoFuncionario::table,funcionario::table.".id",funcionarioGrupoFuncionario::table.".id_funcionario","LEFT")
            ->addJoin(agendaFuncionario::table,agendaFuncionario::table.".id_funcionario",funcionario::table.".id","LEFT")  
            ->addJoin(usuario::table,usuario::table.".id",funcionario::table.".id_usuario");
         
        if($id_empresa)
            $this->addFilter(usuario::table.".id_empresa","=",$id_empresa);

        if($id_grupo_funcionarios)
            $this->addFilter(funcionarioGrupoFuncionario::table.".id_grupo_funcionario","=",$id_grupo_funcionarios);

        if($id_agenda)
            $this->addFilter(agenda::table."_funcionario.id_agenda","=",$id_agenda);

        if($nome)
            $this->addFilter(funcionario::table.".nome","LIKE","%".$nome."%");

        $this->addGroup("funcionario.id");

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
                    
        return $this->selectColumns(funcionario::table.".id,funcionario.cpf_cnpj,funcionario.nome,funcionario.email,funcionario.telefone,hora_ini,hora_fim,hora_almoco_ini,hora_almoco_fim,dias");
    }

    public function prepareData(array $funcionarios){
        $funcionarioFinal = [];
        if ($funcionarios){
            foreach ($funcionarios as $funcionario){

                if(is_subclass_of($funcionario,"diogodg\neoorm\db")){
                    $funcionario = $funcionario->getArrayData();
                }

                if ($funcionario["cpf_cnpj"]){
                    $funcionario["cpf_cnpj"] = functions::formatCnpjCpf($funcionario["cpf_cnpj"]);
                }
                if ($funcionario["telefone"]){
                    $funcionario["telefone"] = functions::formatPhone($funcionario["telefone"]);
                }
                if ($funcionario["dias"]){
                    $funcionario["dias"] = functions::formatDias($funcionario["dias"]);
                }
                $funcionarioFinal[] = $funcionario;
            }
        }
        
        return $funcionarioFinal;
    }

    public function getByAgenda(int $id_agenda):array
    {
        return $this->addJoin(agendaFuncionario::table,agenda::table.".id",agendaFuncionario::table.".id_agenda")
                ->addJoin(self::table,self::table.".id",agendaFuncionario::table.".id_funcionario")
                ->addFilter(agendaFuncionario::table.".id_agenda","=",$id_agenda)
                ->selectColumns(funcionario::table.".id",funcionario::table.".nome",agenda::table.".nome as age_nome",funcionario::table.".cpf_cnpj",funcionario::table.".email",funcionario::table.".telefone","hora_ini","hora_fim","dias");
    }

    public function getByEmpresa(int $id_empresa):array
    {
        return $this->addJoin(usuario::table,usuario::table.".id",funcionario::table.".id_usuario")
                ->addFilter(usuario::table.".id_empresa","=",$id_empresa)
                ->selectColumns(funcionario::table.".id",funcionario::table.".nome",funcionario::table.".cpf_cnpj",funcionario::table.".email",funcionario::table.".telefone","hora_ini","hora_fim","dias");
    }

    public function getByUsuario(int $id_usuario):array
    {
        return $this->addJoin(agendamento::table,agendamento::table.".id_funcionario",funcionario::table.".id")
                ->addGroup(funcionario::table.".id")
                ->selectColumns(funcionario::table.".id",funcionario::table.".nome",funcionario::table.".cpf_cnpj",funcionario::table.".email",funcionario::table.".telefone","hora_ini","hora_fim","dias");
    }

    public function set(bool $valid_fk = true):funcionario|null
    {
        $mensagens = [];

        if($this->id && !self::get($this->id)->id){
            $mensagens[] = "Funcionario não encontrada";
        }

        if($valid_fk && (!$this->id_usuario || !(new usuario)->get($this->id_usuario)->id)){
            $mensagens[] = "Usuario não encontrada";
        }

        if(!($this->nome = htmlspecialchars(ucwords(strtolower(trim($this->nome?:"")))))){
            $mensagens[] = "Nome deve ser informado";
        }

        if(!($this->cpf_cnpj = functions::onlynumber($this->cpf_cnpj))){
            $mensagens[] = "CPF/CNPJ deve ser informado";
        }

        if(!(functions::validaCpfCnpj($this->cpf_cnpj))){
            $mensagens[] = "CPF/CNPJ invalido";
        }

        if(!($this->email = htmlspecialchars(trim($this->email)))){
            $mensagens[] = "Email não informado";
        }

        if(!functions::validaEmail($this->email)){
            $mensagens[] = "Email é invalido";
        }

        if(!($this->telefone = functions::onlynumber($this->telefone)))
        {
            $mensagens[] = "Telefone deve ser informado";
        }

        if(!functions::validaTelefone($this->telefone)){
            $mensagens[] = "Telefone invalido";
        }

        if(!($this->hora_ini = functions::formatTime($this->hora_ini)))
        {
            $mensagens[] = "Horario inicial deve ser informado";
        }

        if(!(functions::validaHorario($this->hora_ini)))
        {
            $mensagens[] = "Horario inicial invalido";
        }

        if(!($this->hora_fim = functions::formatTime($this->hora_fim)))
        {
            $mensagens[] = "Horario final deve ser informado";
        }

        if(!functions::validaHorario($this->hora_fim)){
            $mensagens[] = "Horario final invalido";
        }

        if(!($this->hora_almoco_ini = functions::formatTime($this->hora_almoco_ini)))
        {
            $mensagens[] = "Horario inicial de almoço deve ser informado";
        }

        if(!functions::validaHorario($this->hora_almoco_ini)){
            $mensagens[] = "Horario inicial de almoço invalido";
        }

        if(!$this->hora_almoco_fim = functions::formatTime($this->hora_almoco_fim))
        {
            $mensagens[] = "Horario final de almoço deve ser informado";
        }

        if(!functions::validaHorario($this->hora_almoco_fim)){
            $mensagens[] = "Horario final de almoço invalido";
        }

        if($this->espacamento_agenda < 0 || $this->espacamento_agenda > 480){
            $mensagens[] = "Espaçamento entre os slots da agenda deve ser entre 0 e 480 minutos";
        }

        if(!($this->dias)){
            $mensagens[] = "Os dias devem ser informados";
        }

        if(!functions::validarDiasSemana($this->dias)){
            $mensagens[] = "Um ou mais dias estão no formato invalido";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($this->store()){
            mensagem::setSucesso("Funcionario salvo com sucesso");
            return $this;
        }
        
        return null;
    }

    public function remove():bool
    {
        try {
            
            connection::beginTransaction();

            $servicoFuncionario = (new servicoFuncionario);
            $servicoFuncionario->id_funcionario = $this->id;
            $servicoFuncionario->removeByFuncionario();

            $agendaFuncionario = (new agendaFuncionario);
            $agendaFuncionario->id_funcionario = $this->id;
            $agendaFuncionario->removeByFuncionario();

            $funcionarioGrupoFuncionario = (new funcionarioGrupoFuncionario);
            $funcionarioGrupoFuncionario->id_funcionario = $this->id;
            $funcionarioGrupoFuncionario->removeByFuncionario();
           
            if((new funcionario)->delete($this->id)){
                mensagem::setSucesso(funcionario::table." deletado com sucesso");
                connection::commit();
                return true;
            }

            mensagem::setErro("Erro ao deletar funcionario");
            connection::rollBack();
            return false;
        }catch (\exception $e){
            mensagem::setErro("Erro ao deletar funcionario");
            connection::rollBack();
            return false;
        }
    }
}