<?php


$servername = "xxx";

// REPLACE with your Database name
$dbname = "xxx";
// REPLACE with Database user
$username = "xxx";
// REPLACE with Database user password
$password = "xxx";

//Récupére les données d'un type de donnée mesurées par un capteur

function graphique($id_capteur = 2 , $type = 1)  {
    global $servername, $username, $password, $dbname;
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    

    $sql = "SELECT donnée.valeur, donnée.date, type_de_donnée.unité
    FROM donnée
    JOIN type_de_donnée ON type_de_donnée.id = donnée.id_type_de_donnée
    WHERE  id_capteur = $id_capteur && id_type_de_donnée = $type 
    ORDER BY donnée.id desc
    ";
    $result = $conn->query($sql);
    
    
    $data = array();
      while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
          "x" => strtotime($row["date"]) * 1000,
          "y" => floatval($row["valeur"]),
          "unite" =>  ($row["unité"]) 
                      );
      } 
      
     return $data; 
    } 
    if (isset($_POST["id_capteur"]) && isset($_POST["type"])) {
       
        
      $id_capteur = $_POST["id_capteur"];
      $type = $_POST["type"];
      $data = graphique($id_capteur, $type);
      echo json_encode($data);
    }
    

?>



