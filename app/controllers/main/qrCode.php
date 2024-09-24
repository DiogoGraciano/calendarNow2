<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\agenda;
use app\models\empresa;
use app\view\layout\qrCode as layoutQrCode;

final class qrCode extends controller{

    public const headTitle = "qrCode";

    public const addHeader = false;

    public const addFooter = false;

    public const permitAccess = true;

    public function agendaQrCode(array $parameters = []){

        if(isset($parameters[0])){
            $agenda = (new agenda())->get($parameters[0]);
            $empresa = (new empresa())->getByAgenda($agenda->id);

            (new layoutQrCode($this->url."encontrar/action/".$agenda->codigo,"Agenda",$empresa->nome,$agenda->nome))->show();
        }
        else
            (new error())->index();
    }
}