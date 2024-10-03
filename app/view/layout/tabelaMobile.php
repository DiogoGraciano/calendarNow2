<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;

class tabelaMobile extends pagina{

    private array $columns = [];

    private array $rows = [];

    public function __construct()
    {
        $this->setTemplate("tabelaMobile.html");
    }

    private function set():void
    {
        if($this->rows){
            foreach ($this->rows as $row){
                if(is_subclass_of($row,"diogodg\\neoorm\db")){
                    $row = $row->getArrayData();
                }
                foreach ($this->columns as $column){
                    if(array_key_exists($column["coluna"],$row)){
                        $this->tpl->columns_name = $column["nome"];
                        $this->tpl->data = $row[$column["coluna"]];
                    }
                    $this->tpl->block("BLOCK_ROW");
                }
            }
        }

        $this->columns = $this->rows = [];
    }

    public function addColumns(string|int $width,string $nome,string $coluna):tabelaMobile
    {
        $this->columns[] = ["nome" => $nome,"width" => $width.'%',"coluna" => $coluna];

        return $this;
    }

    public function addRow(array $row = []):tabelaMobile
    {
        $this->rows[] = $row;

        return $this;
    }

    public function addRows(array $rows = []):tabelaMobile
    {
        $this->rows = $rows;

        return $this;
    }

    public function show():void
    {
        $this->set();
        $this->rows = [];
        $this->columns = [];
        $this->show();
        $this->setTemplate("tabelaMobile.html");
    }


    public function parse():string
    {
        $this->set();
        $this->rows = [];
        $this->columns = [];
        $tabela = $this->tpl->parse();
        $this->setTemplate("tabelaMobile.html");
        return $tabela;
    }
}
?>
