<?php
namespace App\Controller;

use App\Core\Render;
use App\Helper\Errors;

class Base
{
    public function index(): void
    {
        $pseudo = "Prof";

        $render = new Render("home", "frontoffice");
        $render->assign("pseudo", $pseudo);
        $render->assign("name", "SKRZYPCZYK");
        $render->render();
    }

    public function contact(): void
    {
        new Render("contact");
    }

    public function portfolio(): void
    {
        new Render("portfolio");
    }


}