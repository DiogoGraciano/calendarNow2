<?php
namespace app\models;

use app\helpers\mensagem;
use app\helpers\functions;
use core\session;

final class login{

    public static function login(string $usuario,string $senha):bool
    {
        if(functions::validaEmail($usuario))
            $login = (new usuario)->get($usuario,"email");
        else
            $login = (new usuario)->get(functions::onlynumber($usuario),"cpf_cnpj");

        if ($login->id && ($login->ativo || strtotime($login->criado) < strtotime('+1 days'))){
            if (password_verify($senha, $login->senha)){
                $login->senha = $senha;
                session::set("user",(object)$login->getArrayData());
                return true;
            }
        }

        mensagem::setErro("Usuário ou senha inválidos");
        return false;
    }

    public static function getLogged():object|bool
    {
        if($user = session::get("user"))
            return $user;

        return false;
    }

    public static function deslogar():bool
    {
        return session_destroy();
    }

}
