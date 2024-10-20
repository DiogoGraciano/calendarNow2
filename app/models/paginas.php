<?php
namespace app\models;

use app\helpers\mensagem;
use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

class paginas extends model {
    public const table = "paginas";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de Quem Somos"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID"))
                ->addColumn((new column("titulo","VARCHAR",250))->isNotNull()->setComment("Titulo"))
                ->addColumn((new column("descricao","VARCHAR",10000))->isNotNull()->setComment("Descrição"))
                ->addColumn((new column("pagina","VARCHAR",50))->isNotNull()->setComment("pagina"))
                ->addColumn((new column("efeito","VARCHAR",50))->isNotNull()->setComment("Efeito"))
                ->addColumn((new column("ordem","INT"))->isNotNull()->setComment("Ordem"));
    }

    public function getByFilter(?string $titulo = null,?string $pagina = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        if($titulo){
            $this->addFilter("titulo","LIKE","%".$titulo."%");
        }

        if($pagina){
            $this->addFilter("pagina","LIKE","%".$pagina."%");
        }

        $this->addOrder("ordem","ASC");

        if($limit && $offset){
            self::setLastCount($this);
            $this->addLimit($limit);
            $this->addOffset($offset);
        }
        elseif($limit){
            self::setLastCount($this);
            $this->addLimit($limit);
        }

        if($asArray){
            $this->asArray();
        }

        $result = $this->selectAll();
        
        if($result)
            return $result;
        
        return [];
    }

    public function set():self|null
    {
        $mensagens = [];

        if($this->id && !(new self)->get($this->id)->id)
            $mensagens[] = "pagina não encontrada";

        if(!($this->titulo = htmlspecialchars(trim($this->titulo))))
            $mensagens[] = "Titulo é obrigatorio";

        if(!($this->descricao = strip_tags($this->descricao,["html","body","b","br","em","hr","i","li","ol","p","s","span","table","tr","td","u","ul","h","img","video"])))
            $mensagens[] = "Descrição é obrigatorio";

        if(!($this->efeito = htmlspecialchars(trim($this->efeito))))
            $mensagens[] = "Efeito é obrigatorio";

        if($this->ordem < 0){
            $mensagens[] = "Ordem invalida"; 
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Salvo com sucesso");
            return $this;
        }
        
        return null;
    }

}