<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\controllers\abstract\controller;
use app\models\paginas as paginaModel;
use app\view\layout\elements;
use app\helpers\mensagem;

final class paginas extends controller{

    public const headTitle = "Paginas";

    public function index(array $parameters = []){
        $this->loadForm([]);
    }

    public function loadForm(array $parameters = [],?paginaModel $pagina = null):void
    {
        $id = 0;

        $form = new form($this->url."pagina/action","pagina");

        if (isset($parameters[0])){
            $id = $parameters[0];
            $form->setHidden("cd",$parameters[0]);
        }

        $elements = new elements;
        
        $dado = $pagina?:(new paginaModel)->get($id);

        $elements->addOption("fade-right","fade-right");
        $elements->addOption("fade-left","fade-left");
        $form->setElement($elements->titulo(1,"Paragrafos","fw-normal text-title mt-2 mb-3"))
            ->setElement($elements->input("titulo","Titulo:",$dado->titulo,true,max:250))
            ->setTwoElements($elements->select("efeito","Efeito:",$dado->efeito),
                            $elements->input("ordem","Ordem:",$dado->ordem,type:"number",min:1))
            ->setElement($elements->textarea("editor","Descrição:",$dado->descricao,max:10000));

        $form->setButton($elements->button("Salvar","submit"));

        $paginas = (new paginaModel)->getByFilter();
        
        foreach ($paginas as $pagina)
        {
            $form->addCustomElement(6,$elements->titulo(3,$pagina["titulo"])."<br>".$pagina["descricao"]);
            $form->addCustomElement(6,[
                $elements->buttonHtmx("Editar Paragrafo","editarParagrafo",$this->url."pagina/loadForm/".$pagina["id"],"#form-pagina",class:"btn btn-primary w-100 mt-1 pt-2 btn-block"),
                $elements->buttonHtmx("Excluir Paragrafo","excluirParagrafo",$this->url."pagina/action/".$pagina["id"],"#form-pagina",confirmMessage:"Tem certeza que desaja excluir?",class:"btn btn-primary w-100 mt-1 pt-2 btn-block")
                ]
            );
            $form->setCustomElements("align-items-center");
        }
           
        $form->show();
    }

    public function action(array $parameters = []):void
    {
        if ($parameters){
            $pagina = new paginaModel;
            $pagina->id = $parameters[0];
            $pagina->remove();
            $this->loadForm([]);
            return;
        }

        $pagina = new paginaModel;
    
        $pagina->id                = intval($this->getValue('cd'));
        $pagina->titulo            = $this->getValue('titulo');
        $pagina->pagina            = "quemsomos";
        $pagina->descricao         = $this->getValue('editor');
        $pagina->efeito            = $this->getValue('efeito')?:1;
        $pagina->ordem             = $this->getValue('ordem')?:1;

        if ($pagina->set()){ 
            $this->go("pagina");
            return;
        }

        mensagem::setSucesso(false);
        $this->loadForm($parameters,$pagina);
        return;
    }
}