<?php 
namespace app\controllers\admin;
use app\view\layout\head;
use app\view\layout\form;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\models\marca;
use app\models\produto;
use app\models\produtoDocumento;
use app\models\produtoImagem;
use app\view\layout\elements;
use app\view\layout\footer;
use app\helpers\functions;
use app\helpers\mensagem;
use app\models\main\produtoModel;
use app\models\main\menuAdmModel;
use app\view\layout\filter;
use app\view\layout\header;
use app\view\layout\modal;
use app\view\layout\pagination;
use app\view\layout\tab;

final class produtoController extends controller{

    public function index(array $parameters = []):void
    {
        $head = new head("Produto");
        $head->show();

        $header = new header();
        $header->addMenus(new menuAdmModel)
        ->show(); 

        $elements = new elements;
        
        $form = new form($this->url."produto/mudarMarca/","mudarMarca");

        $elements->setOptions(new marca,"id","nome");
        $form->setInput($elements->select("id_marca","Marca"))
        ->setButton($elements->buttonHtmx("Salvar","salvarMarca",$this->url."produto/mudarMarca","#consulta-admin",includes:"form"))
        ->set();

        $modal = new modal("mudarMarca","Selecione a Marca desejada",$form->parse());
        $modal->show();

        $this->loadConsulta();
       
        $footer = new footer();
        $footer->setSectionPagina(new menuAdmModel)
                ->setSectionAtendimento()
                ->show();
    }

    public function manutencao(array $parameters = []):void
    {
        $head = new head("Manutenção Produto");
        $head->show();

        
        $header = new header();
        $header->addMenus(new menuAdmModel)
        ->show(); 

        $tab = new tab();

        if(!isset($parameters[0]))
            mensagem::setMensagem("Cadastro de imagens e documentos estará disponivel apenas depois que o produto for salvo");
        
        $tab->addTab("Manutenção",$this->loadFormManutencao($parameters)->parse(),true);
        if(isset($parameters[0])){
            $tab->addTab("Imagems",$this->loadFormImagem($parameters)->parse());
            $tab->addTab("Documentos",$this->loadFormDocumento($parameters)->parse());
        }
        $tab->show();
       
        $footer = new footer();
        $footer->setSectionPagina(new menuAdmModel)
                ->setSectionAtendimento()
                ->show();
    }

    public function action(array $parameters = []):void
    {
        if ($parameters){
            $id = functions::decrypt($parameters[0]);
            produtoModel::delete($id);
            $this->loadConsulta();
            return;
        }

        $produto = new produto;
    
        $produto->id                  = intval(functions::decrypt($this->getValue('cd')));
        $produto->id_marca            = $this->getValue('marca');
        $produto->nome                = $this->getValue('nome');
        $produto->descricao           = $this->getValue('descricao');
        $produto->ordem               = $this->getValue('ordem')?:1;
        $produto->ativo               = $this->getValue('ativo')?:0;

        if (produtoModel::set($produto)){ 
            $this->go("produto/manutencao/".functions::encrypt($produto->id));
            return;
        }

        mensagem::setSucesso(false);
        $this->loadFormManutencao($parameters,$produto)->show();
    }

    public function actionImagem(array $parameters = []):void
    {
        $id = 0;
        $id_produto = 0;

        if ($parameters && count($parameters) == 2){
            $id = functions::decrypt($parameters[1]);
            $id_produto = functions::decrypt($parameters[0]);
            produtoModel::deleteImagem($id,$id_produto);
            $this->loadFormImagem($parameters)->show();
            return;
        }
        elseif($parameters && count($parameters) == 1){
            $id_produto = functions::decrypt($parameters[0]);
        }
        else{
            $this->loadFormImagem([])->show();
            return;
        }

        $produto = new produtoImagem;
    
        $produto->id                  = intval(functions::decrypt($this->getValue('cd_imagem')));
        $produto->id_produto          = $id_produto;
        $produto->titulo              = $this->getValue('titulo');
        $produto->caminho             = $this->getValue("caminho_imagem")?:str_replace(functions::getRaiz(),"",functions::saveImagem("produtos".DIRECTORY_SEPARATOR."imagens","imagem"));
        $produto->ordem               = $this->getValue('ordem')?:1;

        if(!$produto->caminho){
            mensagem::setSucesso(false);
            $this->loadFormImagem($parameters,produtoImagem:$produto)->show();
            return;
        }

        if (produtoModel::setImagem($produto)){ 
            $this->loadFormImagem($parameters)->show();
            return;
        }

        mensagem::setSucesso(false);
        $this->loadFormImagem($parameters,produtoImagem:$produto)->show();
    }

    public function editarImagem(array $parameters = []){
        if ($parameters && count($parameters) == 2){
            $id = functions::decrypt($parameters[1]);
            $this->loadFormImagem($parameters,$id)->show();
            return;
        }

        $this->loadFormImagem($parameters)->show();
    }

    public function actionDocumento(array $parameters = []):void
    {
        $id = 0;
        $id_produto = 0;

        if ($parameters && count($parameters) == 2){
            $id_produto = functions::decrypt($parameters[0]);
            $id = functions::decrypt($parameters[1]);
            produtoModel::deleteDocumento($id,$id_produto);
            $this->loadFormDocumento($parameters)->show();
            return;
        }
        elseif($parameters && count($parameters) == 1){
            $id_produto = functions::decrypt($parameters[0]);
        }
        else{
            $this->loadFormDocumento([])->show();
            return;
        }

        $produto = new produtoDocumento;
    
        $produto->id                  = intval(functions::decrypt($this->getValue('cd_documento')));
        $produto->id_produto          = $id_produto;
        $produto->titulo              = $this->getValue('titulo');
        $produto->caminho             = $this->getValue("caminho_documento")?:str_replace(functions::getRaiz(),"",functions::saveDocumento("produtos".DIRECTORY_SEPARATOR."documentos","documento"));
        $produto->ordem               = $this->getValue('ordem')?:1;

        if(!$produto->caminho){
            mensagem::setSucesso(false);
            $this->loadFormDocumento($parameters,produtoDocumento:$produto)->show();
            return;
        }

        if (produtoModel::setDocumento($produto)){ 
            $this->loadFormDocumento($parameters)->show();
            return;
        }

        mensagem::setSucesso(false);
        $this->loadFormDocumento($parameters,produtoDocumento:$produto)->show();
    }

    public function editarDocumento(array $parameters = []){
        if ($parameters && count($parameters) == 2){
            $id = functions::decrypt($parameters[1]);
            $this->loadFormDocumento($parameters,$id)->show();
            return;
        }

        $this->loadFormDocumento($parameters)->show();
    }

    public function desativar(){

        $ids = $this->getValue("massaction")??[];
        
        $idsSucesso = [];
        $idsErros = [];
        foreach ($ids as $id)
        {
            $produto = produtoModel::get($id);
            if($produto->id){
                $produto->ativo = 0;
                $produto = produtoModel::set($produto);
                if($produto)
                    $idsSucesso[] = $produto->id." - ".$produto->nome;
                else
                    $idsErros[] = $id;
            }
            else 
                $idsErros[] = $id;
        }

        if($idsSucesso)
            mensagem::setSucesso("Produtos atualizados com sucesso: <br>".implode("<br>",$idsSucesso));

        if($idsErros)
            mensagem::setErro("Produtos não atualizados: <br>".implode("<br>",$idsErros));

        $this->loadConsulta();
    }

    public function ativar(){

        $ids = $this->getValue("massaction")??[];
        
        $idsSucesso = [];
        $idsErros = [];
        foreach ($ids as $id)
        {
            $produto = produtoModel::get($id);
            if($produto->id){
                $produto->ativo = 1;
                $produto = produtoModel::set($produto);
                if($produto)
                    $idsSucesso[] = $produto->id." - ".$produto->nome;
                else
                    $idsErros[] = $id;
            }
            else 
                $idsErros[] = $id;
        }

        if($idsSucesso)
            mensagem::setSucesso("Produtos atualizados com sucesso: <br>".implode("<br>",$idsSucesso));

        if($idsErros)
            mensagem::setErro("Produtos não atualizados: <br>".implode("<br>",$idsErros));

        $this->loadConsulta();
    }

    public function mudarMarca(){
        $ids = $this->getValue("massaction")??[];
        $id_marca = intval($this->getValue("id_marca"));
        
        $idsSucesso = [];
        $idsErros = [];
        foreach ($ids as $id)
        {
            $produto = produtoModel::get($id);
            if($produto->id && $id_marca){
                $produto->id_marca = $id_marca;
                $produto = produtoModel::set($produto);
                if($produto)
                    $idsSucesso[] = $produto->id." - ".$produto->nome;
                else
                    $idsErros[] = $id;
            }
            else 
                $idsErros[] = $id;
        }

        if($idsSucesso)
            mensagem::setSucesso("Produtos atualizados com sucesso: <br>".implode("<br>",$idsSucesso));

        if($idsErros)
            mensagem::setErro("Produtos não atualizados: <br>".implode("<br>",$idsErros));

        $this->loadConsulta();
    }

    public function loadConsulta():void
    {
        $id_marca = $this->getValue("id_marca");
        $id_marca = $id_marca == "" ? null : intval($id_marca);

        $ativo = $this->getValue("ativo");
        $ativo = $ativo == "" ? null : intval($ativo);
        
        $nome = $this->getValue("nome");

        $elements = new elements;

        $consulta = new consulta(true,"Produtos Consulta");

        $filter = new filter($this->url."produto/loadConsulta","#consulta-admin");

        $filter->addFilter(3,$elements->input("nome","Nome:",$nome));

        $elements->addOption("","Todos");
        $elements->setOptions(new marca,"id","nome");
        $filter->addFilter(3,$elements->select("id_marca","Marca:",$id_marca));

        $elements->addOption("","Todos");
        $elements->addOption(0,"Não");
        $elements->addOption(1,"Sim");
        $filter->addFilter(3,$elements->select("ativo","Ativo:",$ativo))
                ->addbutton($elements->button("Buscar","buscar",class:"btn btn-primary"))
                ->addLinha();

        $consulta->addFilter($filter)
            ->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."produto/manutencao'"))
            ->addButtons($elements->buttonHtmx("Desativar","desativar",$this->url."produto/desativar","#consulta-admin",class:"btn btn-primary",includes:"form"))
            ->addButtons($elements->buttonHtmx("Ativar","ativar",$this->url."produto/ativar","#consulta-admin",class:"btn btn-primary",includes:"form"))
            ->addButtons($elements->buttonModal("Mudar Marca","mudarMarca","#mudarMarca"))
            ->addColumns("1","Id","id")
            ->addColumns("50","Nome","nome")
            ->addColumns("15","Marca","nome_marca")
            ->addColumns("4","Ordem","ordem")
            ->addColumns("4","Ativo","ativo")
            ->addColumns("11","Ações","acoes")
            ->setData($this->url."produto/manutencao",
                    $this->url."produto/action/",
                    produtoModel::prepareData(produtoModel::getByFilter($nome,$id_marca,$ativo,$this->getLimit(),$this->getOffset(),true)),
                    "id")
            ->addPagination(new pagination(
                produtoModel::getLastCount("getByFilter"),
                $this->url."produto/loadConsulta",
                limit:$this->getLimit()))
            ->show();
    }

    private function loadFormManutencao(array $parameters = [],?produto $produto = null):form
    {
        $id = "";

        $form = new form($this->url."produto/action/");

        $elements = new elements;

        if ($parameters && array_key_exists(0,$parameters)){
            $id = functions::decrypt($parameters[0]);
            $form->setHidden("cd",$parameters[0]);
        }
        
        $dado = $produto?:produtoModel::get($id);

        $elements->setOptions(new marca,"id","nome");
        $form->setInput($elements->titulo(1,"Produto Manutenção","fw-normal text-title mt-2 mb-3"))
            ->setInput($elements->input("nome","Nome:",$dado->nome,true,max:250))
            ->setDoisInputs($elements->select("marca","Marca:",$dado->id_marca),
                            $elements->input("ordem","Ordem:",$dado->ordem,type:"number"))
            ->setInput($elements->checkbox("ativo","Ativo",checked:$dado->ativo??true))
            ->setInput($elements->textarea("descricao","Descricao:",$dado->descricao,max:1000))
            ->setButton($elements->button("Salvar","submit"))
            ->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."produto'"))
            ->set();

        return $form;
    }

    private function loadFormImagem(array $parameters = [], ?int $id = null,?produtoImagem $produtoImagem = null):form
    {
        $id_produto = 0;

        if ($parameters && array_key_exists(0,$parameters)){
            $form = new form($this->url."produto/actionImagem/".$parameters[0],"imagem");
            $id_produto = intval(functions::decrypt($parameters[0]));
        }
        else{
            $form = new form($this->url."produto/actionImagem/","imagem");
        }

        $form->setHidden("cd_imagem",functions::encrypt($id));

        $elements = new elements;
        
        $dado = $produtoImagem?:produtoModel::getProdutoImage($id);

        $elements->setOptions(new marca,"id","nome");
        $form->setInput($elements->titulo(1,"Produto Imagens","fw-normal text-title mt-2 mb-3"))
            ->setInput($elements->input("titulo","Titulo:",$dado->titulo,true,max:250))
            ->setDoisInputs($elements->input("imagem","Imagem:",type:"file"),
                            $elements->input("ordem","Ordem:",$dado->ordem,type:"number"));
        
        $form->setHidden("caminho_imagem",$dado->caminho);

        $form->setButton($elements->button("Salvar","submit"));

        $imagens = produtoModel::getImagensByFilter($id_produto);
        foreach ($imagens as $imagem)
        {
            $form->addCustomInput(3,$elements->img($imagem["caminho"],$imagem["titulo"]));
            $form->addCustomInput(3,[
                $elements->buttonHtmx("Editar Imagem","editarImagem",$this->url."produto/editarImagem/".$parameters[0]."/".functions::encrypt($imagem["id"]),"#form-imagem"),
                $elements->buttonHtmx("Excluir Imagem","excluirImagem",$this->url."produto/actionImagem/".$parameters[0]."/".functions::encrypt($imagem["id"]),"#form-imagem",confirmMessage:"Tem certeza que desaja excluir?",class:"btn btn-primary w-100 mt-1 pt-2 btn-block")
                ]
            );
            $form->setCustomInputs("align-items-center");
        }
           
        return $form->set();
    }

    private function loadFormDocumento(array $parameters = [], ?int $id = null,?produtoDocumento $produtoDocumento = null):form
    {
        $id_produto = 0;

        if ($parameters && array_key_exists(0,$parameters)){
            $form = new form($this->url."produto/actionDocumento/".$parameters[0],"documento");
            $id_produto = intval(functions::decrypt($parameters[0]));
        }
        else{
            $form = new form($this->url."produto/actionDocumento/","documento");
        }
    
        $form->setHidden("cd_documento",functions::encrypt($id));

        $elements = new elements;
        
        $dado = $produtoDocumento?:produtoModel::getProdutoDocumento($id);

        $elements->setOptions(new marca,"id","nome");
        $form->setInput($elements->titulo(1,"Produto Documentos","fw-normal text-title mt-2 mb-3"))
            ->setInput($elements->input("titulo","Titulo:",$dado->titulo,true,max:250))
            ->setDoisInputs($elements->input("documento","Documento:",type:"file"),
                            $elements->input("ordem","Ordem:",$dado->ordem,type:"number"));

        $form->setHidden("caminho_documento",$dado->caminho);

        $form->setButton($elements->button("Salvar","submit"));

        $documentos = produtoModel::getDocumentosByFilter($id_produto);
        foreach ($documentos as $documento)
        {
            $form->addCustomInput(4,$elements->embed($documento["caminho"]));
            $form->addCustomInput(3,[
                $elements->buttonHtmx("Editar Documento","editarDocumento",$this->url."produto/editarDocumento/".$parameters[0]."/".functions::encrypt($documento["id"]),"#form-documento"),
                $elements->buttonHtmx("Excluir Documento","excluirDocumento",$this->url."produto/actionDocumento/".$parameters[0]."/".functions::encrypt($documento["id"]),"#form-documento",confirmMessage:"Tem certeza que desaja excluir?",class:"btn btn-primary w-100 mt-1 pt-2 btn-block")
                ]
            );
            $form->setCustomInputs();
        }
           
        return $form->set();
    }
}