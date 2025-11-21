<?php
namespace App\Controller;
use App\Core\Render;

class Auth
{
    public function login(): void
    {
        new Render("login", "backoffice");
    }

    public function register(): void
    {
        new Render("register", "backoffice");
    }

    public function logout(): void
    {
        die();
    }

}