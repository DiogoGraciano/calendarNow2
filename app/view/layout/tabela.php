<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;

class tabela extends pagina{

    private $columns = [];

    private $rows = [];

    public function __construct()
    {
        $this->setTemplate("tabela.html");
    }

    private function set()
    {
        if($this->rows){
            $i = 1;
            foreach ($this->rows as $row){
                if(is_subclass_of($row,"diogodg\\neoorm\db")){
                    $row = $row->getArrayData();
                }
                foreach ($this->columns as $column){
                    if(array_key_exists($column["coluna"],$row)){
                        $this->tpl->data = $row[$column["coluna"]];
                        $this->tpl->block("BLOCK_DATA");
                        if($i == 1){
                            $this->tpl->columns_name = $column["nome"];
                            $this->tpl->columns_width = $column["width"];
                            $this->tpl->block("BLOCK_COLUMNS");
                        }
                    }
                }
                $i++;
                $this->tpl->block("BLOCK_ROW");
            }
        }

        $this->columns = $this->rows = [];
    }

    public function addColumns(string|int $width,string $nome, string $coluna):tabela
    {
        $this->columns[] = ["nome" => $nome,"width" => $width.'%',"coluna" => $coluna];

        return $this;
    }

    public function addRow(array $row = array()):tabela
    {
        $this->rows[] = $row;

        return $this;
    }

    public function addRows(array $rows = []):tabela
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
        $this->setTemplate("tabela.html");
    }


    public function parse():string
    {
        $this->set();
        $this->rows = [];
        $this->columns = [];
        $tabela = $this->tpl->parse();
        $this->setTemplate("tabela.html");
        return $tabela;
    }

}

?>
