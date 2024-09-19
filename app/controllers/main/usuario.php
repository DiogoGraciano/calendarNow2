<?php

namespace app\controllers\main;

use app\view\layout\form;
use app\view\layout\elements;
use app\helpers\functions;
use app\controllers\abstract\controller;
use app\view\layout\consulta;
use app\helpers\mensagem;
use app\view\layout\filter;
use app\db\transactionManeger;
use app\models\cidade;
use app\models\endereco;
use app\models\estado as ModelsEstado;
use app\models\funcionario;
use app\models\login;
use app\models\usuario as usuarioModel;
use app\models\usuarioBloqueio;
use app\view\layout\pagination;

final class usuario extends controller {

    public const headTitle = "Usuario";

    public function index()
    {
        $id_funcionario = $this->getValue("funcionario");
        $nome = $this->getValue("nome");

        $elements = new elements();

        $user = login::getLogged();

        $filter = new filter($this->url."usuario/");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));

        $filter->addFilter(4,$elements->input("nome","Nome:",$nome));

        $funcionarios = (new funcionario())->getByEmpresa($user->id_empresa);

        if ($funcionarios){
            $elements->addOption("","Selecione/Todos");
            foreach ($funcionarios as $funcionario){
                $elements->addOption($funcionario->id,$funcionario->nome);
            }

            $funcionarios = $elements->select("funcionario","Funcionario",$id_funcionario);

            $filter->addFilter(4,$funcionarios);
        }

        $cadastro = new consulta(true,"Consulta Usuarios");
        
        $cadastro->addButtons($elements->buttonHtmx("Bloquear Usuario","usuarioblock","bloquear","#consulta-admin",class:"btn btn-primary"));
        $cadastro->addButtons($elements->buttonHtmx("Desbloquear Usuario","usuariounblock","desbloquear","#consulta-admin",class:"btn btn-primary"));
        $cadastro->addButtons($elements->button("Voltar","voltar","button","btn btn-primary","location.href='".$this->url."home'"));
        
        $usuario = (new usuarioModel);
        $dados = $usuario->prepareData($usuario->getByFilter($user->id_empresa,$nome,$id_funcionario,3,$this->getLimit(),$this->getOffset()));

        $cadastro->addColumns("1", "Id", "id")
                 ->addColumns("10", "CPF", "cpf")
                 ->addColumns("15", "Nome", "nome")
                 ->addColumns("15", "Email", "email")
                 ->addColumns("11", "Telefone", "telefone")
                 ->setData($this->url."usuario/manutencao", $this->url."usuario/action", $dados,"id")
                 ->addPagination(new pagination(
                    $usuario::getLastCount("getByFilter"),
                    $this->url."usuario/index",
                    limit:$this->getLimit()))
                ->addFilter($filter)
                 ->show();
    }

    public function bloquear($parameters = []):void
    {
        try{

            transactionManeger::init();

            transactionManeger::beginTransaction();

            $qtd_list = $this->getValue("qtd_list");

            $user = login::getLogged();

            $mensagem = "Usuarios bloqueados com sucesso: ";
            $mensagem_erro = "Usuarios não bloqueados: ";

            if ($qtd_list){
                for ($i = 1; $i <= $qtd_list; $i++) {
                    if($id_usuario = $this->getValue("id_check_".$i)){

                        $usuarioBloqueio = (new usuarioBloqueio);
                        $usuarioBloqueio->id_usuario = $id_usuario;
                        $usuarioBloqueio->id_empresa = $user->id_empresa;

                        if($usuarioBloqueio->set())
                            $mensagem .= $id_usuario." <br> ";
                        else
                            $mensagem_erro .= $id_usuario." <br> ";
                    }
                }
                $mensagem_erro = rtrim($mensagem_erro," <br> ");
                $mensagem = rtrim($mensagem," <br> ");
            }
            else{
                mensagem::setErro("Não foi possivel encontrar o numero total de usuarios");
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente");
            transactionManeger::rollback();
            $this->index();
            return;
        }

        transactionManeger::commit();
        $this->index();
    }

    public function desbloquear($parameters = []){
        try{

            transactionManeger::init();

            transactionManeger::beginTransaction();

            $qtd_list = $this->getValue("qtd_list");

            $user = login::getLogged();

            $mensagem = "Usuarios bloqueados com sucesso: ";
            $mensagem_erro = " Usuarios não bloqueados: ";

            if ($qtd_list){
                for ($i = 1; $i <= $qtd_list; $i++) {
                    if($id_usuario = $this->getValue("id_check_".$i)){

                        $usuarioBloqueio = (new usuarioBloqueio);
                        $usuarioBloqueio->id_usuario = $id_usuario;
                        $usuarioBloqueio->id_empresa = $user->id_empresa;

                        if($usuarioBloqueio->remove())
                            $mensagem .= $id_usuario." - ";
                        else
                            $mensagem_erro .= $id_usuario." - ";
                    }
                }
                $mensagem_erro = rtrim($mensagem_erro," - ");
                $mensagem = rtrim($mensagem," - ");
            }
            else{
                mensagem::setErro("Não foi possivel encontrar o numero total de usuarios");
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente");
            transactionManeger::rollback();
            $this->index();
            return;
        }

        transactionManeger::commit();

        $this->index();
    }

    public function manutencao($parameters = [],?usuarioModel $usuario = null,?endereco $endereco = null){

        $id = null;
        $location = null;

        if ($parameters && array_key_exists(0, $parameters)){
            if (array_key_exists(0, $parameters)){
                $id = intval($parameters[0]); 
            }
        }
    
        $form = new form($this->url."usuario/action/".$location?:"");

        $dado = $usuario?:(new usuarioModel)->get($id);

        $dadoEndereco = $endereco?:(new endereco)->get($dado->id,"id_usuario");

        $elements = new elements();

        $form->setHidden("cd",$dado->id);
        $form->setHidden("id_endereco",$dadoEndereco->id);

        $form->setInput($elements->titulo(1,"Cadastro de Usuario"))
        ->setDoisInputs(
            $elements->input("nome", "Nome", $dado->nome, true),
            $elements->input("cpf_cnpj", "CPF/CNPJ", functions::formatCnpjCpf($dado->cpf_cnpj), true),
            array("nome", "cpf_cnpj")
        );

        $form->setTresInputs(
            $elements->input("email", "Email", $dado->email, true, false, "", "email"),
            $elements->input("senha", "Senha", "", $dado->senha?false:true, false, "", "password"),
            $elements->input("telefone", "Telefone", functions::formatPhone($dado->telefone), true),
            array("email", "senha", "telefone")
        );

        $elements->setOptions((new ModelsEstado())->getAll(), "id", "nome");
        $estado = $elements->select("id_estado","Estado",$dadoEndereco->id_estado ?: 24, true);

        $form->setDoisInputs(
            $elements->input("cep", "CEP", $dadoEndereco->cep, true),
            $estado,
            array("cep", "id_estado")
        );

        $elements->setOptions((new cidade())->getByEstado($dadoEndereco->id_estado ?: 24), "id", "nome");
        $form->setDoisInputs(
            $elements->select("id_cidade","Cidade", $dadoEndereco->id_cidade ?: 4487, true),
            $elements->input("bairro", "Bairro", $dadoEndereco->bairro, true),
            array("bairro", "id_cidade")
        );

        $form->setDoisInputs(
            $elements->input("rua", "Rua", $dadoEndereco->rua, true),
            $elements->input("numero", "Número", $dadoEndereco->numero, true, false, "", "number", "form-control", 'min="0" max="999999"'),
            array("rua", "numero")
        );

        $form->setInput($elements->textarea("complemento", "Complemento", $dadoEndereco->complemento), "complemento");

        $form->setButton($elements->button("Salvar", "submit"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url.$location?:"login")."'"));

        $form->show();
    }

    public function action($parameters = []):void
    {
        $id = intval($this->getValue('cd'));
        $nome = $this->getValue('nome');
        $cpf_cnpj = $this->getValue('cpf_cnpj');
        $senha = $this->getValue('senha');
        $email = $this->getValue('email');
        $telefone = $this->getValue('telefone');
        $id_endereco = intval($this->getValue('id_endereco'));
        $cep = $this->getValue('cep');
        $id_estado = intval($this->getValue('id_estado'));
        $id_cidade = intval($this->getValue('id_cidade'));
        $bairro = $this->getValue('bairro');
        $rua = $this->getValue('rua');
        $numero = $this->getValue('numero');
        $complemento = $this->getValue('complemento');

        $usuario = new usuarioModel;
        $usuario->id           = $id;
        $usuario->nome         = $nome;
        $usuario->cpf_cnpj     = $cpf_cnpj;
        $usuario->senha        = $senha;
        $usuario->email        = $email;
        $usuario->tipo_usuario = 3;
        $usuario->telefone     = functions::onlynumber($telefone);

        $endereco              = new endereco;
        $endereco->id          = $id_endereco;
        $endereco->cep         = $cep;
        $endereco->id_estado   = $id_estado;
        $endereco->id_cidade   = $id_cidade;
        $endereco->bairro      = $bairro;
        $endereco->rua         = $rua;
        $endereco->numero      = $numero;
        $endereco->complemento = $complemento;

        if (array_key_exists(0, $parameters)){
            $id = intval(($parameters[1])); 
        }

        transactionManeger::init();
        transactionManeger::beginTransaction();

        try {
            if ($usuario->set()){
                $endereco->id_usuario = $usuario->id;
                if ($endereco->set(false)){

                    mensagem::setSucesso("Usuário salvo com sucesso");
                    transactionManeger::commit();

                    $login = (new login);
                    if(!$login->getLogged() && $login->login($usuario->cpf_cnpj,$senha)){
                        $this->go("home");
                    }

                    $this->manutencao([$usuario->id],$usuario,$endereco);
                    return;
                }
            }
        } catch (\Exception $e) {
            mensagem::setErro("Erro ao salvar usuário",$e->getMessage());
            transactionManeger::rollback();
            $this->manutencao([$usuario->id],$usuario,$endereco);
            return;
        }

        mensagem::setSucesso(false);
        transactionManeger::rollback();
        $this->manutencao([$usuario->id],$usuario,$endereco);
    }
}

?>