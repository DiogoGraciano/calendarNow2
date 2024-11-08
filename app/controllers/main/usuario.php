<?php

namespace app\controllers\main;

use app\view\layout\form;
use app\view\layout\elements;
use app\helpers\functions;
use app\controllers\abstract\controller;
use app\helpers\email;
use app\helpers\logger;
use app\view\layout\consulta;
use app\helpers\mensagem;
use app\helpers\recapcha;
use app\view\layout\filter;
use diogodg\neoorm\connection;
use app\models\agenda;
use app\models\agendaUsuario;
use app\models\endereco;
use app\models\funcionario;
use app\models\login;
use app\models\usuario as usuarioModel;
use app\models\usuarioBloqueio;
use app\view\layout\email as LayoutEmail;
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
                    "usuario/index",
                    "#consulta-admin",
                    limit:$this->getLimit()))
                ->addFilter($filter)
                 ->show();
    }

    public function bloquear($parameters = []):void
    {
        try{

            connection::beginTransaction();

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
            connection::rollback();
            $this->index();
            return;
        }

        connection::commit();
        $this->index();
    }

    public function desbloquear($parameters = []){
        try{

            connection::beginTransaction();

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
            connection::rollback();
            $this->index();
            return;
        }

        connection::commit();

        $this->index();
    }

    public function manutencao($parameters = [],?usuarioModel $usuario = null,?endereco $endereco = null,int $tipo_usuario = 3){
       $this->formUsuario($parameters,$usuario,$endereco,$tipo_usuario)->show();
    }

    public function formUsuario($parameters = [],?usuarioModel $usuario = null,?endereco $endereco = null,int $tipo_usuario = 3):form
    {
        $id = null;

        if ($parameters && array_key_exists(0, $parameters)){
            if (array_key_exists(0, $parameters)){
                $id = intval($parameters[0]); 
            }
        }
    
        $form = new form($this->url."usuario/action","usuario",hasRecapcha:true);

        $dado = $usuario?:(new usuarioModel)->get($id);

        $elements = new elements();

        $form->setHidden("cd",$dado->id)->setHidden("tipo_usuario",$tipo_usuario);

        $form->setElement($elements->titulo(1,"Cadastro de Usuario"))
        ->setTwoElements(
            $elements->input("nome","Nome",$dado->nome,true),
            $elements->input("cpf_cnpj","CPF/CNPJ",functions::formatCnpjCpf($dado->cpf_cnpj),$tipo_usuario != 4),
            array("nome", "cpf_cnpj")
        );

        $form->setThreeElements(
            $elements->input("email","Email",$dado->email,$tipo_usuario != 4,type:"email"),
            $elements->input("senha","Senha","",$dado->senha?false:$tipo_usuario != 4,type:"password"),
            $elements->input("telefone","Telefone", functions::formatPhone($dado->telefone),$tipo_usuario != 4,type:"tel"),
            array("email", "senha", "telefone")
        );

        // $dadoEndereco = $endereco?:(new endereco)->get($dado->id,"id_usuario");
        // $form->setHidden("id_endereco",$dadoEndereco->id);

        // $elements->setOptions((new ModelsEstado())->getAll(), "id", "nome");
        // $estado = $elements->select("id_estado","Estado",$dadoEndereco->id_estado ?: 24, true);

        // $form->setTwoElements(
        //     $elements->input("cep", "CEP", $dadoEndereco->cep, true),
        //     $estado,
        //     array("cep", "id_estado")
        // );

        // $elements->setOptions((new cidade())->getByEstado($dadoEndereco->id_estado ?: 24), "id", "nome");
        // $form->setTwoElements(
        //     $elements->select("id_cidade","Cidade", $dadoEndereco->id_cidade ?: 4487, true),
        //     $elements->input("bairro", "Bairro", $dadoEndereco->bairro, true),
        //     array("bairro", "id_cidade")
        // );

        // $form->setTwoElements(
        //     $elements->input("rua", "Rua", $dadoEndereco->rua, true),
        //     $elements->input("numero", "Número", $dadoEndereco->numero, true, false, "", "number", "form-control", 'min="0" max="999999"'),
        //     array("rua", "numero")
        // );

        // $form->setElement($elements->textarea("complemento", "Complemento", $dadoEndereco->complemento), "complemento");

        $form->setButton($elements->button("Salvar", "submitUsuario"));
        return $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url."home")."'"));
    }

    public function action(array $parameters = []):void
    {
        $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-usuario-response"));

        if(!$recapcha){
            $this->formUsuario(tipo_usuario:$this->getValue('tipo_usuario')?:3);
            return;
        }

        $usuario               = new usuarioModel;
        $usuario->id           = intval($this->getValue('cd'));
        $usuario->nome         = $this->getValue('nome');
        $usuario->cpf_cnpj     = $this->getValue('cpf_cnpj');
        $senha                 = $this->getValue('senha');
        $usuario->senha        = $senha;
        $usuario->email        = $this->getValue('email');
        $usuario->tipo_usuario = $this->getValue('tipo_usuario')?:3;
        $usuario->telefone     = functions::onlynumber($this->getValue('telefone'));
        if($usuario->tipo_usuario == 4)
            $usuario->ativo        = 1;
        else 
            $usuario->ativo        = 0;

        // $endereco              = new endereco;
        // $endereco->id          = intval($this->getValue('id_endereco'));
        // $endereco->cep         = $this->getValue('cep');
        // $endereco->id_estado   = intval($this->getValue('id_estado'));
        // $endereco->id_cidade   = intval($this->getValue('id_cidade'));
        // $endereco->bairro      = $this->getValue('bairro');
        // $endereco->rua         = $this->getValue('rua');
        // $endereco->numero      = $this->getValue('numero');
        // $endereco->complemento = $this->getValue('complemento');

        
        connection::beginTransaction();

        try {
            if ($usuario->set()){
                // $endereco->id_usuario = $usuario->id;
                // if ($endereco->set(false)){

                    $login = (new login);
                    $user = $login::getLogged();
                   
                    if($user && $user->tipo_usuario != 3){
                        $agendas = (new agenda)->getByUsuario($user->id);

                        foreach ($agendas as $agenda){
                            $agendaUsuario = new agendaUsuario;
                            $agendaUsuario->id_agenda = $agenda->id;
                            $agendaUsuario->id_usuario = $usuario->id;
                            $agendaUsuario->set();
                        }
                    }

                    if(!$user && $login->login($usuario->cpf_cnpj,$senha)){
                        $email = new email;
                        $email->addEmail($usuario->email);
    
                        $redefinir = new LayoutEmail();
                        $redefinir->setEmailBtn("login/confirmacao/".functions::encrypt($usuario->id),"Confirmação de cadastro","Clique no botão a baixo para confirmar seu cadastro, caso não foi você que solicitou essa ação, pode excluir esse email sem problemas.");
    
                        $email->send("Confirmação de cadastro",$redefinir->parse(),true);
                        mensagem::setMensagem("Verifique seu email para confirmação de cadastro");
                        mensagem::setSucesso("Usuário salvo com sucesso");
                        connection::commit();
                        $this->go("home");
                    }

                    mensagem::setSucesso("Usuário salvo com sucesso");
                    connection::commit();
                    $this->manutencao([$usuario->id],$usuario,tipo_usuario:$usuario->tipo_usuario);//,$endereco);
                    return;
                // }
            }
        } catch (\Exception $e) {
            mensagem::setErro("Erro ao salvar usuário");
            logger::error($e->getMessage());
            connection::rollback();
            login::deslogar();
            $this->manutencao([$usuario->id],$usuario,tipo_usuario:$usuario->tipo_usuario);//,$endereco);
            return;
        }

        mensagem::setSucesso(false);
        connection::rollback();
        $this->manutencao([$usuario->id],$usuario,tipo_usuario:$usuario->tipo_usuario);//,$endereco);
    }
}

?>