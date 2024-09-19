<?php
namespace core;

use app\controllers\main\error;
use Exception;

class controller{
   
    private string $uri;

    private array $folders = [];

    private string $namespace;

    private string $controller;

    public function __construct()
    {
        $this->uri = url::getUriPath();
        $this->getFolders();
    }

    private function getFolders(){
        $pasta = substr(__DIR__, 0, -5).DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."controllers";
        $arquivos = scandir($pasta);
        foreach ($arquivos as $arquivo) {
            if (!str_contains($arquivo, '.'))
                $this->folders[] = "app\controllers\\".$arquivo;
        }
    }

    public function getNamespace(){
        return $this->namespace;
    }

    public function load($controller=false){

        if ($controller){
            return $this->controllerSet($controller); 
        }
        
        if($this->isHome())
            return $this->controllerHome();
        
        return $this->controllerNotHome();
    }

    private function controllerHome(){
        if (!$this->controllerExist('home'))
            throw new Exception("Essa pagina não existe");
        
        return $this->instatiateController();
    }

    private function controllerSet($controller){
        if (!$this->controllerExist($controller))
            throw new Exception("Essa pagina não existe");
        
        return $this->instatiateController();
    }

    private function controllerNotHome(){
        $controller = $this->getControllerNotHome();

        if (!$this->controllerExist($controller)){
            return (new error);
        }
        
        return $this->instatiateController();
    }

    private function getControllerNotHome(){

        if(substr_count($this->uri,'/') > 1){
            list($controller) = array_values(array_filter(explode('/',$this->uri)));
            return (($controller));
        }
        return ((ltrim($this->uri,"/")));
    }

    private function controllerExist($controller){
        $exists = false;

        foreach ($this->folders as $folder){
            if(class_exists($folder."\\".$controller) && is_subclass_of($folder."\\".$controller,"app\controllers\abstract\controller")){
                $exists = true;
                $this->namespace = $folder;
                $this->controller = $controller; 
            }
        }
        return $exists;
    }
    
    private function instatiateController(){
        $controller = $this->namespace.'\\'.$this->controller;
        return new $controller;
    }

    private function isHome(){
        return ($this->uri == "/");    
    }
}
?>
