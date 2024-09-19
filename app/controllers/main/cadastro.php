<?php
namespace app\controllers\main;
use app\models\login;
use app\controllers\abstract\controller;

class cadastro extends controller{

    public const headTitle = "Cadastro";

    public function index(){
        $user = login::getLogged();

        if($user->tipo_usuario == 1){
            (new empresa)->manutencao([$user->id]);
        }
        if($user->tipo_usuario == 2){
            (new funcionario)->manutencao([$user->id]);
        }
        if($user->tipo_usuario == 3){
            (new usuario)->manutencao([$user->id]);
        }
    }
}

?>