<?php require_once 'database_method.php'; 
if (isset($_POST['alarme'])) {
  $alarme = htmlspecialchars($_POST['alarme']);

  // Vérification des informations de connexion
  if (nouvelalarme($alarme)) {
   
    header("Location: Management.php");
    exit(); 
    
    } 
}

if (isset($_POST['grandeur']) && isset($_POST['unité'])) {
  $grandeur = htmlspecialchars($_POST['grandeur']) ;
  $unité = htmlspecialchars($_POST['unité']) ;
  // Vérification des informations de connexion
  if (nouveltypededonnée($grandeur, $unité)) {
    
    header("Location: Management.php");
    exit(); 

  } 
} 

if (isset($_POST['description']) && isset($_POST['type'])) {
  $desciption = htmlspecialchars($_POST['description']) ;
  $type = htmlspecialchars($_POST['type']) ;
  // Vérification des informations de connexion
  if (nouvelemplacement($desciption, $type)) {
    
    header("Location: Management.php");
    exit(); 

  } 
}

if (isset($_POST['id_capteur']) && isset($_POST['emplacement'])) {
  $capteur = htmlspecialchars($_POST['id_capteur']) ;
  $emplacement = htmlspecialchars($_POST['emplacement']) ;
  // Vérification des informations de connexion
  if (nouvelinstallation($capteur, $emplacement)) {
    
    header("Location: Management.php");
    exit(); 

  } 
}

if (isset($_POST['modèle'])) {
  $capteur = htmlspecialchars($_POST['modèle']) ;
  // Vérification des informations de connexion
  if (nouveaucapteur($capteur)) {
    
    header("Location: Management.php");
    exit(); 
  }
 }

if (isset($_POST['ncapteur']) && isset($_POST['grandeur'])) {
  $ncapteur = htmlspecialchars($_POST['ncapteur']);
  $grandeur = $_POST['grandeur'];

  if(nouveaumodèle($ncapteur, $grandeur)){
    
    header("Location: Management.php");
    exit(); 

  }

  
}



?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

  <title>Management</title>

<style>
    body {
        background-color: #F0EBEA;
    }

    .container {
      display: flex;
      flex-wrap: wrap;
    }
    .section {
      flex: 0 0 50%;
      padding: 30px;
      box-sizing: border-box;
  
    }
    .dropdown {
      margin-bottom: 20px;
    }
    .table {
    border-collapse: collapse;
    margin-bottom: 20px;
    } 

    .table th {
  
    background-color: #ccc;
    font-weight: bold;
    }

    .table td {

    border: 1px solid #ddd;
    padding: 5px;
    }
  </style>
</head>
<body>
  
  <div class="container">
    <div class="section">
      <h2>Capteur</h2>
      <?php $results = alltypesensor(); ?>

  <form id="chartOptions" method="post">
    <label for="modèle">Nouveau capteur:</label>
      <select name="modèle" id="modèle"  >
  
        <?php foreach ($results as $result): ?>
        <option value="<?php echo $result['id']; ?>"><?php echo $result['nom']; ?>
        </option>
        <?php endforeach; ?>
      </select>
   
  
  <button type="submit">ajouter</button>
</form>

<?php
    echo '<h3>Capteurs</h3>';
    echo '<table cellspacing="5" cellpadding="5" id="tableReadings " class="table">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>emplacement</th>
                    <th>état</th>
                    <th>date</th>
                </tr>';

    $result = allsensor();
    
        if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_nom = $row["nom"];
            $row_emplacement = $row["description"];
            $row_état = $row["état_capteur"];
            $row_date = date('H:i:s d-m-Y', strtotime($row["last_installation"]));

            echo '<tr>
                    <td>' . $row_id . '</td>
                    <td>' . $row_nom . '</td>
                    <td>' . $row_emplacement . '</td>
                    <td>' . $row_état . '</td>
                    <td>' . $row_date . '</td>
                  </tr>';
        }
        echo '</table>';
        $result->free();
    }
    echo '<h3>Modèle de capteur</h3>';
    echo '<table cellspacing="5" cellpadding="5" id="tableReadings" class="table">
                <tr>
                    <th>Modèle</th>
                    <th>Type de donnée</th>
      
                </tr>';

?>



<?php $results = typededonnée(); ?>

<form id="chartOptions" method="post">
<div class="form-group">
          <label for="ncapteur">Nouveau modèle de capteur :</label> <br>
          <input type="text" name="ncapteur" required><br>
        </div>
  <label for="grandeur">Grandeur physique :</label> <br>
 
  <?php foreach ($results as $result): ?>
    <label for="<?php echo $result['id']; ?>">
      <input type="checkbox" name="grandeur[]" value="<?php echo $result['id']; ?>" id="<?php echo $result['id']; ?>">
      <?php echo $result['grandeur_physique']; ?>
    </label><br>
  <?php endforeach; ?>

  <button type="submit">ajouter</button>
</form>
<br><br>
   <?php
      
   

    $result = alltypesensor();
    
        if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_nom = $row["nom"];
            $row_types = $row["types"];
           

            echo '<tr>
                    <td>' . $row_nom . '</td>
                    <td>' . $row_types . '</td>
                    
                  </tr>';
        }
        echo '</table>';
        $result->free();
    }
    
    ?>
    </div>
    <div class="section">
      <h2>Emplacement</h2>
<form method="post">
        <div class="form-group">
          <label for="description">Ajout d'un nouvel emplacement :</label>
          <input type="text" name="description" required><br>
        </div>
        <div class="form-group">
          <label for="type">Type :</label>
          <input type="text" name="type" required><br>
        </div>
        <div class="form-group">
          <button type="submit">Ajouter</button>
        </div>
      </form>

<?php
     

  echo '<h3>Emplacements</h3>';
      echo '<table cellspacing="5" cellpadding="5" id="tableReadings" class="table">
          <tr>
              <th>Description</th>
              <th>Type</th>
              
          </tr>';
                $result = emplacement();

          if ($result) {
          while ($row = $result->fetch_assoc()) {
              $row_id_capteur = $row["type"];
              $row_description = $row["description"];
      
              echo '<tr>
                      <td>' . $row_description . '</td>
                      <td>' . $row_id_capteur . '</td>
                    
                    </tr>';
          }
          echo '</table>';
          $result->free();
      }

      echo '<h3>Installations</h3>';
          echo '<table cellspacing="5" cellpadding="5" id="tableReadings" class="table">
              <tr>
                  <th>Capteur</th>
                  <th>Emplacement</th>
                  <th>Date</th>
              </tr>';

              
    ?>
   
<?php $results = allsensor(); 
       $results2 = emplacement();?>

  <form id="chartOptions" method="post">
    <label for="id_capteur">Capteur:</label>
      <select name="id_capteur" id="id_capteur"  >
  
        <?php foreach ($results as $result): ?>
        <option value="<?php echo $result['id']; ?>"><?php echo $result['id']; ?>
        </option>
        <?php endforeach; ?>
      </select><br>

    <label for="emplacement">Emplacement:</label>
      <select name="emplacement" id="emplacement">
        <?php foreach ($results2 as $result2): ?>
        <option value="<?php echo $result2['id']; ?>"><?php echo $result2['description']; ?>
        </option>
        <?php endforeach; ?>
      </select>

  <button type="submit">ajouter</button>
</form>
<br><br>
  </div>
  


     
      <?php

    $result = installation();
    
        if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_id_capteur = $row["id_capteur"];
            $row_description = $row["description"];
            $row_date_installation = $row["date_installation"];

            echo '<tr>
                    <td>' . $row_id_capteur . '</td>
                    <td>' . $row_description . '</td>
                    <td>' . $row_date_installation . '</td>
                  </tr>';
        }
        echo '</table>';
        $result->free();
    }
    

      ?>
    </div>
    <div class="section">
      <h2>Type de donnée</h2>
      <form method="post">
        <div class="form-group">
          <label for="grandeur">Ajout d'une nouvelle grandeur physisque :</label>
          <input type="text" name="grandeur" required><br>
        </div>
        <div class="form-group">
          <label for="unité">Unité :</label>
          <input type="text" name="unité" required><br>
        </div>
        <div class="form-group">
          <button type="submit">Ajouter</button>
        </div>
      </form>
      
    
      <?php
        echo '<h3>Type de donnée</h3>';
        echo '<table cellspacing="5" cellpadding="5" id="tableReadings" class="table">
            <tr>
                <th>Grandeur physique</th>
                <th>Unité</th>
                
            </tr>';
                  $result = typededonnée();

            if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row_grandeur_physique = $row["grandeur_physique"];
                $row_unité = $row["unité"];
        
                echo '<tr>
                        <td>' . $row_grandeur_physique . '</td>
                        <td>' . $row_unité . '</td>
                      
                      </tr>';
            }
            echo '</table>';
            $result->free();
        }




      ?>
      </div>
      <div class="section">
      <h2>Alarme</h2>
      <form method="post">
        <div class="form-group">
          <label for="alarme">Ajout d'un nouveau type d'alarme :</label>
          <input type="text" name="alarme" required><br>
        </div>
        <div class="form-group">
          <button type="submit">Ajouter</button>
        </div>
      </form>
      
    
<?php
      echo '<h3>Types</h3>';
        echo '<table cellspacing="5" cellpadding="5" id="tableReadings" class="table">
            <tr>
                <th>Type</th>
                
            </tr>';
                  $result = typealarme();

            if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row_type = $row["type"];
        
                echo '<tr>
                        <td>' . $row_type . '</td>
                      
                      </tr>';
            }
            echo '</table>';
            $result->free();
        }
      
      echo '<h3>Alarmes</h3>';
        echo '<table cellspacing="5" cellpadding="5" id="tableReadings" class="table">
            <tr>
                
                <th>Date</th>
                <th>Capteur</th>
                <th>Type</th>
            </tr>';
                  $result = alarme();

            if ($result) {
            while ($row = $result->fetch_assoc()) {
                
                $row_date_alarme = $row["date_alarme"];
                $row_id_capteur = $row["id_capteur"];
                $row_type = $row["type"];
        
                echo '<tr>
                      
                        <td>' . $row_date_alarme . '</td>
                        <td>' . $row_id_capteur . '</td>
                        <td>' . $row_type . '</td>
                      
                      </tr>';
            }
            echo '</table>';
            $result->free();
        }
        










?>
</div>
      </div>
     
     
