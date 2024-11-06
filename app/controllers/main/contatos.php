<?php 
namespace app\controllers\main;

use app\view\layout\form;
use app\view\layout\consulta;
use app\controllers\abstract\controller;
use app\models\contato;
use app\helpers\email;
use app\view\layout\elements;
use app\helpers\mensagem;
use app\view\layout\filter;
use app\view\layout\pagination;

final class contatos extends controller{

    public const headTitle = "Contatos";

    public function index(array $parameters = []):void
    {
        $this->loadConsulta();
    }

    public function loadConsulta():void
    {
        $nome = $this->getValue("nome");

        $email = $this->getValue("email");

        $telefone = $this->getValue("telefone");
        
        $enviado = $this->getValue("enviado");
        $enviado = $enviado == "" ? null : $enviado;

        $elements = new elements;

        $consulta = new consulta(true,"Contatos Consulta");

        $filter = new filter($this->url."contatos/loadConsulta","#consulta-admin");

        $filter->addFilter(3,$elements->input("nome","Nome:",$nome));

        $filter->addFilter(3,$elements->input("email","Email:",$email));

        $filter->addFilter(3,$elements->input("telefone","Telefone:",$telefone));

        $elements->addOption("","Todos");
        $elements->addOption(0,"Não");
        $elements->addOption(1,"Sim");
        $filter->addFilter(3,$elements->select("enviado","Enviado:",$enviado))
                ->addbutton($elements->button("Buscar","buscar",class:"btn btn-primary"))
                ->addLinha();

        $contato = new contato;

        $consulta->addFilter($filter)
            ->addButtons($elements->buttonHtmx("Visualizar","visualizar",$this->url."contatos/visualizar","#consulta-admin",class:"btn btn-primary",includes:"form"))
            ->addButtons($elements->buttonHtmx("Re-enviar","reenviar",$this->url."contatos/reenviar","#consulta-admin",class:"btn btn-primary",includes:"form"))
            ->addColumns("1","Id","id")
            ->addColumns("40","Nome","nome")
            ->addColumns("20","Assunto","assunto")
            ->addColumns("25","Email","email")
            ->addColumns("15","Telefone","telefone")
            ->addColumns("4","Enviado","enviado")
            ->setData($this->url."contato/manutencao",
                    $this->url."contato/action",
                    $contato->prepareData($contato->getByFilter($nome,$email,$telefone,$enviado,$this->getLimit(),$this->getOffset(),true)),
                    "id")
            ->addPagination(new pagination(
                            $contato::getLastCount("getByFilter"),
                            "contatos/loadConsulta",
                            $this->url."contato/loadConsulta",
                            limit:$this->getLimit()))
            ->show();
    }

    public function reenviar(){

        $ids = $this->getValue("massaction")??[];
        
        $idsSucesso = [];
        $idsErros = [];
        foreach ($ids as $id)
        {
            $contato = (new contato)->get($id);
            if($contato->id){
                $mensagem = "Nome: ".$contato->nome;
                $mensagem .= PHP_EOL."Email: ".$contato->email;
                $mensagem .= PHP_EOL."Telefone: ".$contato->telefone;
                $mensagem .= PHP_EOL.PHP_EOL.$contato->mensagem;

                $email = new email;
                $email->addEmail($contato->email);
                
                if($email->send($contato->assunto?:"Assunto Não Informado",$mensagem)){
                    $contato->enviado = 1;
                    $contato = $contato->set($contato);
                    if($contato)
                        $idsSucesso[] = $contato->id." - ".$contato->nome;
                    else
                        $idsErros[] = $id;
                }
                else
                    $idsErros[] = $id;
            }
            else 
                $idsErros[] = $id;
        }

        if($idsSucesso)
            mensagem::setSucesso("Contatos re-enviados com sucesso: <br>".implode("<br>",$idsSucesso));

        if($idsErros)
            mensagem::setErro("Contatos não re-enviados: <br>".implode("<br>",$idsErros));

        $this->loadConsulta();
    }

    public function visualizar(){
        
        $ids = $this->getValue("massaction")??[];

        $lastId = 0;
        if($key = array_key_last($ids)){
            $lastId = $ids[$key];
        }
        
        foreach ($ids as $id)
        {
            $contato = (new contato)->get($id);
            if($contato->id){

                if($contato->id == $lastId){
                    $this->getForm($contato,true);
                    return;
                }

                $this->getForm($contato);

            }
        }
    }

    private function getForm(?contato $contato = null,bool $last = false):void
    {
        $form = new form($this->url."contatos/visualizar");

        $elements = new elements;
        
        $form->setElement($elements->titulo(1,"Contato ".$contato->id,"fw-normal text-title mt-2 mb-3"))
            ->setElement($elements->checkbox("enviado","Enviado",checked:$contato->enviado??true))
            ->setThreeElements($elements->input("nome","Nome:",$contato->nome,readonly:true,max:250),
                            $elements->input("email","Email:",$contato->email,readonly:true),
                            $elements->input("telefone","Telefone:",$contato->telefone,readonly:true))
            ->setElement($elements->input("assunto","Assunto:",$contato->assunto,readonly:true))
            ->setElement($elements->textarea("mensagem","Mensagem:",$contato->mensagem,readonly:true));
           
        if($last)
            $form->setElement($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."contatos'"));
        
        $form->show();
    }
}