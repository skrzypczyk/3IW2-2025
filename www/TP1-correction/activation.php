<?php

if(isset($_GET["email"]) && isset($_GET["token"])){
    $email = $_GET["email"];
    $token = $_GET["token"];

    try{
        $pdo = new PDO("pgsql:host=db;port=5432;dbname=devdb","devuser", "devpass");
    }catch(Exception $e){
        die("Erreur ".$e->getMessage());
    }

    $sql = 'UPDATE "user" SET "is_active"=true, token=null 
              WHERE email=:email AND token=:token';
    $queryPrepared = $pdo->prepare($sql);
    $queryPrepared->execute([
            "email"=>$email,
            "token"=>$token
    ]);
}