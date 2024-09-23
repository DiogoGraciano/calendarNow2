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

    public function getGeoCoding(string $rua = "",?string $bairro = "",?string $numero = "",?string $cidade = "",?string $estado = ""){

        $query_array = array (
            'street' => trim(urlencode(strtolower(functions::tirarAcentos("$rua $numero")))),
            'county' => urlencode(strtolower(functions::tirarAcentos($bairro))),
            'city' => urlencode(strtolower(functions::tirarAcentos($cidade))),
            'state' => urlencode(strtolower(functions::tirarAcentos($estado))),
            'format' => 'geojson'
        );

        $query = http_build_query($query_array);

        $options = [
            'http' => [
                'user_agent' => 'diogo.dg691@gmail.com',
            ],
        ];
        
        return $this->getResult('https://nominatim.openstreetmap.org/search?'.$query,$options);
    }

    private function getResult(string $urlCompleta,array $context = []){

        try{
            $context = stream_context_create($context);
            $response = file_get_contents($urlCompleta,false,$context);
            
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
