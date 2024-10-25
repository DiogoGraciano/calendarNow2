<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\login as ModelsLogin;
use app\models\usuario as ModelsUsuario;
use app\view\layout\login as lyLogin;
use app\view\layout\elements;
use app\view\layout\form;
use app\helpers\email;
use app\helpers\functions;
use app\helpers\mensagem;
use app\helpers\recapcha;
use app\view\layout\email as LayoutEmail;

final class login extends controller{

    public const headTitle = "Login";

    public const permitAccess = true;
    
    public const addHeader = false;

    public function index(array $parameters = []):void
    {
        $login = new lyLogin();
        $login->show();
    }

    public function action(array $parameters = []):void
    {
        $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-usuario-response"));

        if(!$recapcha){
            $this->go("login");
        }

        $cpf_cnpj = $this->getValue('cpf_cnpj');
        $senha = $this->getValue('senha');
        
        $login = ModelsLogin::login($cpf_cnpj,$senha);

        if ($login){
            $this->go("home");
        }else {
            $this->go("login");
        }
    }

    public function empresa(array $parameters = []):void
    {
        (new empresa)->manutencao();
    }

    public function usuario(array $parameters = []):void
    {
        (new usuario)->manutencao(tipo_usuario:3);
    }

    public function esqueci(array $parameters = []):void
    {
        $elements = new elements;

        $form = new form("login/sendEsqueci",hasRecapcha:true);
        $form->setElement($elements->titulo(1,"Esqueci minha senha"));
        $form->setElement($elements->input("email","E-mail","",true,type:"email"));
        $form->setElement($elements->input("cpf_cnpj","CPF/CNPJ","",true));
        $form->setButton($elements->button("Recuperar","recuperar"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url."login")."'"));
        $form->show();
    }

    public function sendEsqueci(array $parameters = []):void
    {
        $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-usuario-response"));

        if(!$recapcha){
            $this->esqueci();
        }

        $usuario = (new ModelsUsuario)->getByFilter(email:$this->getValue("email"),cpf_cnpj:$this->getValue("cpf_cnpj"),limit:1);

        if(isset($usuario[0]) && $usuario = $usuario[0]){
            $email = new email;
            $email->addEmail($usuario->email);

            $redefinir = new LayoutEmail();
            $redefinir->setEmailBtn("login/resetar/".functions::encrypt($usuario->id),"Resetar Senha","Clique no botão a baixo para resetar sua senha, caso não foi você que solicitou essa alteração, pode excluir esse email sem problemas.");

            $email->send("Redefinir Senha",$redefinir->parse(),true);
            mensagem::setMensagem("Verifique seu email para resetar sua senha");
            $this->index();
        }

        mensagem::setErro("Nenhum usuario encontrado, revise as campos informados e tente novamente");
        $this->esqueci();
    }

    public function resetar(array $parameters = []){

        if(!isset($parameters[0])){
            (new error())->index();
            return;
        }

        $elements = new elements;

        $form = new form("login/actionResetar/".$parameters[0],hasRecapcha:true);
        $form->setElement($elements->titulo(1,"Resetar Senha"));
        $form->setElement($elements->input("senha","Senha","",true,type:"password"));
        $form->setButton($elements->button("Redefinir","redefinir"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url."login")."'"));
        $form->show();
    }

    public function actionResetar(array $parameters = []):void
    {
        if(!isset($parameters[0])){
            (new error())->index();
            return;
        }

        $usuario = (new ModelsUsuario)->get(functions::decrypt($parameters[0]));
        $usuario->senha = $this->getValue("senha");

        if($usuario->set()){
            $this->index();
            return;
        }

        $this->resetar($parameters);
    }

    public function deslogar(array $parameters = []):void
    {
        ModelsLogin::deslogar();

        $this->go("login");
    }
}