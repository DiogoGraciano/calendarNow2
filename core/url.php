<?php

namespace core;

class url
{    
    public static function getUriPath():string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public static function getUriQuery():string
    {
        return parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY)?:"";
    }

    public static function getUriQueryArray():array
    {
        $result = [];
        $query = url::getUriQuery();

        !$query?:parse_str($query,$result);

        return $result ? $result : [];
    }
    
    public static function getUrlBase():string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return $protocol . "://" . $_SERVER['HTTP_HOST'] . "/";
    }

    public static function getUrlCompleta(){
        return rtrim(self::getUrlBase(),"/").$_SERVER['REQUEST_URI'];
    }

    public static function go(string $caminho):void
    {
        echo '<meta http-equiv="refresh" content="0;url='.self::getUrlBase().$caminho.'">';
        exit;
    }
}    