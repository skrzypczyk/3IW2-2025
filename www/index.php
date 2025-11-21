<?php
namespace App;

/*
 * Faire en sorte que toutes les URLS possibles
 * pointent sur ce fichier index.php
 *
 * http://localhost:8080/toto -> index.php
 * http://localhost:8080/ -> index.php
 * http://localhost:8080/article/312 -> index.php
 */

//Faire en sorte de créer l'instance du controller associé à l'URL
//Appeler l'action associée à l'URL

//ex : /contact
//Appel du controller Base
//Action contact

/*
 *
 * Dans le cas ou l'url ne correspond à aucune route dans routes.yml
 * Afficher une veritable pas 404
 *
 */




spl_autoload_register(function ($class){
        // App\Helper\Errors
        //Créer le code permettant d'aller chercher dans
        //le dossier Helpers la classe qui a engendré une erreur
        $namespaceArray = [
                            "namepace"=> ["App\\Helper\\", "App\\Core\\"],
                            "path"=> ["Helpers/", "Core/"],
                        ];
        $filname = str_ireplace($namespaceArray['namepace'],$namespaceArray['path'], $class  ). ".php";
        if(file_exists($filname)) {
            include $filname;
        }

    }
);





$uri = $_SERVER["REQUEST_URI"];
$uriExploded = explode("?",$uri);
if(is_array($uriExploded)){
    $uri = $uriExploded[0];
}
if(strlen($uri)>1){
    $uri = rtrim($uri, "/");
}

if(!file_exists("routes.yml")){
    die("Le fichier de routing routes.yml n'existe pas");
}
$routes = yaml_parse_file("routes.yml");

//Est-ce que l'uri existe sinon 404
if(empty($routes[$uri])){
    die("Page 404");
}
//Est ce que pour cette URI on a un controller et une action
if(empty($routes[$uri]["controller"]) || empty($routes[$uri]["action"])){
    die("Erreur, il n'y a aucun controller ou aucune action pour cette uri");
}

$controller = $routes[$uri]["controller"];
$action = $routes[$uri]["action"];

//Est ce que le fichier du controller existe
if(!file_exists("Controllers/".$controller.".php")){
    die("Erreur, le fichier du controller n'existe pas");
}

//Inclure le fichier controller
include "Controllers/".$controller.".php";

//Est ce que la class existe ?
$controller = "App\\Controller\\".$controller;
if(!class_exists($controller)){
    die("Erreur, la class controller ".$controller." n'existe pas");
}

//Instance de la classe
$objController = new $controller();

//Est ce que la methode (action) existe ?
if(!method_exists($objController, $action)){
    die("Erreur, l'action ".$action." n'existe pas");
}

//Appel de la méthode
$objController->$action();


