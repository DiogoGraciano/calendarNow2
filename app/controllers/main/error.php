<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\view\layout\error as lyError;
use app\view\layout\head;

final class error extends controller
{
    public const headTitle = "Error";

    public const addHeader = false;

    public const addFooter = false;

    public const permitAccess = true;

    public const addHead = false;

    public function index($parameters = [],$code = 404,$message = "A Pagina que está procurando não existe")
    {
        $head = new head("Erro");
        $head->show();
        
        $error = new lyError;
        $error->show($code,$message);
    }
}