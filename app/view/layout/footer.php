<?php
namespace app\view\layout;

use app\helpers\functions;
use app\models\login;
use app\models\main\empresaModel;
use app\view\layout\abstract\pagina;
use app\models\menu;
use core\url;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class footer extends pagina
{

    public function __construct(string $logo = "assets\imagens\logo_grande.png",int $tamanho_logo = 4)
    {
        $this->setTemplate("footer.html");
        if($logo){
            $this->tpl->logo = url::getUrlBase().$logo;
            $this->tpl->tamanho_logo = $tamanho_logo;
            $this->tpl->block("LOGO_FOOTER");
        }
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->ano = date("Y");
    }

    public function addLink(string $link,string $titulo,string $extra = ""):footer
    {
        $this->tpl->link = $link;
        $this->tpl->titulo = $titulo;
        $this->tpl->extra = $extra;
        $this->tpl->block("LINK_FOOTER");
        return $this;
    }

    public function setSection(string $titulo = "",int $tamanho = 3,array $links = []):footer
    {
        $this->tpl->titulo = $titulo;
        $this->tpl->tamanho = $tamanho;
        if($links){
            
        }
        $this->tpl->block("SECTION_FOOTER");
        return $this;
    }

    public function setSectionPagina(){
        $model = new menu;
    
        $menus = $model->getByFilter(ativo:1);

        $user = login::getLogged();
        
        $i = 1;
        $titulo = "Paginas";
        foreach ($menus as $menu){
            if($user && in_array($user->tipo_usuario,json_decode($menu["tipo_usuario"]))){
                if($menu["controller"])
                    $this->addLink(url::getUrlBase().$menu["controller"],$menu["nome"]);

                if($i == 6 && !$this->isMobile()){
                    $this->setSection($titulo,2);
                    $titulo = "&nbsp;";
                    $i = 1;
                }

                $i++;
            } 
        }
        if($i != 1)
            $this->setSection($titulo,2);

        return $this;
    }

    public function setSectionInstitucional(){

        $this->addLink(url::getUrlBase()."contato","Contato");
        $this->addLink(url::getUrlBase()."privacidade","Privacidade e Termos de Uso");
        $this->addLink(url::getUrlBase()."quemSomos","Quem Somos");
        $this->setSection("Institucional",2);

        return $this;
    }

    public function setSectionAtendimento(){
        
        $empresa = empresaModel::get(1);

        if($empresa->telefone)
            $this->addLink("tel:".functions::onlynumber($empresa->telefone),'<i class="fa-solid fa-phone me-2 icon"></i> '.$empresa->telefone);
        
        if($empresa->celular)
            $this->addLink("tel:".functions::onlynumber($empresa->celular),'<i class="fa-solid fa-mobile me-2 icon"></i> '.$empresa->celular);

        if($empresa->contato_email)
            $this->addLink("mailto:".$empresa->contato_email,'<i class="fa-solid fa-envelope me-2 icon"></i> '.$empresa->contato_email);

        if($empresa->contato_comercial)
            $this->addLink("mailto:".$empresa->contato_comercial,'<i class="fa-solid fa-handshake-angle me-2 icon"></i> '.$empresa->contato_comercial);
       
        if($empresa->contato_sac)
            $this->addLink("mailto:".$empresa->contato_sac,'<i class="fa-solid fa-question me-2 icon"></i> '.$empresa->contato_sac);
            
        if($empresa->horario_atendimento)
            $this->addLink(url::getUrlBase()."contato",$empresa->horario_atendimento);
            
        $this->setSection("Atendimento",4);

        return $this;
    }

    public function show():void
    {
        $this->setSectionPagina();
        $this->setSectionInstitucional();
        (new wave(4,"#0b5ed7",name:"footer",margin:3))->show();
        $this->tpl->show();
    }

    public function parse():string
    {
        $this->setSectionPagina();
        $this->setSectionInstitucional();
        return (new wave(4,"#0b5ed7",name:"footer",margin:3))->parse().$this->tpl->parse();
    }
}
