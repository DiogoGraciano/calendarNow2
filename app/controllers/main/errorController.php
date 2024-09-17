<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\view\layout\error;

final class errorController extends controller
{
    public const headTitle = "Error";

    public function index($parameters = [],$code = 404,$message = "A Pagina que está procurando não existe")
    {
        $error = new error;
        $error->show($code,$message);
    }
}