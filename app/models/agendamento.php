<?php
namespace app\models;

use app\db\abstract\model;
use app\db\migrations\table;
use app\db\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

final class agendamento extends model {
    public const table = agendamento::table;

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de agendamentos"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID agendamento"))
                ->addColumn((new column("id_agenda","INT"))->isNotNull()->isForeingKey(agenda::table())->setComment("ID da tabela agenda"))
                ->addColumn((new column("id_usuario","INT"))->isForeingKey(usuario::table())->setComment("ID da tabela usuario"))
                ->addColumn((new column("id_cliente","INT"))->isForeingKey(cliente::table())->setComment("ID da tabela cliente"))
                ->addColumn((new column("id_funcionario","INT"))->isForeingKey(funcionario::table())->setComment("ID da tabela funcionario"))
                ->addColumn((new column("titulo","VARCHAR",150))->isNotNull()->setComment("titulo do agendamento"))
                ->addColumn((new column("dt_ini","TIMESTAMP"))->isNotNull()->setComment("Data inicial de agendamento"))
                ->addColumn((new column("dt_fim","TIMESTAMP"))->isNotNull()->setComment("Data final de agendamento"))
                ->addColumn((new column("cor","VARCHAR",7))->setDefaut("#4267b2")->isNotNull()->setComment("Cor do agendamento"))
                ->addColumn((new column("total","DECIMAL","10,2"))->isNotNull()->setComment("Total do agendamento"))
                ->addColumn((new column("id_status","INT"))->isForeingKey(status::table())->isNotNull()->setComment("id do Status do agendamento"))
                ->addColumn((new column("obs","VARCHAR",400))->setComment("Observações do agendamento"))
                ->addIndex("getEventsbyFuncionario",["dt_ini","dt_fim","id_agenda","id_funcionario"]);
    }

    public function getEventsbyFilter(?string $dt_inicio = null,?string $dt_fim = null,?int $id_agenda = null,?int $id_funcionario = null,?int $isnotstatus = 4):array
    {
        if($dt_inicio)
            $this->addFilter("dt_ini",">=",functions::dateTimeBd($dt_inicio));
        if($dt_fim)
            $this->addFilter("dt_fim","<=",functions::dateTimeBd($dt_fim));
        if($id_agenda)
            $this->addFilter("id_agenda","=",intval($id_agenda));
        if($id_funcionario)
            $this->addFilter("id_funcionario ","=",intval($id_funcionario));
        if($isnotstatus)
            $this->addFilter("id_status","!=",intval($isnotstatus));
                      
        $results = $this->selectAll();

        $return = [];

        $user = (new login)->getLogged();

        if ($results){
            foreach ($results as $result){
                if ($user->tipo_usuario != 3){
                    $return[] = [
                        'id' => functions::encrypt($result->id),
                        'title' => $result->titulo,
                        'color' => $result->cor,
                        'start' => $result->dt_ini,
                        'end' => $result->dt_fim,
                    ];
                }
                elseif ($user->id == $result->id_usuario){
                    $return[] = [
                        'id' => functions::encrypt($result->id),
                        'title' => $result->titulo,
                        'color' => $result->cor,
                        'start' => $result->dt_ini,
                        'end' => $result->dt_fim,
                    ];
                }
                else{
                    $return[] = [
                        'title' => "Outro agendamento",
                        'color' => "#9099ad",
                        'start' => $result->dt_ini,
                        'end' => $result->dt_fim,
                    ];
                }
            }
        }
        return $return;
    }

    public function getAgendamentosByfilter($id_empresa,?int $id_usuario = null,?string $dt_ini = null,?string $dt_fim = null,bool $onlyActive = false,?int $id_agenda = null,?int $id_funcionario = null,?int $limit = null,?int $offset = null):array
    {
        $this->addJoin(usuario::table,usuario::table.".id",agendamento::table.".id_usuario","LEFT")
            ->addJoin(agenda::table."",agenda::table.".id",agendamento::table.".id_agenda")
            ->addJoin("cliente","cliente.id",agendamento::table.".id_cliente","LEFT")
            ->addJoin(funcionario::table."",funcionario::table.".id",agendamento::table.".id_funcionario")
            ->addFilter(agenda::table.".id_empresa","=",$id_empresa);

        if($id_usuario){
            $this->addFilter(usuario::table.".id","=",$id_usuario);
        }
                  
        if($dt_ini && $dt_fim){
            $this->addFilter(agendamento::table.".dt_fim",">=",functions::dateTimeBd($dt_ini));
            $this->addFilter(agendamento::table.".dt_fim","<=",functions::dateTimeBd($dt_fim));
        }

        if($onlyActive){
            $this->addFilter(agendamento::table.".id_status","IN",[1,2]);
        }

        if($id_funcionario){
            $this->addFilter(agendamento::table.".id_funcionario","=",$id_funcionario);
        }

        if($id_agenda){
            $this->addFilter(agendamento::table.".id_agenda","=",$id_agenda);
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

        return $this->selectColumns(agendamento::table.".id",usuario::table.".cpf_cnpj","cliente.nome as cli_nome",usuario::table.".nome as usu_nome",usuario::table.".email",usuario::table.".telefone",agenda::table.".nome as age_nome",funcionario::table.".nome as fun_nome",agendamento::table.".id_status","dt_ini","dt_fim");
    }

    public static function prepareList(array $agendamentos)
    {
        $statuses = (new Status)->getAll();

        $statusHashMap = [];
        foreach ($statuses as $status)
        {
            $statusHashMap[$status->id] = $status->nome;
        }

        $agendamentosFinal = [];
        foreach ($agendamentos as $agendamento){
            if ($agendamento->cpf_cnpj){
                $agendamento->cpf_cnpj = functions::formatCnpjCpf($agendamento->cpf_cnpj);
            }
            if ($agendamento->telefone){
                $agendamento->telefone = functions::formatPhone($agendamento->telefone);
            }
            if (!$agendamento->usu_nome){
                $agendamento->nome = $agendamento->cli_nome;
            }
            if (!$agendamento->cli_nome){
                $agendamento->nome = $agendamento->usu_nome;
            }
            if ($agendamento->dt_ini){
                $agendamento->dt_ini = functions::dateTimeBr($agendamento->dt_ini);
            }
            if ($agendamento->dt_fim){
                $agendamento->dt_fim = functions::dateTimeBr($agendamento->dt_fim);
            }
            if ($agendamento->id_status){
                $agendamento->status = $statusHashMap[$agendamento->id_status];
            }
            $agendamentosFinal[] = $agendamento;
        }

        return $agendamentosFinal;
    }

    public function setTotal(int $id):agendamento|null
    {
        $agendamento = $this->get($id);

        $mensagens = [];

        if(!($agendamento->id)){
            $mensagens[] = agendamento::table." não encontrada";
        }

        $agendamentosItens = (new agendamentoItem)->getItens($agendamento->id);

        $total = 0;
        foreach ($agendamentosItens as $agendamentosIten){
            $total += $agendamentosIten->total_item;
        }

        if(($agendamento->total = $total) < 0){
            $mensagens[] = "Total deve ser maior que 0";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        if ($agendamento->store()){
            mensagem::setSucesso(agendamento::table." salvo com sucesso");
            return $agendamento;
        }

        return False;
    }

    public function cancel(int $id):agendamento|null
    {
        $agendamento = $this->get($id);

        $mensagens = [];

        if(!$agendamento->id){
            $mensagens[] = agendamento::table." não encontrada";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return false;
        }

        $agendamento->id_status = 4;

        if ($agendamento->store()){
            mensagem::setSucesso(agendamento::table." salvo com sucesso");
            return $agendamento;
        }
         
        return null;
    }

    public function set():self|null
    {
        $mensagens = [];

        if($this->id  && (new self)->get($this->id)->id){
            $mensagens[] = agendamento::table." não encontrada";
        }

        if(!$this->id_agenda || !(new agenda)->get($this->id_agenda)->id){
            $mensagens[] = agenda::table." não encontrada";
        }

        if(!$this->id_cliente && !$this->id_usuario){
            $mensagens[] = "Obrigatorio Informar Cliente ou Usuario";
        }
        else{
            if($this->id_usuario && !(new usuario)->get($this->id_usuario)->id){
                $mensagens[] = usuario::table." não encontrado";
            }

            if($this->id_cliente && !(new cliente)->get($this->id_cliente)->id){
                $mensagens[] = "Cliente não cadastrado";
            }
        }

        if(!$this->id_funcionario || !(new funcionario)->get($this->id_funcionario)->id){
            $mensagens[] = funcionario::table." não cadastrado";
        }

        if(!$this->titulo = htmlspecialchars(ucwords(strtolower(trim($this->titulo))))){
            $mensagens[] = "Titulo deve ser informado";
        }

        if(!$this->dt_ini = functions::dateTimeBd($this->dt_ini)){
            $mensagens[] = "Data inicial invalida";
        }

        if(!$this->dt_fim = functions::dateTimeBd($this->dt_fim)){
            $mensagens[] = "Data final invalida";
        }

        if(!$this->cor = functions::validaCor($this->cor?:"#4267b2")){
            $mensagens[] = "Cor invalida";
        }

        if(($this->Total) < 0){
            $mensagens[] = "Total deve ser maior que 0";
        }

        if(!($this->id_status) && !(new status)->get($this->id_status)){
            $mensagens[] = "Status informado invalido";
        }

        if($this->id_usuario && !$this->id){
            if(($empresa = (new empresa)->getByAgenda($this->id_agenda))){

                $dt_ini = (new \DateTimeImmutable($this->dt_ini))->format("Y-m-d");
                $primeiroDiaMes = (new \DateTimeImmutable($dt_ini))->format("Y-m")."-01";
                $ultimoDiaMes = (new \DateTimeImmutable($dt_ini))->modify('last day of this month')->format("Y-m-d");
                $primeiroDiaSemana = (new \DateTimeImmutable($dt_ini))->modify('monday this week')->format("Y-m-d");
                $ultimoDiaSemana = (new \DateTimeImmutable($dt_ini))->modify('sunday this week')->format("Y-m-d");

                $agendamentos = (new self)->getAgendamentosByFilter($empresa->id,$this->id_usuario,$primeiroDiaMes,$ultimoDiaMes,true);

                $dia = 0;
                $semana = 0;
                $mes = 0;
                foreach ($agendamentos as $agendamento){

                    $agendamento_dt_ini = (new \DateTimeImmutable($agendamento->dt_ini))->format("Y-m-d");
                    
                    if($agendamento_dt_ini == $dt_ini){
                        $dia++;
                    }
                    if($agendamento_dt_ini >= $primeiroDiaSemana && $agendamento_dt_ini <= $ultimoDiaSemana){
                        $semana++;
                    }
                    if($agendamento_dt_ini >= $primeiroDiaMes && $agendamento_dt_ini <= $ultimoDiaMes){
                        $mes++;
                    }
                }

                if(intval($empresa->configuracoes->max_agendamento_dia) < $dia)
                    $mensagens[] = "Numero maximo de agendamentos para o dia de hoje atingindo";

                if(intval($empresa->configuracoes->max_agendamento_semana) < $semana)
                    $mensagens[] = "Numero maximo de agendamentos para o essa semana atingindo";

                if(intval($empresa->configuracoes->max_agendamento_mes) < $mes)
                    $mensagens[] = "Numero maximo de agendamentos para o esse mês atingindo";
            }
            else 
                $mensagens[] = "Nenhuma empresa vinculada a agenda informada";
           
        }   

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        $this->obs = htmlspecialchars(trim($this->obs));

        if ($this->store()){
            mensagem::setSucesso(agendamento::table." salvo com sucesso");
            return $this;
        }
            
        return null;
    }

    public function remove():bool
    {
        return $this->delete($this->id);
    }
}