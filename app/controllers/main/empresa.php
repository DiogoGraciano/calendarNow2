<?php

namespace app\controllers\main;

use app\view\layout\form;
use app\view\layout\elements;
use app\controllers\abstract\controller;
use app\view\layout\consulta;
use app\helpers\functions;
use app\helpers\mensagem;
use diogodg\neoorm\transactionManeger;
use app\helpers\integracaoWs;
use app\helpers\logger;
use app\helpers\recapcha;
use app\models\cidade;
use app\models\configuracoes;
use app\models\empresa as empresaModel;
use app\models\endereco;
use app\models\estado;
use app\models\login;
use app\models\segmento;
use app\models\usuario;
use app\view\layout\filter;
use app\view\layout\pagination;

class empresa extends controller {

    public const headTitle = "Empresa";

    public function index($parameters = []):void
    {
        $nome = $this->getValue("nome");

        $elements = new elements();

        $filter = new filter($this->url."usuario/");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));
        $filter->addFilter(4,$elements->input("nome","Nome:",$nome));
        $filter->show();

        $cadastro = new consulta(false,"Empresa");
        $cadastro->addButtons($elements->button("Adicionar","manutencao","button","btn btn-primary","location.href='".$this->url."empresa/manutencao'"));
        $cadastro->addButtons($elements->button("Voltar", "voltar", "button", "btn btn-primary", "location.href='".$this->url."home'"));

        $empresa = (new empresaModel);
        $dados = $empresa->prepareData($empresa->getByFilter($nome,$this->getLimit(),$this->getOffset()));

        $cadastro->addColumns("1", "Id", "id")
                 ->addColumns("10", "CPF", "cpf")
                 ->addColumns("15", "Nome", "nome")
                 ->addColumns("15", "Email", "email")
                 ->addColumns("11", "Telefone", "telefone")
                 ->setData($this->url."empresa/manutencao", $this->url."empresa/action", $dados,"id")
                 ->addPagination(new pagination(
                    $empresa::getLastCount("getByFilter"),
                    "#consulta-admin",
                    limit:$this->getLimit()))
                 ->show();
    }

    public function manutencao($parameters = [],?usuario $usuario = null,?endereco $endereco = null,?empresaModel $empresa = null):void
    {
        $id = null;
       
        if ($parameters && array_key_exists(0, $parameters)){
            $id = intval($parameters[0]); 
        }
      
        $form = new form($this->url."empresa/action","empresa",hasRecapcha:true);

        $dado = $usuario?:(new usuario)->get($id);
        $form->setHidden("cd", $dado->id);

        $dadoEndereco = $endereco?:(new endereco)->get($dado->id_empresa,"id_empresa");
        $form->setHidden("id_endereco", $dadoEndereco->id);

        $dadoEmpresa = $empresa?:(new empresaModel)->get($dado->id_empresa);
        $form->setHidden("id_empresa", $dadoEmpresa->id);

        $elements = new elements();

        $elements->setOptions((new segmento)->getAll(),"id","nome");
        $form->setElement($elements->titulo(1,"Cadastro de Empresa"))
        ->setThreeElements(
            $elements->input("nome", "Nome do Usuario", $dado->nome, true),
            $elements->input("cpf_cnpj", "CPF/CNPJ da Empresa:", $dado->cpf_cnpj?functions::formatCnpjCpf($dado->cpf_cnpj):"", true),
            $elements->select("segmento", "Segmento:",$dadoEmpresa->id_segmento),
            array("nome", "cpf_cnpj","segmento")
        )->setThreeElements(
            $elements->input("email","Email",$dado->email, true, false,"",type:"email"),
            $elements->input("senha","Senha","",$dado->senha?false:true,false,type:"password"),
            $elements->input("telefone", "Telefone",functions::formatPhone($dado->telefone),true,type:"tel"),
            array("email", "senha", "telefone")
        )->setThreeElements(
            $elements->input("nome_empresa", "Nome da Empresa", $dadoEmpresa->nome, true),
            $elements->input("fantasia", "Nome Fantasia", $dadoEmpresa->fantasia, true),
            $elements->input("razao", "Razao Social:", $dadoEmpresa->razao, true),
            array("nome_empresa", "fantasia", "razao")
        );

        $elements->setOptions((new estado())->getAll(), "id", "nome");
        $estado = $elements->select("id_estado","Estado", $dadoEndereco->id_estado ?: 24, true);

        $form->setTwoElements(
            $elements->input("cep", "CEP", $dadoEndereco->cep, true),
            $estado,
            array("cep","id_estado")
        );

        $elements->setOptions((new cidade())->getByEstado($dadoEndereco->id_estado ?: 24), "id", "nome");
        $form->setTwoElements(
            $elements->select("id_cidade","Cidade",$dadoEndereco->id_cidade ?: 4487, true),
            $elements->input("bairro", "Bairro", $dadoEndereco->bairro, true),
            array("bairro", "id_cidade")
        );

        $form->setTwoElements(
            $elements->input("rua","Rua",$dadoEndereco->rua,true),
            $elements->input("numero","Numero",$dadoEndereco->numero,true,false,type:"number",min:0,max:99999),
            array("rua", "numero")
        )->setElement($elements->textarea("complemento", "Complemento", $dadoEndereco->complemento), "complemento")
        ->setButton($elements->button("Salvar", "submit"))
        ->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url."empresa")."'"))
        ->show();
    }

    public function action($parameters = []):void
    {
        $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-usuario-response"));

        if(!$recapcha){
            $this->manutencao();
            return;
        }

        $cpf_cnpj = $this->getValue('cpf_cnpj');
        $email = $this->getValue('email');
        $telefone = $this->getValue('telefone');

        $usuario               = new usuario;
        $id                    = intval($this->getValue('cd'));
        $usuario->id           = $id;
        $usuario->nome         = $this->getValue('nome');
        $usuario->cpf_cnpj     = $cpf_cnpj;
        $senha                 = $this->getValue('senha');
        $usuario->senha        = $senha;
        $usuario->email        = $email;
        $usuario->telefone     = $telefone;
        $usuario->tipo_usuario = 1;
        $usuario->ativo        = 0;

        $empresa               = new empresaModel;
        $id_empresa            = intval($this->getValue('id_empresa'));
        $empresa->id           = $id_empresa;
        $empresa->nome         = $this->getValue('nome_empresa');
        $empresa->cpf_cnpj     = $cpf_cnpj;
        $empresa->fantasia     = $this->getValue('fantasia');
        $empresa->email        = $email;
        $empresa->telefone     = $telefone;
        $empresa->razao        = $this->getValue('razao');
        $empresa->id_segmento  = intval($this->getValue('segmento'));

        $endereco              = new endereco;
        $id_endereco           = intval($this->getValue('id_endereco'));
        $endereco->id          = $id_endereco;
        $endereco->cep         = $this->getValue('cep');
        $endereco->id_estado   = intval($this->getValue('id_estado'));
        $endereco->id_cidade   = intval($this->getValue('id_cidade'));
        $endereco->bairro      = $this->getValue('bairro');
        $endereco->rua         = $this->getValue('rua');
        $endereco->numero      = $this->getValue('numero');
        $endereco->complemento = $this->getValue('complemento');

        try {

            transactionManeger::init();
            transactionManeger::beginTransaction();

            if ($empresa->set()){
                $usuario->id_empresa = $empresa->id;
                if($usuario->set(false)){
                    $endereco->id_usuario = $usuario->id;
                    $endereco->id_empresa = $empresa->id;
                    if($endereco->set(false)){
                        if(!$id_empresa){
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "max_agendamento_dia";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = 2;
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "max_agendamento_dia";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = 3;
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "max_agendamento_mes";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = 3;
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "hora_ini";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = "08:00";
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "hora_ini";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = "18:00";
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "hora_almoco_ini";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = "12:00";
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "hora_almoco_fim";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = "14:00";
                            $configuracoes->set();
                            $configuracoes = new configuracoes;
                            $configuracoes->identificador = "mostrar_endereco";
                            $configuracoes->id_empresa = $empresa->id;
                            $configuracoes->valor = "N";
                            $configuracoes->set();
                        }
    
                        mensagem::setSucesso("Usuario empresarial salvo com sucesso");
                        transactionManeger::commit();

                        $login = (new login);
                        if(!$login->getLogged() && $login->login($usuario->cpf_cnpj,$senha)){
                            $this->go("agenda");
                        }

                        $this->manutencao([$usuario->id],$usuario,$endereco,$empresa);
                        return;
                    }
                }
            }
        } catch (\Exception $e) {
            mensagem::setErro("Erro ao Salvar Empresa Tente Novamente");
            logger::error($e->getMessage()." ".$e->getTraceAsString());
            mensagem::setSucesso(false);
            transactionManeger::rollback();
            if(!$id_empresa || $id || $id_endereco){
                $usuario->id = null;
                $empresa->id = null;
                $endereco->id = null;
            }
            $this->manutencao([$usuario->id],$usuario,$endereco,$empresa);
            return;
        }

        mensagem::setSucesso(false);
        transactionManeger::rollback();
        if(!$id_empresa || $id || $id_endereco){
            $usuario->id = null;
            $empresa->id = null;
            $endereco->id = null;
        }
        $this->manutencao([$usuario->id],$usuario,$endereco,$empresa);
    }
}

?>
