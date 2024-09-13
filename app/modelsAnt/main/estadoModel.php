<?php
namespace app\models\main;

use app\models\estado;

/**
 * Classe estadoModel
 * 
 * Esta classe fornece métodos para interagir com os dados de estados.
 * Ela utiliza a classe estado para realizar operações de consulta no banco de dados.
 * 
 * @package app\models\main
 */
class estadoModel{

    /**
     * Obtém um estado pelo ID.
     * 
     * @param string $id O ID do estado a ser buscado.
     * @return object Retorna os dados do estado ou null se não encontrado.
     */
    public static function get(null|string|int $value,string $column = "id",int $limit = 1):object|array
    {
        return (new estado)->get($value,$column,$limit);
    }

    /**
     * Obtém um estado pela UF.
     * 
     * @param string $uf A UF (Unidade Federativa) do estado a ser buscado.
     * @return array Retorna um array com os dados do estado ou um array vazio se não encontrado.
     */
    public static function getByUf(string $uf):array
    {
        $this = new estado;
        
        $estado = $this->addFilter("uf", "=", $uf)->selectAll();

        return $estado;
    }
}
