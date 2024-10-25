<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\login as ModelsLogin;
use app\models\usuario;
use app\view\layout\login as lyLogin;
use app\view\layout\elements;
use app\view\layout\form;
use app\helpers\email;

final class login extends controller{

    public const headTitle = "Login";

    public const permitAccess = true;
    
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
        (new usuario)->manutencao(tipo_usuario:3);
    }

    public function esqueci(){

        $elements = new elements;

        $form = new form("login/redefinir");
        $form->setElement($elements->titulo(1,"Esqueci Minha Senha"));
        $form->setElement($elements->input("email","E-mail","",true,type:"email"));
        $form->setElement($elements->input("cpf_cnpj","CPF/CNPJ","",true));
        $form->setButton($elements->button("Recuperar","recuperar"));
        $form->show();
    }

    public function redefinir(){
        $usuario = (new usuario)->getByFilter(email:$this->getValue("email"),cpf_cnpj:$this->getValue("cpf_cnpj"),limit:1);

        if(isset($usuario[0]) && $usuario = $usuario[0]){
            $email = new email;
            $email->addEmail($usuario->email);
            $email->send("Redefinir Senha","",true);
        }
    }

    public function deslogar(array $parameters = []){

        ModelsLogin::deslogar();

        $this->go("login");
    }
}