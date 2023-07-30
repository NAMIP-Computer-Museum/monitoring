
<?php

//Ce code est appelé par l'ESP32 afin d'insérer les données dans la base de données grâce à la méthode "insertReadings"

  include_once('database_method.php');

 //Permets de faire correspondre avec l'esp32
  $api_key_value = "xxx";
  $api_key= $temperature = $humidity = $id = "";
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);
    if($api_key == $api_key_value) {
      $temperature = test_input($_POST["temperature"]);
      $humidity = test_input($_POST["humidity"]);
      $id = test_input($_POST["id"]);
      
//appel de la méthode d'insertion avec les paramètre 
      $result = insertReading($temperature, $humidity, $id);
      echo $result;
    }
    else {
      echo "Wrong API Key provided.";
    }
  }
  else {
    echo "No data posted with HTTP POST.";
  }

  //Filtre les caractères indésirables
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }