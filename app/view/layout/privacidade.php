<?php
namespace app\view\layout;

use app\models\main\empresaModel;
use app\view\layout\abstract\pagina;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class privacidade extends pagina
{
    public function show():void
    {
        $this->setTemplate("privacidade.html");

        $empresa = empresaModel::get(1);

        $this->tpl->empresa = $empresa;

        $this->tpl->show();
    }
}
