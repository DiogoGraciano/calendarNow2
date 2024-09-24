<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\view\layout\error as lyError;

final class error extends controller
{
    public const headTitle = "Error";

    public const addHeader = false;

    public const addFooter = false;

    public const permitAccess = true;

    public function index($parameters = [],$code = 404,$message = "A Pagina que está procurando não existe")
    {
        $error = new lyError;
        $error->show($code,$message);
    }
}