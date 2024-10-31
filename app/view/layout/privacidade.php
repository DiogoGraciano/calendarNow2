<?php
namespace app\view\layout;

use app\models\main\empresaModel;
use app\view\layout\abstract\layout;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class privacidade extends layout
{
    public function show():void
    {
        $this->setTemplate("privacidade.html");

        $empresa = (new calendarNow)->get(1);

        $this->tpl->empresa = $empresa;

        $this->tpl->show();
    }
}
