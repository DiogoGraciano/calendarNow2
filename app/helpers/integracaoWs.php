<?php 

namespace app\helpers;
use Exception;

class integracaoWs{

    public function getEmpresa(string $cnpj){
        return $this->getResult("https://receitaws.com.br/v1/cnpj/".$cnpj);
    }

    public function getEndereco(string|int $cep){

        $urls = array(
            'https://viacep.com.br/ws/'. $cep . '/json/',
            'http://republicavirtual.com.br/web_cep.php?cep='.$cep.'&formato=json'
        );
        
        foreach ($urls as $url){
            if(is_object($retornoWS = $this->getResult($url))){
                return $retornoWS;
            }
        }
    }

    private function getResult(string $urlCompleta){

        try{
            $response = file_get_contents($urlCompleta);
            
            if ($response && $response = json_decode($response))
                return $response;

            return false;
        }
        catch(Exception $e){
            return $e->getMessage();
        }

    }
}

?>
