<?php 
namespace app\models\main;
use app\models\status;
use app\helpers\mensagem;

/**
 * Classe statusModel
 * 
 * Esta classe fornece métodos para interagir com os dados de status
 * 
 * @package app\models\main
 */
class statusModel{

    /**
     * Obtém um registro da agenda com base em um valor e coluna especificados.
     * 
     * @param string $value O valor para buscar.
     * @param string $column A coluna onde buscar o valor.
     * @param int $limit O número máximo de registros a serem retornados.
     * @return object|array Retorna os dados da agenda ou null se não encontrado.
    */
    public static function get($value = "",string $column = "id",int $limit = 1):array|object
    {
        return (new status)->get($value,$column,$limit);
    }


    /**
     * Obtém um registro da agenda com base em um valor e coluna especificados.
     * 
     * @return object|array Retorna os dados da agenda ou null se não encontrado.
    */
    public static function getAll():array|object
    {
        return (new status)->getAll();
    }

    /**
     * Obtém um registro da agenda com base em um valor e coluna especificados.
     * 
     * @return object|array Retorna os dados da agenda ou null se não encontrado.
    */
    public static function getUsuarioStatus():array|object
    {
        return (new status)->addFilter("id","IN",[1,4])->selectAll();
    }

    /**
     * Insere ou atualiza um registro na tabela de agenda.
     * 
     * @param string $nome O nome da agenda.
     * @param int|null $id O ID da status (opcional).
     * @return int|null Retorna o ID da agenda inserida ou atualizada se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function set(string $nome,int|null $id = null):int|bool
    {
        $values = new status;
        
        $this->id = $id;
        $this->nome = htmlspecialchars(trim($nome));

        $retorno = $this->store();
        
        if ($retorno == true){
            mensagem::setSucesso("Status salvo com sucesso");
            return $this->id;
        }
        else {
            return False;
        }
    }

    /**
     * Exclui um registro dos status.
     * 
     * @param int $id O ID da status a ser excluída.
     * @return bool Retorna true se a operação for bem-sucedida, caso contrário retorna false.
    */
    public static function delete(int $id):bool
    {
        return (new status)->delete($id);
    }

}