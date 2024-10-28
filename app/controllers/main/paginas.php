<?php 
namespace app\controllers\admin;
use app\view\layout\form;
use app\controllers\abstract\controller;
use app\models\paragrafo;
use app\view\layout\elements;
use app\helpers\mensagem;
use app\view\layout\tab;

final class paginas extends controller{

    public const headTitle = "Paginas";

    public function index(array $parameters = []){
        $tabs = new tab;

        $tabs->addTab("Quem Somos",$this->loadForm([],pagina:"quemsomos")->parse(),true);
        $tabs->addTab("Privacidade",$this->loadForm([],pagina:"privacidade")->parse());

        $tabs->show();
    }

    public function editar(array $parameters = []){
        if(isset($parameters[0],$parameters[1])){
            $this->loadForm([$parameters[1]],pagina:$parameters[0])->show();
            return;
        }

        $this->go("paginas");
    }

    public function loadForm(array $parameters = [],?paragrafo $paragrafo = null,string $pagina = ""):form
    {
        $id = 0;

        $form = new form($this->url."paginas/action","pagina-".$pagina);

        if (isset($parameters[0])){
            $id = $parameters[0];
            $form->setHidden("cd",$parameters[0]);
        }

        $elements = new elements;
        
        $dado = $paragrafo?:(new paragrafo)->get($id);

        $elements->addOption("fade-right","fade-right");
        $elements->addOption("fade-left","fade-left");
        $form->setHidden("pagina",$pagina)
            ->setElement($elements->titulo(1,"Paragrafos","fw-normal text-title mt-2 mb-3"))
            ->setElement($elements->input("titulo","Titulo:",$dado->titulo,true,max:250))
            ->setTwoElements($elements->select("efeito","Efeito:",$dado->efeito),
                            $elements->input("ordem","Ordem:",$dado->ordem,type:"number",min:1))
            ->setElement($elements->textarea("editor","Descrição:",$dado->descricao,max:10000));

        $form->setButton($elements->button("Salvar","submit"));

        $paragrafos = (new paragrafo)->getByFilter(pagina:$pagina);
        
        foreach ($paragrafos as $paragrafo)
        {
            $form->addCustomElement(6,$elements->titulo(3,$paragrafo["titulo"])."<br>".$paragrafo["descricao"]);
            $form->addCustomElement(6,[
                $elements->button("Excluir Paragrafo","excluirParagrafo",action:"location.href='".$this->url."paginas/action/".$pagina."/".$paragrafo["id"]."'",class:"btn btn-primary w-100 mt-1 pt-2 btn-block")
                ]
            );
            $form->setCustomElements("align-items-center");
        }

        return $form;
    }

    public function action(array $parameters = []):void
    {
        if (isset($parameters[0],$parameters[1])){
            $paragrafo = new paragrafo;
            $paragrafo->id = $parameters[1];
            $paragrafo->remove();
            $this->go("paginas");
            return;
        }

        $paragrafo = new paragrafo;
    
        $paragrafo->id                = intval($this->getValue('cd'));
        $paragrafo->titulo            = $this->getValue('titulo');
        $paragrafo->pagina            = $this->getValue('pagina');
        $paragrafo->descricao         = $this->getValue('editor');
        $paragrafo->efeito            = $this->getValue('efeito')?:1;
        $paragrafo->ordem             = $this->getValue('ordem')?:1;

        if ($paragrafo->set()){ 
            $this->go("paginas");
            return;
        }

        mensagem::setSucesso(false);
        $this->go("paginas");
        return;
    }
}