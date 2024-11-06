<?php

namespace app\models;

use app\helpers\functions;
use app\helpers\mensagem;
use diogodg\neoorm\abstract\model;
use diogodg\neoorm\db;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

class dre extends model
{
    public const table = "dre";

    public function __construct() {
        parent::__construct(self::table,$this::class);
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de DRE"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID self"))
                ->addColumn((new column("codigo","INT"))->isNotNull()->setComment("Codigo Conta"))
                ->addColumn((new column("descricao ","VARCHAR",100))->isNotNull()->setComment("Descrição da Conta"))
                ->addColumn((new column("id_empresa","INT"))->isForeingKey(empresa::table())->setComment("ID da empresa"));      
    }

    public function getByFilter(?int $id_empresa = null,?string $descricao = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array
    {
        $this->addFilter("id_empresa","is",null,startGroupFilter:true);

        if($id_empresa){
            $this->addFilter("id_empresa","=",$id_empresa,db::OR,endGroupFilter:true);
        }

        if($descricao){
            $this->addFilter("descricao","LIKE","%".$descricao."%");
        }

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

        return $this->selectAll();
    }

    public function set():self|null
    {
        $mensagens = [];

        if($this->codigo < 1){
            $mensagens[] = "Codigo invalido";
        }

        if(!$this->descricao = htmlspecialchars(trim($this->descricao))){
            $mensagens[] = "Descrição invalido";
        }

        if($this->id_empresa && !((new empresa)->get($this->id_empresa)->id))
            $mensagens[] = "Empresa não encontrada"; 

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Conta salva com sucesso");
            return $this;
        }
            
        return null;
    }

    public static function prepareList(array $dres):array
    {
        $dresFinal = [];
        foreach ($dres as $dre){

            if(is_subclass_of($dre,"diogodg\\neoorm\db")){
                $dre = $dre->getArrayData();
            }

            $dre["codigo"] = functions::formatDre($dre["codigo"]);

            $dresFinal[] = $dre; 
        }

        return $dresFinal;
    }


    public static function seed(){
        $menu = new self;
        if(!$menu->addLimit(1)->selectColumns("id")){
            $menu->codigo = 1;
            $menu->descricao = "ATIVO";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11;
            $menu->descricao = "ATIVO CIRCULANTE";
            $menu->store();

            $menu = new self;
            $menu->codigo = 111;
            $menu->descricao = "Caixa e Equivalentes de Caixa";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11101;
            $menu->descricao = "Caixa";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11102;
            $menu->descricao = "Bancos Conta Movimento";
            $menu->store();

            $menu = new self;
            $menu->codigo = 112;
            $menu->descricao = "Contas a Receber";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11201;
            $menu->descricao = "Clientes";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11202;
            $menu->descricao = "(-) Perdas Estimadas com Créditos de Liquidação Duvidosa";
            $menu->store();

            $menu = new self;
            $menu->codigo = 113;
            $menu->descricao = "Estoque";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11301;
            $menu->descricao = "Mercadorias";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11302;
            $menu->descricao = "Produtos Acabados";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11303;
            $menu->descricao = "Insumos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 114;
            $menu->descricao = "Outros Créditos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11401;
            $menu->descricao = "Títulos a Receber";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11402;
            $menu->descricao = "Impostos a Recuperar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 11403;
            $menu->descricao = "Outros Valores a Receber";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12;
            $menu->descricao = "ATIVO NÃO CIRCULANTE";
            $menu->store();

            $menu = new self;
            $menu->codigo = 121;
            $menu->descricao = "Realizável a Longo Prazo";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12101;
            $menu->descricao = "Contas a Receber";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12102;
            $menu->descricao = "(-) Perdas Estimadas com Créditos de Liquidação Duvidosa";
            $menu->store();

            $menu = new self;
            $menu->codigo = 122;
            $menu->descricao = "Investimentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12201;
            $menu->descricao = "Participações Societárias";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12202;
            $menu->descricao = "Outros Investimentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 123;
            $menu->descricao = "Imobilizado";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12301;
            $menu->descricao = "Terrenos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12302;
            $menu->descricao = "Edificações";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12303;
            $menu->descricao = "Máquinas e Equipamentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12304;
            $menu->descricao = "Veículos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12305;
            $menu->descricao = "Móveis e Utensílios";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12306;
            $menu->descricao = "(-) Depreciação Acumulada";
            $menu->store();

            $menu = new self;
            $menu->codigo = 124;
            $menu->descricao = "Intangível";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12401;
            $menu->descricao = "Softwares";
            $menu->store();

            $menu = new self;
            $menu->codigo = 12402;
            $menu->descricao = "(-) Amortização Acumulada";
            $menu->store();

            $menu = new self;
            $menu->codigo = 2;
            $menu->descricao = "PASSIVO E PATRIMÔNIO LÍQUIDO";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21;
            $menu->descricao = "PASSIVO CIRCULANTE";
            $menu->store();

            $menu = new self;
            $menu->codigo = 211;
            $menu->descricao = "Fornecedores Nacionais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21101;
            $menu->descricao = "Fornecedor";
            $menu->store();

            $menu = new self;
            $menu->codigo = 212;
            $menu->descricao = "Empréstimos e Financiamentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21201;
            $menu->descricao = "Empréstimos Bancários";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21202;
            $menu->descricao = "Financiamentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 213;
            $menu->descricao = "Obrigações Fiscais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21301;
            $menu->descricao = "SIMPLES NACIONAL";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21302;
            $menu->descricao = "ICMS a Recolher";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21303;
            $menu->descricao = "ISSQN a Recolher";
            $menu->store();

            $menu = new self;
            $menu->codigo = 214;
            $menu->descricao = "Obrigações Trabalhistas e Sociais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21401;
            $menu->descricao = "Salários a Pagar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21402;
            $menu->descricao = "FGTS a Recolher";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21403;
            $menu->descricao = "INSS dos Segurados a Recolher";
            $menu->store();

            $menu = new self;
            $menu->codigo = 215;
            $menu->descricao = "Contas a Pagar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21501;
            $menu->descricao = "Telefone a Pagar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21502;
            $menu->descricao = "Energia a Pagar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21503;
            $menu->descricao = "Aluguel a Pagar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 216;
            $menu->descricao = "Provisões";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21601;
            $menu->descricao = "Provisão de Férias";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21602;
            $menu->descricao = "Provisão de 13º Salário";
            $menu->store();

            $menu = new self;
            $menu->codigo = 21603;
            $menu->descricao = "Provisão de Encargos Sociais sobre Férias e 13º Salário";
            $menu->store();

            $menu = new self;
            $menu->codigo = 22;
            $menu->descricao = "PASSIVO NÃO CIRCULANTE";
            $menu->store();

            $menu = new self;
            $menu->codigo = 221;
            $menu->descricao = "Financiamentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 22101;
            $menu->descricao = "Financiamentos Banco A";
            $menu->store();

            $menu = new self;
            $menu->codigo = 222;
            $menu->descricao = "Outras Contas a Pagar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 22201;
            $menu->descricao = "Empréstimos de Sócios";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23;
            $menu->descricao = "PATRIMÔNIO LÍQUIDO";
            $menu->store();

            $menu = new self;
            $menu->codigo = 231;
            $menu->descricao = "Capital Social";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23101;
            $menu->descricao = "Capital Subscrito";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23102;
            $menu->descricao = "(-) Capital a Integralizar";
            $menu->store();

            $menu = new self;
            $menu->codigo = 232;
            $menu->descricao = "Reservas";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23201;
            $menu->descricao = "Reservas de Capital";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23202;
            $menu->descricao = "Reservas de Lucros";
            $menu->store();

            $menu = new self;
            $menu->codigo = 233;
            $menu->descricao = "Lucros/Prejuízos Acumulados";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23301;
            $menu->descricao = "Lucros Acumulados";
            $menu->store();

            $menu = new self;
            $menu->codigo = 23302;
            $menu->descricao = "(-) Prejuízos Acumulados";
            $menu->store();

            $menu = new self;
            $menu->codigo = 3;
            $menu->descricao = "RECEITAS, CUSTOS E DESPESAS (CONTAS DE RESULTADO)";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31;
            $menu->descricao = "RECEITAS";
            $menu->store();

            $menu = new self;
            $menu->codigo = 311;
            $menu->descricao = "Receitas de Venda";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31101;
            $menu->descricao = "Venda de Produtos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31102;
            $menu->descricao = "Venda de Mercadorias";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31103;
            $menu->descricao = "Venda de Serviços";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31104;
            $menu->descricao = "(-) Deduções de Tributos, Abatimentos e Devoluções";
            $menu->store();

            $menu = new self;
            $menu->codigo = 312;
            $menu->descricao = "Receitas Financeiras";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31201;
            $menu->descricao = "Receitas de Aplicações Financeiras";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31202;
            $menu->descricao = "Juros Ativos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 313;
            $menu->descricao = "Outras Receitas Operacionais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31301;
            $menu->descricao = "Receitas de Venda de Imobilizado";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31302;
            $menu->descricao = "Receitas de Venda de Investimentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 31303;
            $menu->descricao = "Outras Receitas";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32;
            $menu->descricao = "CUSTOS E DESPESAS";
            $menu->store();

            $menu = new self;
            $menu->codigo = 321;
            $menu->descricao = "Custos dos Produtos, Mercadorias e Serviços Vendidos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32101;
            $menu->descricao = "Custos dos Insumos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32102;
            $menu->descricao = "Custos da Mão de Obra";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32103;
            $menu->descricao = "Outros Custos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 322;
            $menu->descricao = "Despesas Operacionais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32201;
            $menu->descricao = "Despesas Administrativas";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32202;
            $menu->descricao = "Despesas com Vendas";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32203;
            $menu->descricao = "Outras Despesas Gerais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 323;
            $menu->descricao = "Despesas Financeiras";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32301;
            $menu->descricao = "Juros Passivos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32302;
            $menu->descricao = "Outras Despesas Financeiras";
            $menu->store();

            $menu = new self;
            $menu->codigo = 324;
            $menu->descricao = "Outras Despesas Operacionais";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32401;
            $menu->descricao = "Despesas com Baixa de Imobilizado";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32402;
            $menu->descricao = "Despesas com Baixa de Investimentos";
            $menu->store();

            $menu = new self;
            $menu->codigo = 32403;
            $menu->descricao = "Outras Despesas";
            $menu->store();
        }
    }
}
