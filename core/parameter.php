<?php

namespace core;

class parameter{

    private string $uri;

    public function __construct()
    {
        $this->uri = url::getUriPath();
    }

    public function load():array
    {
        if (substr_count($this->uri,'/') > 2){
            $parameter = array_values(array_filter(explode('/',$this->uri)));

            return array_slice($parameter, 2);
        }
        return [];
    }
}

?>
