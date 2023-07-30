<?php


$servername = "xxx";

// REPLACE with your Database name
$dbname = "xxx";
// REPLACE with Database user
$username = "xxx";
// REPLACE with Database user password
$password = "xxx";




    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $id_capteur = $_GET['id_capteur'];

    $sql = "SELECT type_de_donnée.grandeur_physique, type_de_donnée.id 
    FROM type_de_donnée 
    JOIN donnée_par_capteur ON donnée_par_capteur.idtype_de_donnée = type_de_donnée.id
    JOIN type_de_capteur ON type_de_capteur.id = donnée_par_capteur.id_type_capteur
   	JOIN capteur on capteur.modèle = type_de_capteur.id 
    WHERE capteur.id = $id_capteur;
    " ;
    
    $result = $conn->query($sql);
    $options = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $options[] = array(
            'id' => $row['id'],
            'type' => $row['grandeur_physique']
        );
    }
    echo json_encode($options);

    $conn->close();
    
?>