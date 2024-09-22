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
            'q' => urlencode(strtolower("$rua $bairro $numero $cidade $estado")),
            'format' => 'geojson'
        );

        $query = http_build_query($query_array);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://nominatim.openstreetmap.org/search?'.$query,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        
        if($response)
            return json_decode($response);
        
        return ["error"=>curl_error($curl)];
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
