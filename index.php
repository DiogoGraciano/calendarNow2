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
use core\session;
use core\url;

session::start();

$controller = new Controller;


// $urlPermitidas = ["/ajax","/usuario/manutencao","/usuario/action/","/empresa/manutencao","/empresa/action/"];
    
// if (session::get("user") || in_array(url::getUriPath(),$urlPermitidas)){
    $controller = $controller->load();
// }else 
//     $controller = $controller->load("login");

$namespace = explode("\\",$controller::class);
unset($namespace[array_key_last($namespace)]);
$namespace = implode("\\",$namespace);

session::set("controller_namespace",$namespace); 
session::set("controller",$controller::class);

$method = new Method();
$method = $method->load($controller);

$parameters = new Parameter();
$parameters = $parameters->load($controller);

try{

    $head = new head("Home");
    $head->show();

    $header = new header();
    $header->show();

    $controller->$method($parameters);

    $footer = new footer();
    $footer->show();

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