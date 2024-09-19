<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\login as ModelsLogin;
use app\view\layout\login as lyLogin;

final class login extends controller{

    public const headTitle = "Login";
    public const addHeader = false;

    public function index(array $parameters = []){

        $login = new lyLogin();
        $login->show();
    }

    public function action(array $parameters = []){

        $cpf_cnpj = $this->getValue('cpf_cnpj');
        $senha = $this->getValue('senha');
        
        $login = ModelsLogin::login($cpf_cnpj,$senha);

        if ($login){
            $this->go("home");
        }else {
            $this->go("login");
        }
    }

    public function empresa(){
        (new empresa)->manutencao();
    }

    public function usuario(){
        (new usuario)->manutencao();
    }

    public function deslogar(array $parameters = []){

        ModelsLogin::deslogar();

        $this->go("login");
    }
}