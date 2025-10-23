<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// includes (chemins absolus basés sur le dossier du fichier courant)
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

/*
Tout le code doit se faire dans ce fichier PHP

Réalisez un formulaire HTML contenant :
- firstname
- lastname
- email
- pwd
- pwdConfirm

Créer une table "user" dans la base de données, regardez le .env à la racine et faites un build de docker
si vous n'arrivez pas à les récupérer pour qu'il les prenne en compte

Lors de la validation du formulaire vous devez :
- Nettoyer les valeurs, exemple trim sur l'email et lowercase (5 points)
- Attention au mot de passe (3 points)
- Attention à l'unicité de l'email (4 points)
- Vérifier les champs sachant que le prénom et le nom sont facultatifs
- Insérer en BDD avec PDO et des requêtes préparées si tout est OK (4 points)
- Sinon afficher les erreurs et remettre les valeurs pertinantes dans les inputs (4 points)

Le design je m'en fiche mais pas la sécurité

Bonus de 3 points si vous arrivez à envoyer un mail via un compte SMTP de votre choix
pour valider l'adresse email en bdd

Pour le : 22 Octobre 2025 - 8h
M'envoyer un lien par mail de votre repo sur y.skrzypczyk@gmail.com
Objet du mail : TP1 - 2IW3 - Nom Prénom
Si vous ne savez pas mettre votre code sur un repo envoyez moi une archive
*/

/*
 *  Les variables super globales
 *  $_SERVER = array
 *  _ + majuscules
 *  alimenté par le server
 *  accessible partout
 */

$errors = [];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(
        isset($_POST['firstname']) &&
        isset($_POST['lastname']) &&
        !empty($_POST['email']) &&
        !empty($_POST['pwd']) &&
        !empty($_POST['pwdConfirm']) &&
        count($_POST) == 5
    ){
        try{
            $pdo = new PDO("pgsql:host=db;port=5432;dbname=devdb","devuser", "devpass");
        }catch(Exception $e){
            die("Erreur ".$e->getMessage());
        }

        //Nettoyage de la donnée
        $firstname = ucwords(strtolower(trim($_POST['firstname'])));
        $lastname = strtoupper(trim($_POST['lastname']));
        $email = strtolower(trim($_POST['email']));

        if(!empty($firstname) && strlen($firstname)<2){
            $errors[]="Votre prénom doit faire au minimum 2 caractères";
        }

        if(!empty($lastname) && strlen($lastname)<2){
            $errors[]="Votre nom doit faire au minimum 2 caractères";
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors[]="Votre email n'est pas correct";
        }else{
            $sql = 'SELECT "id" FROM "user" WHERE email=:email';
            $queryPrepared = $pdo->prepare($sql);
            $queryPrepared->execute(["email"=>$email]);
            if($queryPrepared->fetch()){
                $errors[]="Votre email existe déjà en bdd";
            }
        }

        if(strlen($_POST["pwd"]) < 8 ||
            !preg_match('/[a-z]/', $_POST["pwd"] ) ||
            !preg_match('/[A-Z]/', $_POST["pwd"]) ||
            !preg_match('/[0-9]/', $_POST["pwd"])
        ){
            $errors[]="Votre mot de passe doit faire au minimum 8 caractères avec min, maj, chiffres";
        }

        if($_POST["pwd"] != $_POST["pwdConfirm"]){
            $errors[]="Votre mot de passe de confirmation ne correspond pas";
        }

        if(empty($errors)){

            $pwdHashed = password_hash($_POST["pwd"], PASSWORD_DEFAULT );


            $token = hash("sha256", bin2hex(random_bytes(32)));

            $sql = 'INSERT INTO "user"("firstname","lastname","email","pwd", "token","date_created")
                    VALUES (:firstname,:lastname,:email,:pwd,:token,\''.date('Y-m-d').'\')';
            $queryPrepared = $pdo->prepare($sql);
            $queryPrepared->execute([
                "firstname"=>$firstname?:null,
                "lastname"=>$lastname?:null,
                "email"=>$email,
                "token"=>$token,
                "pwd"=>$pwdHashed
            ]);

            $activationLink = "http://localhost:8080/TP1-correction/activation.php?email=".$email."&token=".$token;

            //Envoyer un mail de confirmation
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'mailpit';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
                $mail->Port       = 1025;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom('from@example.com', 'Mailer');
                $mail->addAddress($email, $firstname.' '.$lastname);     //Add a recipient

                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Veuillez confirmer votre inscription';
                $mail->Body    = 'Cliquez sur ce lien : <a href="'.$activationLink.'">ici!</a>';
                $mail->AltBody = $activationLink;

                $mail->send();
                echo 'Un mail de confirmation vient de vous être envoyé';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        }



    }else{
        echo "Tentative de XSS";
    }
}


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>TP1 Form</title>
        <meta name="description" content="Ceci est un formulaire d'inscription">
    </head>
    <body>
    <?php

        if(!empty($errors)){
            echo "<pre>";
            print_r($errors);
            echo "</pre>";
        }

    ?>
        <form method="POST" action="TP1-correction.php">
            <input type="text" value="<?= $_POST["firstname"] ?? "" ?>" name="firstname" placeholder="Votre prénom"><br>
            <input type="text" value="<?= $_POST["lastname"] ?? "" ?>" name="lastname" placeholder="Votre nom"><br>
            <input type="email" value="<?= $_POST["email"] ?? "" ?>" required name="email" placeholder="Votre email"><br>
            <input type="password" required name="pwd" placeholder="Votre mot de passe"><br>
            <input type="password" required name="pwdConfirm" placeholder="Confirmation du mot de passe"><br>
            <input type="submit" value="S'inscrire">
        </form>
    </body>
</html>




















