<?php
namespace app\view\layout;
use app\view\layout\abstract\pagina;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class quemSomos extends pagina
{

    public function __construct(string $image)
    {
        $this->setTemplate("quemSomos.html");
        $this->tpl->image = $image;
    }
}
