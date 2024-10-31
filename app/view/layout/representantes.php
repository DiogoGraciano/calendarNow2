<?php
namespace app\view\layout;

use app\helpers\functions;
use app\models\main\representanteModel;
use app\view\layout\abstract\layout;

/**
 * Classe footer é responsável por exibir o rodapé de uma página usando um template HTML.
 */
class representantes extends layout
{

    public function __construct(string $action,?int $id_estado = null)
    {
        $this->setTemplate("representantes.html");

        $estados_ativos = [];
        $estados = representanteModel::getUfs();
        if($estados)
            $estados_ativos = $estados;

        $representantes = [];
        if($id_estado)
            $representantes = representanteModel::getByFilter(id_estado:$id_estado,ativo:1);

        foreach ($estados_ativos as $estado)
        {
            $estado_active = $estado["uf"];
            $estado_action = "action_".$estado["uf"];
            $this->tpl->$estado_action = 'hx-target="section.representante" hx-swap="outerHTML" hx-post="'.$action.$estado["uf"].'"';
            $this->tpl->$estado_active = "active";
        }

        foreach ($representantes as $representante){
            $this->tpl->nome = $representante["nome"];
            $contatos = json_decode($representante["contatos"]);
            foreach ($contatos as $contato){
                if(str_contains($contato,"@")){
                    $this->tpl->email = $contato;
                    $this->tpl->block("BLOCK_EMAIL");
                }
                else{
                    $this->tpl->telefone_number = functions::onlynumber($contato);
                    $this->tpl->telefone = $contato;
                    $this->tpl->block("BLOCK_TELEFONE");
                }
            }
            $area_atuacao = json_decode($representante["area_atuacao"]);
            foreach ($area_atuacao as $area){
                $this->tpl->area = $area;
                $this->tpl->block("BLOCK_AREA");
            }
            $this->tpl->block("BLOCK_REPRESENTANTE");
        }
    }
}
