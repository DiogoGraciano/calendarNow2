<?php 
namespace app\controllers\main;
use app\view\layout\form;
use app\controllers\abstract\controller;
use app\view\layout\elements;
use app\helpers\mensagem;
use app\models\calendarNow as ModelsCalendarNow;
use app\models\cidade;
use app\models\estado as ModelsEstado;

final class calendarNow extends controller{

    public const headTitle = "CalendarNow";

    public function index(array $parameters = [],?ModelsCalendarNow $calendarNow = null):void
    {
        $form = new form($this->url."calendarNow/action/");

        $elements = new elements;
        
        $dado = $calendarNow?:(new ModelsCalendarNow)->get(1);

        $elements->setOptions((new ModelsEstado)->getAll(), "id", "nome");
        $estado = $elements->select("id_estado","Estado", $dado->id_estado ?: 24);

        $cidades = (new cidade)->getByEstado($dado->id_estado ?: 24);

        foreach ($cidades as $cidade){
            $elements->addOption($cidade->id, $cidade->nome);
        }

        $cidade = $elements->select("id_cidade","Cidade",$dado->id_cidade ?: 4487);

        $elements->addOption("tls","TLS");
        $elements->addOption("ssl","SSL");
        
        $form->setElement($elements->titulo(1,"CalendarNow","fw-normal text-title mt-2 mb-3"))
            ->setElement($elements->titulo(2,"Informações Gerais","fw-normal text-title mt-2 mb-2"))
            ->setElement($elements->input("nome","Nome:",$dado->nome,max:150))
            ->setElement($elements->textarea("horario_atendimento","Horario Atendimento:",$dado->horario_atendimento,max:250))
            ->setElement($elements->titulo(2,"Informações de Contato","fw-normal text-title mt-2 mb-2"))
            ->setTwoElements($elements->input("telefone","Telefone:",$dado->telefone,max:15,type:"tel"),
                            $elements->input("celular","Celular:",$dado->celular,max:15,type:"tel"))
            ->setTwoElements($elements->input("contato_sac","Contato do Sac (Telefone ou Email):",$dado->contato_sac,max:150),
                            $elements->input("contato_comercial","Contato do Comercial (Telefone ou Email):",$dado->contato_comercial,max:150))
            ->setTwoElements($elements->input("contato_email","Contato do Email:",$dado->contato_email,max:150,type:"email"),
                            "")
            ->setElement($elements->titulo(2,"Endereço","fw-normal text-title mt-2 mb-2"))
            ->setTwoElements($elements->input("latitude","Latitude:",$dado->latitude,type:"number",min:-180,max:180,step:0.000001),
                            $elements->input("longitude","Longitude:",$dado->longitude,type:"number",min:-180,max:180,step:0.000001)
            )
            ->setTwoElements(
                $elements->input("cep", "CEP", $dado->cep,max:9),
                $estado,
                array("cep", "id_estado")
            )
            ->setTwoElements(
                $cidade,
                $elements->input("bairro", "Bairro", $dado->bairro,max:300),
                array("bairro", "id_cidade")
            )
            ->setTwoElements(
                $elements->input("rua", "Rua", $dado->rua,max:300),
                $elements->input("numero", "Número", $dado->numero,type:"number",min:1,max:999999),
                array("rua", "numero")
            )
            ->setElement($elements->textarea("complemento", "Complemento", $dado->complemento,max:300))
            ->setElement($elements->titulo(2,"Meta Dados","fw-normal text-title mt-2 mb-2"))
            ->setElement($elements->textarea("keywords", "Keywords", $dado->keywords,max:300))
            ->setElement($elements->textarea("descricao", "Descrição", $dado->descricao,max:300))
            ->setElement($elements->titulo(2,"SMTP","fw-normal text-title mt-2 mb-3"))
            ->setTwoElements(
                $elements->input("smtp_servidor", "Servidor", $dado->smtp_servidor,max:150),
                $elements->input("smtp_port", "Porta", $dado->smtp_port,type:"number",max:9999,min:1)
            )
            ->setThreeElements(
                $elements->input("smtp_usuario", "Usuario", $dado->smtp_usuario,max:150),
                $elements->input("smtp_senha", "Senha", $dado->smtp_senha,max:150),
                $elements->select("smtp_encryption", "Tipo de Criptrografia", $dado->smtp_encryption),
            )
            ->setElement($elements->titulo(2,"ReCAPTCHA","fw-normal text-title mt-2 mb-3"))
            ->setThreeElements(
                $elements->input("recaptcha_site_key", "Site Key", $dado->recaptcha_site_key,max:150),
                $elements->input("recaptcha_secret_key", "Secret Key", $dado->recaptcha_secret_key,max:150),
                $elements->input("recaptcha_minimal_score", "Score Minimo", $dado->recaptcha_minimal_score?:6,type:"number",max:10,min:1)
            )
            ->setButton($elements->button("Salvar","submit"))
            ->setButton($elements->button("Voltar","voltar","button","btn btn-primary w-100 btn-block","location.href='".$this->url."banner'"))
            ->show();
    }

    public function action(array $parameters = []):void
    {
        $calendarNow = new ModelsCalendarNow;
    
        $calendarNow->id                  = 1;
        $calendarNow->telefone            = $this->getValue('telefone');
        $calendarNow->celular             = $this->getValue('celular'); 
        $calendarNow->latitude            = $this->getValue("latitude");
        $calendarNow->longitude           = $this->getValue("longitude");
        $calendarNow->horario_atendimento = $this->getValue('horario_atendimento');
        $calendarNow->contato_sac         = $this->getValue('contato_sac');
        $calendarNow->contato_email       = $this->getValue('contato_email');
        $calendarNow->contato_comercial   = $this->getValue("contato_comercial");
        $calendarNow->cep                 = $this->getValue('cep');
        $calendarNow->id_estado           = intval($this->getValue('id_estado'));
        $calendarNow->id_cidade           = intval($this->getValue('id_cidade'));
        $calendarNow->bairro              = $this->getValue('bairro');
        $calendarNow->rua                 = $this->getValue('rua');
        $calendarNow->numero              = $this->getValue('numero');
        $calendarNow->complemento         = $this->getValue('complemento');
        $calendarNow->nome                = $this->getValue('nome');
        $calendarNow->keywords            = $this->getValue('keywords');
        $calendarNow->descricao           = $this->getValue('descricao');
        $calendarNow->smtp_servidor       = $this->getValue('smtp_servidor');
        $calendarNow->smtp_port           = $this->getValue('smtp_port');
        $calendarNow->smtp_usuario        = $this->getValue('smtp_usuario');
        $calendarNow->smtp_senha          = $this->getValue('smtp_senha');
        $calendarNow->smtp_encryption     = $this->getValue('smtp_encryption');
        $calendarNow->recaptcha_site_key   = $this->getValue('recaptcha_site_key');
        $calendarNow->recaptcha_secret_key = $this->getValue('recaptcha_secret_key');
        $calendarNow->recaptcha_minimal_score = $this->getValue('recaptcha_minimal_score');

        if ($calendarNow->set()){ 
            $this->index($parameters,$calendarNow);
            return;
        }

        mensagem::setSucesso(false);
        $this->index($parameters,$calendarNow);
        return;
    }
}