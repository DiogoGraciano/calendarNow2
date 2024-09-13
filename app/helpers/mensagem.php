<?php

namespace app\helpers;
use app\view\layout\abstract\pagina;
use core\session;

class mensagem extends pagina{

    public function __construct(string $localizacao="")
    {
        $this->setTemplate("mensagem.html");

        $mensagens = [];

        $mensagens[] = self::getErro();
        $mensagens[] = self::getSucesso();
        $mensagens[] = self::getMensagem();

        $i = 0;

        foreach ($mensagens as $mensagem){
            foreach ($mensagem as $text){
                if($text){
                    if ($i == 0){
                        $this->tpl->alert = "alert-danger";
                    }elseif ($i == 1){
                        $this->tpl->alert = "alert-success";
                    }else{
                        $this->tpl->alert = "alert-warning";
                    }   
                    $this->tpl->mensagem = $text;
                    $this->tpl->block("BLOCK_MENSAGEM");
                }
            }
            $i++;
        }
        
        if ($localizacao){
            $this->tpl->localizacao = $localizacao;
            $this->tpl->block("BLOCK_BOTAO");
        }
        
        self::setErro("");
        self::setSucesso("");
        self::setMensagem("");
    }

    public static function getErro():array
    {
        return session::get("Erros")?:[];
    }

    public static function setErro(...$erros):void
    {
        session::set("Erros",$erros);
    }

    public static function getMensagem():array
    {
        return session::get("Mensagens")?:[];
    }

    public static function setMensagem(...$Mensagens):void
    {
        session::set("Mensagens",$Mensagens);
    }

    public static function getSucesso():array
    {
        return session::get("Sucessos")?:[];
    }

    public static function setSucesso(...$Sucessos):void
    {
        session::set("Sucessos",$Sucessos);
    }
}
?>
