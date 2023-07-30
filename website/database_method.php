
<?php
//Page contenant la majorité des méthodes d'insert, de select et d'update de la base de données

  $servername = "xxx";

  // REPLACE with your Database name
  $dbname = "xxx";
  // REPLACE with Database user
  $username = "xxx";
  // REPLACE with Database user password
  $password = "xxx";

  //paramètre d'alarme
  $tmin = "13";
  $tmax = "30";  
  $hmin = "30";    
  $hmax = "70"; 
 
//Méthode d'ajout de données des capteurs appelée par insert_temperature.php
  function insertReading($temperature, $humidity, $id) {
    global $servername, $username, $password, $dbname,
    $tmin,$tmax,$hmin,$hmax;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql1 = "INSERT INTO donnée (valeur, emplacement, id_type_de_donnée, id_capteur)
    VALUES ( '" . $temperature . "', (SELECT installation.id_emplacement FROM installation WHERE installation.id_capteur = $id ORDER BY installation.id DESC LIMIT 1) , (SELECT id FROM type_de_donnée WHERE grandeur_physique = 'température'), (SELECT id FROM capteur WHERE id = $id))";

    $sql2 = "INSERT INTO donnée (valeur, emplacement, id_type_de_donnée, id_capteur)
    VALUES ( '" . $humidity . "', (SELECT installation.id_emplacement FROM installation WHERE installation.id_capteur = $id ORDER BY installation.id DESC LIMIT 1) , (SELECT id FROM type_de_donnée WHERE grandeur_physique = 'humidité'), (SELECT id FROM capteur WHERE id = $id))";
 
    if ($conn->multi_query($sql1 .";" . $sql2) === TRUE) {
     
     //Si l'insertion de données c'est bien passé, on update l'état du capteur qui vient d'envoyer des données à l'état "allumé"

      $conn->close();

      $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

      $sqlStatus = "SELECT état_capteur FROM capteur WHERE id = $id";
      $resultStatus = $conn->query($sqlStatus);
       $rowStatus = $resultStatus->fetch_assoc();
      $currentStatus = $rowStatus["état_capteur"];

      if ($currentStatus == 0) {
          // Mettre à jour l'état du capteur à 1
          $sqlUpdateStatus = "UPDATE capteur SET état_capteur = 1 WHERE id = $id";
          $conn->query($sqlUpdateStatus);
      }

  // Si des données ont dépassé un seuil, on envoie un mail avec l'erreur mentionné
      
    $message = '';

      if ($temperature > $tmax) {
          $message .= "le capteur : $id a une température trop élevée.\n";
          ajoutalarme($id, 1);
      }

      if ($temperature < $tmin) {
        $message .= "le capteur : $id a une température trop basse.\n";
        ajoutalarme($id, 2);
      }

      if ($humidity > $hmax) {
      $message .= "le capteur : $id a une humidité trop élevée.\n";
      ajoutalarme($id, 3);
      }

      if ($humidity < $hmin) {
          $message .= "le capteur : $id a une humidité trop basse.\n";
          ajoutalarme($id, 4);
      }

      // Envoyer un e-mail si les conditions sont remplies
      if ($message !== '') {
          $to = 'xxx@xxx.com';
          $subject = 'Alerte de capteur';
          $headers = 'From: xxx@xxx.com' . "\r\n" .
              'Reply-To: xxx@xxx.com' . "\r\n" .
              'X-Mailer: PHP/' . phpversion();

          mail($to, $subject, $message, $headers);
          $conn->close();
      }
     
      checkInactiveSensors();
      
    }
    else {
      return "Error: " . $sql1 . $sql2 ."<br>" . $conn->error;
    }
    $conn->close();
    }
  

    //ajoute une itération d'alarme dans la table alarme de la base de donnée 
    function ajoutalarme($capteur,$type) {
      global $servername, $username, $password, $dbname;
    
      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);
      // Check connection
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }
    
      $sql = "INSERT INTO alarme (id_capteur, id_type_alarme) 
      VALUES ('$capteur', $type)";
    
    if ($conn->query($sql) === TRUE) {
    return "nouvel alarme ajouter";
    }
    else {
    
    //return "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    }









//Cette méthode regarde les capteurs qui sont censé être allumées. 
//Si Ces capteurs n'ont pas envoyé de données depuis plus d'un jour cela envoie une alarme et un mail. 
//Et pour finir elle change l'état des capteurs en "éteint"

  function checkInactiveSensors() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sqlActiveSensors = "SELECT id FROM capteur WHERE état_capteur = 1";
    $resultActiveSensors = $conn->query($sqlActiveSensors);

    if ($resultActiveSensors->num_rows > 0) {
        while ($rowActiveSensor = $resultActiveSensors->fetch_assoc()) {
            $idCapteur = $rowActiveSensor['id'];
            
            // Vérifier si le capteur a envoyé des données récemment
            $sqlData = "SELECT MAX(date) AS last_data FROM donnée WHERE id_capteur = $idCapteur";
            $resultData = $conn->query($sqlData);
            $rowData = $resultData->fetch_assoc();
            $lastData = strtotime($rowData['last_data']);
            $now = time();
            $diff = $now - $lastData;
            
            // Vérifier si le capteur n'a pas envoyé de données depuis plus d'un jour (86400 secondes)
            if ($diff > 86400) {
              
                

                $conn->close();

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
                // Check connection
                if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
                }

                // Vérifier si le capteur était précédemment à l'état 1
                $sqlStatus = "SELECT état_capteur FROM capteur WHERE id = $idCapteur";
                $resultStatus = $conn->query($sqlStatus);
                $rowStatus = $resultStatus->fetch_assoc();
                if ($rowStatus['état_capteur'] == 1) {
                  ajoutalarme($idCapteur, 5);
                    // Envoyer une alarme par e-mail
                    $to = 'namip.museum@gmail.com';
                    $subject = 'Alarme capteur - Pas de donnee depuis 1 jour';
                    $message = 'Le capteur ' . $idCapteur . ' n\'a pas envoye de donnee depuis 1 jour.';
                    $headers = 'From: xxx@xxx.com' . "\r\n" .
                        'Reply-To: xxx@xxx.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
    
                    mail($to, $subject, $message, $headers);
                }
                $conn->close();
                
                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
                // Check connection
                if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
                }
                // Mettre à jour l'état_capteur à 0
               $sqlUpdate = "UPDATE capteur SET état_capteur = 0 WHERE id = $idCapteur";
               $conn->query($sqlUpdate);

            }
        }
    }
}






// Permets de se connecter à la page de management en comparant le nom et le mot de passe 
  function connectionAdmin($nom, $mdp) {
    global $servername, $username, $password, $dbname;


    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Vérification des informations de connexion dans la base de données
    $stmt = $conn->prepare( "SELECT * FROM utilisateur WHERE nom = ?");
    $stmt->bind_param('s', $nom);
    $stmt->execute();
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Vérification du mot de passe
        if (hash_equals($row['mdp'], $mdp)) {
        
          // Fermeture de la connexion MySQL
         $stmt->close();
         $conn->close();

          return true;
         }
    } 
    // Fermeture de la connexion MySQL
    $stmt->close();
    $conn->close();
    // Retourne false si l'authentification a échoué
    return false;
}



//Cette méthode récupère tous les capteurs, leur modèle, leur état, le dernier endroit où ils sont installés et de quand dates leur dernière installation

  function allsensor() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT c.id, t.nom, e.description, c.état_capteur, i.last_installation
    FROM capteur AS c
    JOIN type_de_capteur AS t ON t.id = c.modèle
    LEFT JOIN (
        SELECT id_capteur, MAX(date_installation) AS last_installation
        FROM installation
        GROUP BY id_capteur
    ) AS i ON i.id_capteur = c.id
    LEFT JOIN installation AS inst ON inst.id_capteur = c.id AND inst.date_installation = i.last_installation
    LEFT JOIN emplacement AS e ON e.id = inst.id_emplacement
    ORDER BY i.last_installation DESC";

    if ($result = $conn->query($sql)) {
      return $result;
    }
    
    $conn->close();
  
  }

//Cette méthode récupère tous les modèles de capteurs et les types de données qu'ils mesurent dans une seule variable.

  function alltypesensor() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT type_de_capteur.id, type_de_capteur.nom, GROUP_CONCAT(type_de_donnée.grandeur_physique SEPARATOR ', ') AS types
    FROM type_de_capteur 
    JOIN donnée_par_capteur ON donnée_par_capteur.id_type_capteur = type_de_capteur.id
    JOIN type_de_donnée ON type_de_donnée.id = donnée_par_capteur.idtype_de_donnée
    GROUP BY type_de_capteur.nom, type_de_capteur.id
     " ;
    if ($result = $conn->query($sql)) {
      return $result;
    }
    
    $conn->close();
  
  }

  //Cette méthode récupère toutes les installations des capteurs réalisés
  function installation() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT  installation.id_capteur, emplacement.description, emplacement.type, DATE_FORMAT(installation.date_installation,'%H:%i:%s %d-%m-%Y' ) AS date_installation
    FROM installation
    JOIN emplacement ON emplacement.id = installation.id_emplacement
    ORDER BY installation.date_installation DESC
     " ;
    if ($result = $conn->query($sql)) {
      return $result;
    }
    
    $conn->close();
  
  }
//Cette fonction référence tous les emplacements
  function emplacement() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT description, type, id
    FROM emplacement
     " ;
    if ($result = $conn->query($sql)) {
      return $result;
    }
    
    $conn->close();
  
  }
  //Cette fonction référence tous les types de données
  function typededonnée() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id,grandeur_physique, unité
    FROM type_de_donnée
     " ;
    if ($result = $conn->query($sql)) {
      return $result;
    }
    
    $conn->close();
  
  }
  //Cette fonction référence tous les types d'alarmes
  function typealarme() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT type
    FROM type_alarme
     " ;
    if ($result = $conn->query($sql)) {
      return $result;
    }
    
    $conn->close();
  
  }

  //Cette fonction référence toutes les alarmes
  function alarme() {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT DATE_FORMAT(alarme.date,'%H:%i:%s %d-%m-%Y') AS date_alarme, alarme.id_capteur, type_alarme.type
    FROM alarme
    JOIN type_alarme ON type_alarme.id = alarme.id_type_alarme
    ORDER BY date_alarme DESC limit 10 
     " ;
    if ($result = $conn->query($sql)) {
      return $result;
    }
    else{
      echo "Erreur lors de l'exécution de la requête : " . mysqli_error($conn);


    }
    $conn->close();
  
  }

 //Cette fonction permet d'ajouter une alarme dans la base de données 

  function nouvelalarme($alarme) {
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO type_alarme (type) VALUES (?)";
    
    $stmt = $conn->prepare($sql);

        // Vérification de la préparation de la requête
    if ($stmt === false) {
        die("Error: " . $conn->error);
    }

    
    $stmt->bind_param('s', $alarme);

if ($stmt->execute()) {
  $stmt->close();
  $conn->close();
  return "Nouvelle alarme ajoutée";
}
$stmt->close();
$conn->close();
}

//Cette fonction permet d'ajouter un nouveau de type de donnée dans la base de données

function nouveltypededonnée($grandeur, $unité) {
  global $servername, $username, $password, $dbname;

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO type_de_donnée (grandeur_physique, unité) VALUES (?, ?)";

  $stmt = $conn->prepare($sql);

    // Vérification de la préparation de la requête
    if ($stmt === false) {
      die("Error: " . $conn->error);
  }

  $stmt->bind_param('ss', $grandeur, $unité);


  if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    return "Nouveau type de donnée ajouté";
  }
    $stmt->close();
    $conn->close();
}

//Cette fonction permet d'ajouter un nouvel emplacement dans la base de données
function nouvelemplacement($desciption, $type) {
  global $servername, $username, $password, $dbname;

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO emplacement (description, type) VALUES (?,?)";

$stmt = $conn->prepare($sql);

// Vérification de la préparation de la requête
if ($stmt === false) {
  die("Error: " . $conn->error);
}

$stmt->bind_param('ss', $desciption, $type);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
  return "Nouvel type de donnée ajouter";
}
    $stmt->close();
    $conn->close();
}

//Cette fonction permet d'ajouter une nouvelle installation dans la base de données
function nouvelinstallation($capteur, $emplacement) {
  global $servername, $username, $password, $dbname;

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO installation (id_capteur, id_emplacement)
  VALUES ('" . $capteur . "', '" . $emplacement . "')";

if ($conn->query($sql) === TRUE) {
return "Nouvel type de donnée ajouter";
}
else {

//return "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
}

//Cette fonction permet d'ajouter un nouveau capteur dans la base de données
function nouveaucapteur($capteur) {
  global $servername, $username, $password, $dbname;

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO capteur (modèle)
  VALUES ('" . $capteur . "')";

if ($conn->query($sql) === TRUE) {
return "Nouvel type de donnée ajouter";
}
else {

//return "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
}

//Cette fonction permet d'ajouter un nouveau modéle de capteur dans la base de données
function nouveaumodèle($ncapteur, $grandeur) {
  global $servername, $username, $password, $dbname;

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
   // Insérer le nouveau modèle de capteur
  $stmt = $conn->prepare("INSERT INTO type_de_capteur (nom) VALUES (?)");
  $stmt->bind_param('s', $ncapteur);
  $stmt->execute();
 
   // Récupérer l'ID du modèle de capteur inséré
  $idCapteur = $stmt->insert_id;

  $nextElement = current($grandeur);
   // Insérer une ligne pour chaque élément de grandeur[]
    foreach ($grandeur as $i) {
   
      // Vérifier si l'élément suivant existe
      if ($nextElement !== false) {
        $sql2 = "INSERT INTO donnée_par_capteur (id_type_capteur, idtype_de_donnée) VALUES ('$idCapteur', '$nextElement')";
  
        if ($conn->query($sql2) === TRUE) {

          $nextElement = next($grandeur); // Récupérer l'élément suivant
        }         
        } 
    }
    return TRUE;
}

  

?>
