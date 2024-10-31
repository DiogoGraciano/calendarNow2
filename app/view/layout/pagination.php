<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;
use core\request;
use core\url;

class pagination extends layout{

    private int $page;
    private int $totalQuery;
    private int $limit;
    private string $action;
    private array $query;

    public function __construct(int $totalQuery,string $action,string $target = "#consulta-admin",int $limit = 20,?int $page = null)
    {
        $this->setTemplate("pagination.html");
        $this->query = url::getUriQueryArray();
        $queryPage = isset($this->query["page"])?intval($this->query["page"]):1;
        $this->page = $page?:$queryPage;
        $this->totalQuery = $totalQuery;
        $this->limit = $limit?:20;
        $this->action = $action;
        $this->tpl->target = $target;
    }

    public function parse():string
    {
        if($this->totalQuery < $this->limit){
            return "";
        }

        if($this->page > 1){
            $this->tpl->link_anterior = $this->action.$this->getQuery($this->page-1);
            $this->tpl->block("BLOCK_ANTERIOR");
        }

        $i = $this->page-3;
        $b = ($i*-1)+1;
        $i = $i<=0?1:$i;
        for ($i; $i < $this->page+3+$b; $i++)
        { 
            if($this->page == $i){
                $this->tpl->link_page = $this->action.$this->getQuery($this->page);
                $this->tpl->class_page = "active";
                $this->tpl->page = $this->page;
            }
            else{
                $this->tpl->link_page = $this->action.$this->getQuery($i);
                $this->tpl->class_page = "";
                $this->tpl->page = $i;
            }

            $this->tpl->block("BLOCK_PAGINA");

            if($this->getMaxPage() == $i){
                break;
            }
        }
        
        if($this->page < $this->getMaxPage()){
            $this->tpl->link_proximo = $this->action.$this->getQuery($this->page+1);
            $this->tpl->block("BLOCK_PROXIMO");
        }

        return $this->tpl->parse();
    }

    public function getMaxPage():int
    {
        return ceil($this->totalQuery/$this->limit);
    }

    public function getQuery($page):string
    {
        $this->query["page"] = $page;

        return "?".http_build_query($this->query);
    }
}