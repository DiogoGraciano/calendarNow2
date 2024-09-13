<?php
namespace app\controllers\abstract;

use app\models\api\usuarioApiModel;

/**
 * Classe abstrata controller é uma classe base para controladores.
 *
 * Esta classe fornece métodos utilitários comuns que podem ser usados por controladores específicos.
 */
abstract class apiController
{
    /**
     * Tipo de requisição HTTP (GET, POST, PUT, DELETE).
     * 
     * @var string
     */
    protected string $requestType;

    /**
     * Dados enviados na requisição.
     * 
     * @var mixed
     */
    protected mixed $data;

    /**
     * query da requisição.
     *
     * @var array
    */
    protected array $query;

    /**
     * usuario da requisição.
     *
     * @var object
    */
    protected object $user;

    public function __construct(){

        $user = usuarioApiModel::getLogged();

        if(!$user->id){
            throw new \exception("Usuario da Api não está logado");
        }

        $this->user = $user;
    }

    /**
     * Define os parâmetros com base nas colunas fornecidas e nos dados retornados pela API.
     *
     * @param array $columns Colunas a serem retornadas.
     * @param array $values Dados retornados pela API.
     * @return array Array contendo os valores das colunas especificadas.
     */
    protected function setParameters(array $columns, array $values)
    {
        $return = [];
        foreach ($columns as $column) {
            if (isset($values[$column])) {
                $return[] = $values[$column];
            }
            else{
                $return[] = null;
            }
        }
        return $return;
    }

    /**
     * Retorna o nome dos argumentos de um metodo de uma clase.
     *
     * @param string $className Nome da classe.
     * @param string $methodName Nome do Metodo.
     * @return array Array contendo os valores das colunas especificadas.
     */
    protected function getMethodsArgNames($className, $methodName) {
        $r = new \ReflectionMethod($className, $methodName);
        $parameters = $r->getParameters();

        $return = [];
        foreach ($parameters as $parameter){
            $return[] = $parameter->getName();
        }

        return $return;
    }

    protected function validRequest(array $validResquest = ["GET","POST","DELETE","PUT"]){
        foreach ($validResquest as $request)
        {
            if($request == $this->requestType){
                return true;
            }
        } 

        $this->sendResponse(['error' => "Modo da requisição inválido ou Json enviado inválido","result" => false],400);
    }

    protected function sendResponse(array $response,int $httpCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode($response);
        http_response_code($httpCode);
        die;
    }
}
