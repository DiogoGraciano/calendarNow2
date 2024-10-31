<?php

namespace app\view\layout;
use app\view\layout\abstract\layout;
use app\helpers\mensagem;
use core\url;

class agenda extends layout{

    public function __construct()
    {
        $this->setTemplate("agenda.html");

        $mensagem = new mensagem;
        $this->tpl->mensagem = $mensagem->parse();
    }
    
    public function set(string $action,array|string $eventos,string $days_on=",seg,ter,qua,qui,sex,",string $initial_time = "08:00",string $final_time = "19:00",int $slot_duration = 30):self
    {
        $this->tpl->action = $action;
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->initial_time = $initial_time;

        $time = explode(":",$final_time);
        $lastTime = intval($time[1]);
        if ($slot_duration > $lastTime)
            $lastTime = "00";
        
        $this->tpl->final_time = $time[0].":".$lastTime;

        if ($slot_duration >= 60)
            $this->tpl->slot_duration = "01:00";
        else 
            $this->tpl->slot_duration = "00:".$slot_duration;

        $days_on = str_replace("dom",0,$days_on);
        $days_on = str_replace("seg",1,$days_on);
        $days_on = str_replace("ter",2,$days_on);
        $days_on = str_replace("qua",3,$days_on);
        $days_on = str_replace("qui",4,$days_on);
        $days_on = str_replace("sex",5,$days_on);
        $days_on = str_replace("sab",6,$days_on);

        $days = explode(",",$days_on);

        $daysOffFinal = [];

        foreach ($days as $key => $value){
            $alldays = [0,1,2,3,4,5,6];
            if (!in_array($value,$alldays))
                $daysOffFinal[] = $alldays[$key];
        } 

        if(is_array($eventos))
            $this->tpl->events = json_encode(array_merge($eventos));
        else
            $this->tpl->events = '"/'.$eventos.'"';

        $this->tpl->days_off = json_encode($daysOffFinal);

        $date = new \DateTimeImmutable();
        $this->tpl->initial_date = $date->format(\DateTimeInterface::ATOM);

        $this->tpl->block("BLOCK_CALENDARIO");
  
        return $this;
    }

    public function addFilter(Filter $filter):agenda
    {
        $this->tpl->filter = $filter->parse();
        return $this;
    }

    public function addButton($button):agenda
    {
        $this->tpl->button = $button;
        $this->tpl->block("BLOCK_BUTTON");
        return $this;
    }
}
