<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\login as ModelsLogin;
use app\view\layout\login;

final class loginController extends controller{

    public const headTitle = "Login";
    public const addHeader = false;

    public function index(array $parameters = []){

        $login = new login();
        $login->show();
    }

    public function action(array $parameters = []){

        $cpf_cnpj = $this->getValue('cpf_cnpj');
        $senha = $this->getValue('senha');
        
        $login = (new ModelsLogin)->login($cpf_cnpj,$senha);

        if ($login){
            $this->go("home");
        }else {
            $this->go("login");
        }
    }

    public function deslogar(array $parameters = []){

        (new ModelsLogin)->deslogar();

        $this->go("login");
    }
}