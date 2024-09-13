<?php

namespace app\view\layout;
use app\view\layout\abstract\pagina;

class tabela extends pagina{

    private $columns = [];

    private $rows = [];

    public function set():tabela
    {

        $this->setTemplate("tabela.html");
        
        if($this->rows){
            $i = 1;
            foreach ($this->rows as $row){
                if(is_subclass_of($row,"app\db\db")){
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

        return $this;
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

}

?>
