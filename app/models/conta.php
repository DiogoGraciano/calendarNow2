<?php

namespace app\models;

use app\helpers\functions;
use app\helpers\mensagem;
use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

class conta extends model
{
    public const table = "conta";

    public function __construct() {
        parent::__construct(self::table,$this::class);
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de configurações"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID self"))
                ->addColumn((new column("nome","VARCHAR",100))->isNotNull()->setComment("Nome da Conta"))
                ->addColumn((new column("tipo","VARCHAR",1))->isNotNull()->setComment("Tipo de conta R = receber e P = pagar"))
                ->addColumn((new column("tipo_juros","VARCHAR",1))->isNotNull()->setComment("C = Composto e S = Simples"))
                ->addColumn((new column("dt_vencimento","TIMESTAMP"))->isNotNull()->setComment("Data de Vencimento da conta"))
                ->addColumn((new column("dt_pagamento","TIMESTAMP"))->setComment("Data de Pagamento da conta"))
                ->addColumn((new column("juros","DECIMAL","10,6"))->setComment("Juros"))
                ->addColumn((new column("valor","DECIMAL","10,2"))->isNotNull()->setComment("Valor da conta"))
                ->addColumn((new column("id_empresa","INT"))->isNotNull()->isForeingKey(empresa::table())->setComment("ID da empresa"))
                ->addColumn((new column("id_dre","INT"))->isNotNull()->isForeingKey(dre::table())->setComment("ID da dre"))
                ->addColumn((new column("status","VARCHAR",1))->isNotNull()->setComment("P = Pago, A = Pago Com Atraso, C = Cancelado, I = A Pagar"));
    }

    public function getByFilter(?int $id_empresa = null,?string $nome = null,?string $dt_vencimento = null,?string $dt_pagamento = null,string $status = null,?int $limit = null,?int $offset = null):array
    {
        if($id_empresa){
            $this->addFilter("id_empresa","=",$id_empresa);
        }

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }
                  
        if($dt_vencimento){
            $this->addFilter("dt_vencimento","=",functions::dateTimeBd($dt_vencimento));
        }

        if($dt_pagamento){
            $this->addFilter("dt_pagamento","=",functions::dateTimeBd($dt_pagamento));
        }

        if($status){
            $this->addFilter("status","=",$status);
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

        return $this->selectAll();
    }

    public static function prepareList(array $contas):array
    {
        $statusHashMap = ["P" => "Pago", "A" => "Pago com Atraso","C" => "Cancelado","I" => "A Pagar"];
        $statusHashMapReceber = ["P" => "Recebido", "A" => "Recebido com Atraso","C" => "Cancelado","I" => "A Receber"];

        $contasFinal = [];

        $contasFinal["total_pago"] = 0;
        $contasFinal["total_pago_atrasado"] = 0;
        $contasFinal["total_a_pagar"] = 0;

        foreach ($contas as $conta){

            if(is_subclass_of($conta,"diogodg\\neoorm\db")){
                $conta = $conta->getArrayData();
            }

            if ($contas["dt_vencimento"]){
                $contas["dt_vencimento"] = functions::dateTimeBr($contas["dt_vencimento"]);
            }

            if ($contas["dt_pagamento"]){
                $contas["dt_pagamento"] = functions::dateTimeBr($contas["dt_pagamento"]);
            }

            if($conta["tipo"] = "P"){
                if($conta["status"] == "P")
                    $contasFinal["total_pago"] += $contas["valor"];
                if($conta["status"] == "A"){
                    $contasFinal["total_pago_atrasado"] += $contas["valor"];
                    $contasFinal["total_pago"] += $contas["valor"];
                }
                if($conta["status"] == "I")
                    $contasFinal["total_a_pagar"] += $contas["valor"];
            }
            else{
                if($conta["status"] == "P")
                    $contasFinal["total_recebido"] += $contas["valor"];
                if($conta["status"] == "A"){
                    $contasFinal["total_recebido_atrasado"] += $contas["valor"];
                    $contasFinal["total_recebido"] += $contas["valor"];
                }
                if($conta["status"] == "I")
                    $contasFinal["total_a_receber"] += $contas["valor"];
            }
            

            if ($conta["status"] && $conta["tipo"] = "P"){
                $conta["status"] = $statusHashMap[$conta["status"]];
            }

            if($conta["status"] && $conta["tipo"] = "R"){
                $conta["status"] = $statusHashMapReceber[$conta["status"]];
            }

            if ($contasFinal["valor"]){
                $contasFinal["valor"] = functions::formatCurrency($contasFinal["valor"]);
            }

            $contasFinal[] = $conta;
        }

        return $contasFinal;
    }

    public function set():self|null
    {
        $mensagens = [];

        if(!$this->nome = htmlspecialchars(trim($this->nome))){
            $mensagens[] = "Nome invalido";
        }

        if($this->tipo != "R" || $this->tipo != "P"){
            $mensagens[] = "Tipo invalido";
        }

        if($this->tipo_juros != "C" || $this->tipo != "S"){
            $mensagens[] = "Tipo de Juros invalido";
        }

        if(!$this->dt_vencimento = functions::dateTimeBd($this->dt_vencimento)){
            $mensagens[] = "Data de vencimento invalida";
        }

        if(!$this->dt_pagamento = functions::dateTimeBd($this->dt_pagamento)){
            $mensagens[] = "Data de pagamento invalida";
        }

        if($this->juros && $this->juros < 0){
            $mensagens[] = "Juros invalido";
        }

        if($this->valor < 0){
            $mensagens[] = "Valor invalido";
        }

        if($this->status != "P" || $this->tipo != "A" || $this->tipo != "C" || $this->tipo != "I"){
            $mensagens[] = "Status invalido";
        }

        if($this->dre && !((new dre)->get($this->dre)->id))
            $mensagens[] = "Dre não encontrada"; 

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if($this->status == "P" && strtotime($this->dt_pagamento) > strtotime($this->dt_vencimento)){
            $this->status == "A";
        }

        if ($this->store()){
            mensagem::setSucesso("Conta salva com sucesso");
            return $this;
        }
            
        return null;
    }
}
