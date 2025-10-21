<?php
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





// connexion a la base de données postgreSQL
$db_user = $_ENV["POSTGRES_USER"];      
$db_password = $_ENV["POSTGRES_PASSWORD"];
$db_name = $_ENV["POSTGRES_DB"];

$db = new PDO("pgsql:host=db;port=5432;dbname=$db_name", $db_user, $db_password);


$errors = [];
$success = false;

$firstname = "";
$lastname = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = strtolower(trim($_POST['email'])); // efface les espaces et tjr en minuscule
    $password = $_POST['password'];
    $password_verif = $_POST['password_verif'];

    // verifier si l'email n'est pas vide
    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "l'email est pas valide";
    }
    


    // verif si l'email existe déjà
    if (empty($errors)) {
        $email_exist = $db->prepare('SELECT id FROM "user" WHERE email = :email');
        $email_exist->execute(['email' => $email]);
        if ($email_exist->fetch()) { // le fetch va chercher dans la bdd si oui retourne l'id sinon false
            $errors[] = "Cet email est déjà pris";
        }
    }

    // verif si le mdp n'est pas vide et correspond a 12 carac minimum
    if (empty($password)) {
        $errors[] = "Le mdp est obligatoire";
    } elseif (strlen($password) < 12) {
        $errors[] = "Le mdp est trop court (minim 12 carac)";
    }
    
    // Exigences de complexité
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Doit contenir au moins une lettre minuscule";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Doit contenir au moins une lettre majuscule";
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Doit contenir au moins un chiffre";
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Doit contenir au moins un caractère spécial";
    }

    // verif si le mdp et mdp2 sont identiques
    if ($password !== $password_verif) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    // si pas d'erreur, les données sont renvoyées
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $submit = $db->prepare('INSERT INTO "user" (firstname, lastname, email, password) VALUES (:firstname, :lastname, :email, :password)');

        $submit->execute([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password_hash
        ]);


        // $to      = $email;
        // $subject = "Confirmation d'inscription";
        // $message = "Merci" . $firstname . "" . $lastname . "de votre inscription";
        // $headers = array(
        //     'From' => "bll.taoufik@gmaiil.com",
        //     'Reply-To' => "bll.taoufik@gmaiil.com",
        //     'X-Mailer' => 'PHP/' . phpversion()
        // );


        // mail($to, $subject, $message, $headers);


        // met les valeurs a vide quand success
        $success = true;
        $firstname = "";
        $lastname = "";
        $email = "";
    }
}

?>




<h2 style="display:flex;justify-content:center;">FORMULAIRE D'INSCRIPTION PHP - TP1</h2>


<form action="TP1.php" method="POST" style="display:flex;flex-direction:column;padding:20px;gap:5px;">

    <label>Prénom :</label>
    <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>"> <!-- mettre les veleurs necessaire si erreur-->

    <label>Nom :</label>
    <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>">

    <label>*E-mail :</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

    <label>*Mot de passe (8 caractères min) :</label>
    <input type="password" name="password" required>

    <label>*Confirme le mot de passe :</label>
    <input type="password" name="password_verif" required>

    <button type="submit" style="margin-top:10px">ENVOYER</button>
</form>


<?php
    if ($success) {
        echo '<p style="color:green">Inscription ok</p>';
    }
?>


<?php
    // si error n'est pas vide alors il parcours les erreurs et les affiches
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<p style="color:red"> erreur lors de linscription ' . $error . '</p>';
        }
    }
?>
