<?php 
namespace app\models\main;
use app\models\produto;
use app\models\produtoDocumento;
use app\models\produtoImagem;
use app\db\transactionManeger;
use app\helpers\functions;
use app\helpers\mensagem;
use app\models\abstract\model;

final class produtoModel extends model{

    public static function get(mixed $value = "",string $column = "id",int $limit = 1):array|object
    {
        return (new produto)->get($value,$column,$limit);
    }

    public static function getProdutoImage(mixed $value = "",string $column = "id",int $limit = 1):array|object
    {
        return (new produtoImagem)->get($value,$column,$limit);
    }

    public static function getProdutoDocumento(mixed $value = "",string $column = "id",int $limit = 1):array|object
    {
        return (new produtoDocumento)->get($value,$column,$limit);
    }

    public static function prepareData(array $dados){
        $finalResult = [];
        foreach ($dados as $dado){

            if(is_subclass_of($dado,"app\db\db")){
                $dado = $dado->getArrayData();
            }

            $dado["nome_marca"] = (new marcaModel)::get($dado["id_marca"])->nome;
            $dado["ativo"] = $dado["ativo"]?"Sim":"Não";

            $finalResult[] = $dado;
        }

        return $finalResult;
    }

    public static function getImagensByFilter(int $id_produto,?string $titulo = null,?string $caminho = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        $this = new produtoImagem;

        $this->addFilter("id_produto","=",$id_produto);

        if($titulo){
            $this->addFilter("titulo","LIKE","%".$titulo."%");
        }

        if($caminho){
            $this->addFilter("caminho","LIKE","%".$caminho."%");
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

    public static function getDocumentosByFilter(int $id_produto,?string $titulo = null,?string $caminho = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        $this = new produtoDocumento;

        $this->addFilter("id_produto","=",$id_produto);

        if($titulo){
            $this->addFilter("titulo","LIKE","%".$titulo."%");
        }

        if($caminho){
            $this->addFilter("caminho","LIKE","%".$caminho."%");
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

    public static function getByFilter(?string $nome = null,?int $id_marca = null,?int $ativo = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        $this = new produto;

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($id_marca){
            $this->addFilter("id_marca","=",$id_marca);
        }

        if($ativo || $ativo === 0){
            $this->addFilter("ativo","=",$ativo);
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

    public static function getProdutoAndVinculos(?int $id = null,?string $nome = null,?int $id_marca = null,?int $ativo = null,?int $limit = null,?int $offset = null,$asArray = true):array
    {
        $this = new produto;

        if($id){
            $this->addFilter("produto.id","=",$id);
        }

        if($nome){
            $this->addFilter("produto.nome","LIKE","%".$nome."%");
        }

        if($id_marca){
            $this->addFilter("produto.id_marca","=",$id_marca);
        }

        if($ativo || $ativo === 0){
            $this->addFilter("produto.ativo","=",$ativo);
        }

        $this->addJoin("marca","marca.id","produto.id_marca");
        $this->addJoin("wave","wave.id","marca.id_wave","LEFT");
        $this->addJoin("produto_documento","produto_documento.id_produto","produto.id","LEFT");
        $this->addJoin("produto_imagem","produto_imagem.id_produto","produto.id","LEFT");
        $this->addGroup("produto.id");
        $this->addOrder("marca.ordem,marca.id,produto.ordem","ASC");
        
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

        $results = $this->selectColumns("produto.id",
                                    "produto.nome",
                                    "produto.descricao",
                                    "marca.id as marca_id",
                                    "marca.nome as marca_nome",
                                    "marca.descricao as marca_descricao",
                                    "wave.id_tipo_wave as wave_tipo",
                                    "wave.color as wave_color",
                                    "wave.color_background as wave_color_background",
                                    "wave.largura as wave_largura",
                                    "wave.distancia as wave_distancia"
                                );
        $finalResult = [];
        foreach ($results as $result){
            if($asArray){
                $result["imagens"] = self::getImagensByFilter($result["id"]);
                $result["documentos"] = self::getDocumentosByFilter($result["id"]);
            }
            else{
                $result->imagens = self::getImagensByFilter($result->id,asArray:false);
                $result->documentos = self::getDocumentosByFilter($result->id,asArray:false);
            }
            $finalResult[] = $result;
        }
       
        return $finalResult;
    }

    public static function set(produto $produto):produto|null
    {
        $mensagens = [];

        if($produto->id && !self::get($produto->id)->id)
            $mensagens[] = "Produto não encontrada";

        if(!($produto->nome = htmlspecialchars(trim($produto->nome))))
            $mensagens[] = "Nome é obrigatorio";

        if(!($produto->descricao = htmlspecialchars(trim($produto->descricao))))
            $mensagens[] = "Descrição é obrigatorio";

        if($produto->ordem < 0){
            $mensagens[] = "Ordem invalida"; 
        }

        if($produto->ativo < 0 || $produto->ativo > 1){
            $mensagens[] = "O valor de ativo deve ser entre 1 e 0"; 
        }
        
        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($produto->store()){
            mensagem::setSucesso("Produto salvo com sucesso");
            return $produto;
        }
        
        return null;
    }

    public static function setImagem(produtoImagem $produtoImagem):produtoImagem|null
    {
        $mensagens = [];

        if($produtoImagem->id && !$produtoImagem->get($produtoImagem->id))
            $mensagens[] = "Imagem do Produto não encontrada";

        if(!self::get($produtoImagem->id_produto))
            $mensagens[] = "Produto não encontrada";

        if(!($produtoImagem->titulo = htmlspecialchars(trim($produtoImagem->titulo))))
            $mensagens[] = "Titulo da Imagem é obrigatorio";

        if(!($produtoImagem->caminho = htmlspecialchars(trim($produtoImagem->caminho))))
            $mensagens[] = "Caminho da Imagem é obrigatorio";

        if(($produtoImagem->ordem) < 0){
            $mensagens[] = "Ordem invalida"; 
        }
        
        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($produtoImagem->store()){
            mensagem::setSucesso("Imagem do Produto salvo com sucesso");
            return $produtoImagem;
        }
        
        return null;
    }

    public static function setDocumento(produtoDocumento $produtoDocumento):null|produtoDocumento
    {
        $mensagens = [];

        if($produtoDocumento->id && !$produtoDocumento->get($produtoDocumento->id)->id)
            $mensagens[] = "Documento do Produto não encontrada";

        if(!self::get($produtoDocumento->id_produto)->id)
            $mensagens[] = "Produto não encontrado";

        if(!($produtoDocumento->titulo = htmlspecialchars(trim($produtoDocumento->titulo))))
            $mensagens[] = "Titulo do Documento é obrigatorio";

        if(!($produtoDocumento->caminho = htmlspecialchars(trim($produtoDocumento->caminho))))
            $mensagens[] = "Caminho do Documento é obrigatorio";

        if($produtoDocumento->ordem < 0){
            $mensagens[] = "Ordem invalida"; 
        }
        
        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($produtoDocumento->store()){
            mensagem::setSucesso("Documento do Produto salvo com sucesso");
            return $produtoDocumento;
        }
        
        return null;
    }

    public static function deleteImagem($id_imagem,$id_produto):bool 
    {
        $produtoImagem = (new produtoImagem)->addFilter("id","=",$id_imagem)->addFilter("id_produto","=",$id_produto)->selectAll();
        
        if(isset($produtoImagem[0])){
            $produtoImagem = $produtoImagem[0];
        }
        else{
            mensagem::setErro("Imagem não encontrada");
            return false;
        }

        $caminho = functions::getRaiz().$produtoImagem->caminho;

        if(file_exists($caminho)){
            unlink($caminho);
        }
        else{
            mensagem::setErro("Não foi possivel excluir arquivo: ".$caminho);
            return false;
        }

        if($produtoImagem->delete($produtoImagem->id)){
            mensagem::setSucesso("Imagem deletada com sucesso");
            return true;
        }

        mensagem::setErro("Não foi possivel excluir imagem");
        return false;
    }

    public static function deleteDocumento($id_documento,$id_produto):bool 
    {
        $produtoDocumento = (new produtoDocumento)->addFilter("id","=",$id_documento)->addFilter("id_produto","=",$id_produto)->selectAll();

        if(isset($produtoDocumento[0])){
            $produtoDocumento = $produtoDocumento[0];
        }
        else{
            mensagem::setErro("Documento não encontrado");
            return false;
        }

        $caminho = functions::getRaiz().$produtoDocumento->caminho;

        if(file_exists($caminho)){
            unlink($caminho);
        }
        else{
            mensagem::setErro("Não foi possivel excluir arquivo: ".$caminho);
            return false;
        }

        if($produtoDocumento->delete($produtoDocumento->id)){
            mensagem::setSucesso("Documento deletado com sucesso");
            return true;
        }

        mensagem::setErro("Não foi possivel excluir Documento");
        return false;
    }

    public static function delete(int $id):bool
    {
        try {

            transactionManeger::init();
            transactionManeger::beginTransaction();

            (new produtoImagem)->addFilter("id_produto","=",$id)->deleteByFilter();
            (new produtoDocumento)->addFilter("id_produto","=",$id)->deleteByFilter();

            if((new produto)->delete($id)){
                mensagem::setSucesso("Produto deletada com sucesso");
                transactionManeger::commit();
                return true;
            }

            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }catch (\exception $e){
            mensagem::setErro("Erro ao deletar agenda");
            transactionManeger::rollBack();
            return false;
        }
    }
}