<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\agenda;
use app\models\login;
use app\view\layout\lista;

final class home extends controller{

    public const headTitle = "Home";

    public const permitAccess = true;

    public function index(array $parameters = []){

        $user = login::getLogged();

        $agendas = (new agenda)->getByUsuario($user->id);

        $lista = new lista();

        if ($agendas){
            foreach ($agendas as $agenda){
               $lista->addObjeto($this->url."agendamento/index/".$agenda->id,$agenda->nome." - ".$agenda->emp_nome);
            }
        }

        $lista->setLista("Agendas");
        $lista->show();
    }
}