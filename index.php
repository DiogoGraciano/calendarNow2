<?php
require 'bootstrap.php';

use app\helpers\logger;
use app\helpers\mensagem;
use app\view\layout\footer;
use app\view\layout\head;
use app\view\layout\header;
use core\controller;    
use core\method;
use core\parameter;
use core\request;
use core\session;
use core\url;

session::start();

$urlPermitidas = [
    "/ajax",
    "/login/esqueci",
    "/login/sendEsqueci",
    "/login/resetar",
    "/login/actionResetar",
    "/login/action",
    "/login/usuario",
    "/usuario/action",
    "/login/empresa",
    "/empresa/action",
    "/login/confirmacao"
];

$controller = new Controller;

$user = session::get("user");

if(!$user && str_contains(url::getUriPath(),"encontrar/action/")){
    mensagem::setMensagem("Faça Login ou cadastra-se para cadastrar seu agendamento");
    session::set("url_encontrar",url::getUriPath());
}

if($user && $encontrar = session::get("url_encontrar")){
    session::set("url_encontrar",false);
    url::go(ltrim($encontrar,"/"));
}

$uri = explode("/",url::getUriPath());

$path = "/";

if(isset($uri[1])){
    $path .= $uri[1];
}
if(isset($uri[2])){
    $path .= "/".$uri[2];
}

if ($user || in_array($path,$urlPermitidas)){
    $controller = $controller->load();
}else{
    $head = new head("Login");
    $head->show();
    $controller = $controller->load("login");
    $controller->index();
    $footer = new footer();
    $footer->show();
    die;
} 

$namespace = explode("\\",$controller::class);
$controllerName = $namespace[array_key_last($namespace)];
unset($namespace[array_key_last($namespace)]);
$namespace = implode("\\",$namespace);

session::set("controller_namespace",$namespace); 
session::set("controller",$controller::class);

$method = new Method();
$method = $method->load($controller);

$parameters = new Parameter();
$parameters = $parameters->load($controller);

try{

    if(request::isXmlHttpRequest() || isset(request::getAllHeaders()["Hx-Request"])){
        $controller->$method($parameters);
    }
    else{
        if(!$controller::methods || !array_key_exists($method,$controller::methods)){
            if($controller::addHead){
                $head = new head($controller::headTitle);
                $head->show();
            }
    
            if($controller::addHeader){
                $header = new header();
                $header->show();
            }
    
            $controller->$method($parameters);
                
            if($controller::addFooter){
                $footer = new footer();
                $footer->show();
            }
        }
        else{
            $methodConfig = $controller::methods[$method];

            if(isset($methodConfig["addHead"]) && $methodConfig["addHead"]){
                $head = new head($controller::headTitle);
                $head->show();
            }
    
            if(isset($methodConfig["addHeader"]) && $methodConfig["addHeader"]){
                $header = new header();
                $header->show();
            }
    
            $controller->$method($parameters);
              
            if(isset($methodConfig["addFooter"]) && $methodConfig["addFooter"]){
                $footer = new footer();
                $footer->show();
            }
        }
    }

}catch(Exception $e){
    $local = $controller::class.'->'.$method.'('.trim(preg_replace( "/\r|\n/","",print_r($parameters,true))).')';
    logger::error($local.' Error: '.$e->getMessage().' Trace: '.$e->getTraceAsString());
    mensagem::setErro("Ocorreu um erro inesperado");

    if(session::get("error") != $local){
        session::set("error",$local);
        echo '<meta http-equiv="refresh" content="0">';
    }
}
?>