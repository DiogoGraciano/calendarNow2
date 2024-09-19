<?php

namespace core;

class request
{
    private readonly array $get;
    private readonly array $post;
    private readonly array $cookie;
    private readonly array $session;
    private readonly array $server;
    private readonly array $files;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->session = $_SESSION;
        $this->server = $_SESSION;
        $this->files = $_FILES;
    }

    public static function getValue($var){
        if (isset($_POST[$var]))
            return $_POST[$var];
        elseif (isset($_GET[$var]))
            return $_GET[$var];
        elseif (session::get($var))
            return session::get($var);
        elseif (isset($_COOKIE[$var]))
            return $_COOKIE[$var];
        elseif (isset($_SERVER[$var]))
            return $_SERVER[$var];
        else
            return null;
    }

    public static function isXmlHttpRequest():bool
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : "";
        return (strtolower($isAjax) === 'xmlhttprequest');
    }

    public static function getAllHeaders():array
    {
        $headers = []; 
        foreach ($_SERVER as $name => $value) 
        { 
            if (substr($name, 0, 5) == 'HTTP_') 
            { 
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
            } 
        } 
        return $headers; 
    }

    public function get(){
        return $this->get;
    }

    public function post(){
        return $this->post;
    }

    public function cookie(){
        return $this->cookie;
    }

    public function session(){
        return $this->session;
    }

    public function server(){
        return $this->server;
    }

    public function files(){
        return $this->files;
    }
}