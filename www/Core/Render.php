<?php

namespace App\Core;

class Render{

    private string $pathView;
    private string $pathTemplate;
    private array $data = [];

    public function __construct($view, $template="frontoffice"){
        $this->setView($view);
        $this->setTemplate($template);
    }

    public function setView($view){
        $this->pathView = "Views/".$view.".php";
    }
    public function setTemplate($template){
        $this->pathTemplate = "Views/Templates/".$template.".php";
    }

    public function check(): bool{
        if(file_exists($this->pathTemplate) && file_exists($this->pathView)){
            return true;
        }
        return false;
    }

    public function assign(string $key, mixed $value):void{
        $this->data[$key]=$value;
    }

    public function render(){
        if($this->check()){
            extract($this->data);
            include $this->pathTemplate;
        }else{
            die("Problème avec le template ou la vue");
        }
    }

}