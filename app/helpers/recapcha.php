<?php 

namespace app\helpers;

use app\models\main\empresaModel;

class recapcha{

    public function siteverify(string $response){

        if(!$response){
            mensagem::setErro("Não foi possivel verificar o recapcha");
            return false;
        }

        $empresa = (new calendarNow)->get(1);

        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret' => $empresa->recaptcha_secret_key,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
    
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
    
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $responseKeys = json_decode($response);

        $score = null;
        if($empresa->recaptcha_minimal_score)
            $score = $empresa->recaptcha_minimal_score/10;

        if ($responseKeys && $responseKeys->success && $responseKeys->score > $score?:0.6) {
            return true;
        }

        mensagem::setErro("Recapcha invalido");
        return false;
    }
}

?>
