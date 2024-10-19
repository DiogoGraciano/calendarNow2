<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\contato as contatoModel;
use app\helpers\email;
use app\helpers\recapcha;
use app\view\layout\contato as LayoutContato;

final class contato extends controller{

    public const headTitle = "Contato";
    public const typeBanner = 1;

    public function index(array $parameters = []):void
    {
        $contato = new LayoutContato();
        $contato->show();
    }

    public function action(array $parameters = []):void
    {
        $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-contato-response"));

        if(!$recapcha){
            $contato = new LayoutContato();
            $contato->show();
            return;
        }

        $contato = new contatoModel;
        $contato->id = null;
        $contato->nome = $this->getValue("nome");
        $contato->email = $this->getValue("email");
        $contato->telefone = $this->getValue("telefone");
        $contato->assunto = $this->getValue("assunto");
        $contato->mensagem = $this->getValue("mensagem_envio");
        $contato->enviado = 0;

        $contato->set();

        if($contato){
            $mensagem = "Nome: ".$contato->nome;
            $mensagem .= PHP_EOL."Email: ".$contato->email;
            $mensagem .= PHP_EOL."Telefone: ".$contato->telefone;
            $mensagem .= PHP_EOL.PHP_EOL.$contato->mensagem;

            $email = new email;
            if($email->send($contato->assunto?:"Assunto Não Informado",$mensagem)){
                $contato->enviado = 1;
                $contato->set();
            }
        }

        $contato = new LayoutContato();
        $contato->show();
    }
}