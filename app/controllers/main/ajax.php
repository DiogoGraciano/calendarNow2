<?php
namespace app\controllers\main;
use app\helpers\functions;
use app\controllers\abstract\controller;
use app\helpers\integracaoWs;
use app\models\cidade;
use app\models\estado;
use app\models\usuario;
use Exception;

class ajax extends controller{

    public const addHeader = false;

    public const addFooter = false;

    public const addHead = false;
    
    public const permitAccess = true;

    public function index(){
        try{
            $method = $this->getValue("method");
            $parameters = $this->getValue("parameters");
   
            if ($method && method_exists($this,$method)){
                $this->$method($parameters); 
            }
            else{
                $retorno = ["sucesso" => false,"retorno" => "Metodo ({$method}) não Encontrada"];
                echo json_encode($retorno);
            }
        }catch(Exception $e){
            $retorno = ["sucesso" => false,"retorno" => "Erro ao realizar requisição"];
            echo json_encode($retorno);
        }
    }

    private function getCidadeOption(int $id_estado){
        echo json_encode(["sucesso" => true,"retorno" => (new cidade)->getByEstado($id_estado)]);
    }
    
    private function getEmpresa(int $cnpj){
        $integracao = new integracaoWs;
        $retorno = $integracao->getEmpresa($cnpj);

        if ($retorno && is_object($retorno)){
            $retorno = ["sucesso" => true,"retorno" => $retorno];
        }
        else{
            $retorno = ["sucesso" => false,"retorno" => $retorno];
        }

        echo json_encode($retorno);
    }
    
    private function getEndereco(int $cep):void
    {
        $integracao = new integracaoWs;
        $retorno = $integracao->getEndereco($cep);

        if ($retorno && is_object($retorno)){
            $cidade = "";
            $estado = (new estado)->getByUf($retorno->uf);

            if (array_key_exists(0,$estado)){
                $estado = $estado[0];
                $retorno->uf = $estado->id;
            }
            else{
                echo json_encode(["sucesso" => false, "retorno" => "Estado não encontrado"]);
                return;
            }

            if (isset($retorno->ibge)){
                $cidade = (new cidade)->getByIbge($retorno->ibge);
            }
            elseif(!$cidade){
                $cidade = (new cidade)->getByNomeIdUf($retorno->localidade,$estado->id);  
            }

            if (array_key_exists(0,$cidade)){
                $retorno->localidade = $cidade[0]->id;
            }
            else{
                echo json_encode(["sucesso" => false, "retorno" => "Cidade não encontrada"]);
                return;
            }

            $retorno = ["sucesso" => true, "retorno" => $retorno];
        }
        else{
            $retorno = ["sucesso" => false, "retorno" => $retorno];
        }

        echo json_encode($retorno);
    }

    private function existsCpfCnpj(int $cpf_cnpj){
        $cpf_cnpj = functions::onlynumber($cpf_cnpj);

        $usuario = (new usuario)->get($cpf_cnpj,"cpf_cnpj");

        if($usuario)
            $retorno = true;
        else 
            $retorno = false;

        $retorno = ["sucesso" => true, "retorno" => $retorno];
            
        echo json_encode($retorno); 
    }

    private function existsEmail(int $email){
        $usuario = (new usuario)->get($email,"email");

        if(array_key_exists(0,$usuario) && !array_key_exists(1,$usuario))
            $retorno = true;
        else 
            $retorno = false;

        $retorno = ["sucesso" => True,"retorno" => $retorno];
                 
        echo json_encode($retorno); 
    }
}

?>