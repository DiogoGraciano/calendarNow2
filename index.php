<?php
require 'bootstrap.php';

use app\helpers\logger;
use app\helpers\mensagem;
use core\controller;    
use core\method;
use core\parameter;
use core\session;
use core\url;

session::start();

$controller = new Controller;
$controller = $controller->load();

$urlPermitidas = ["/ajax","/usuario/manutencao","/usuario/action/","/empresa/manutencao","/empresa/action/"];
    
if (session::get("user") || in_array(url::getUriPath(),$urlPermitidas)){
    $controller = $controller->load();
}else 
    $controller = $controller->load("login");

session::set("controller_namespace",$namespace); 
session::set("controller",$controller::class);

$method = new Method();
$method = $method->load($controller);

$parameters = new Parameter();
$parameters = $parameters->load($controller);

try{
    
    $controller->$method($parameters);

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