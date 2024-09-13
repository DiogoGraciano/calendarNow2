<?php
namespace app\models\main;

use app\helpers\mensagem;
use app\helpers\functions;
use app\models\main\usuarioModel;
use core\session;

/**
 * Classe loginModel
 * 
 * Esta classe fornece métodos para autenticação de usuários.
 * Ela utiliza a classe usuario para realizar operações de consulta no banco de dados e a classe session para gerenciar a sessão do usuário.
 * 
 * @package app\models\main
 */
class loginModel{

    /**
     * Realiza o login do usuário com base no CPF/CNPJ e senha fornecidos.
     * 
     * @param string $cpf_cnpj O CPF/CNPJ do usuário.
     * @param string $senha A senha do usuário.
     * @return bool Retorna true se o login for bem-sucedido, caso contrário retorna false.
     */
    public static function login($cpf_cnpj, $senha):bool
    {
        $login = usuarioModel::get(functions::onlynumber($cpf_cnpj),"cpf_cnpj");
        
        if ($login->id){
            if (password_verify($senha, $login->senha)){
                $login->senha = $senha;
                session::set("user",(object)$login->getArrayData());
                return true;
            }
        }

        mensagem::setErro("Usuário ou senha inválidos");
        return false;
    }

    /**
     * Desloga o usuário, destruindo a sessão.
     */
    public static function deslogar():bool
    {
        return session_destroy();
    }

}
