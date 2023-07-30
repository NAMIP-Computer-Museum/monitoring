<?php
 //Inclure la fonction d'authentification
require_once 'database_method.php';
require_once 'update.php';
// Traitement des informations de connexion envoyées par le formulaire
if (isset($_POST['nom']) && isset($_POST['mdp'])) {
    $nom = $_POST['nom'];
    $mdp = hash('sha256', $_POST['mdp']);

    // Vérification des informations de connexion
    if (connectionAdmin($nom, $mdp)) {
        // Authentification réussie : rediriger l'utilisateur vers une autre page
        header("refresh:1;url=Management.php");
        exit;

    } else {
        echo "Nom d'utilisateur ou mot de passe incorrect.";
      
    }
}

?>

<!DOCTYPE HTML>
<html>
<head>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<title>NAM-IP</title>
<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.stock.min.js"></script>
<script type="text/javascript">
 var jsonData 
 var dataPoints = []
 var unite
 var stockChart
 window.onload = function() {
    jsonData = <?php echo json_encode(graphique()); ?>;
    
    unite = jsonData[0].unite


// 
    CanvasJS.addCultureInfo("fr", {
  months: [
    "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
  ],
  days: [
    "dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"
  ],
  shortMonths: [
    "Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sept", "Oct", "Nov", "Déc"
  ],

  today: "Aujourd'hui",
  decimalSeparator: ",",
  digitGroupSeparator: " "
});
CanvasJS.addColorSet("greenShades",
                [
                //"#2F4F4F",
                "#008080",
                "#2E8B57",
                "#3CB371",
                "#90EE90"                
                ]);


        stockChart = new CanvasJS.StockChart("chartContainer", {
  // Autres options de configuration du graphique...
  //theme: "light2",
  backgroundColor: "#F0EBEA",
  culture: "fr",
  colorSet: "greenShades",
  animationEnabled: true,
  exportEnabled: true,

        title: {
            text: "Musée NAM-IP"},
          
        toolbar: {
          itemBackgroundColor: "#F0EBEA", 
          itemBackgroundColorOnHover: "#2F4F4F"
    },

    subtitles: [
   { text: "Capteur : 2"
   },
  ],

  charts: [
      {  
      toolTip:{
        borderColor: "#2F4F4F",
        animationEnabled: true,  
      },
      axisY: {
        suffix: unite
      },
      
      axisX: {
        valueFormatString:  "HH:mm, DD MMM, YY"
       
      },
      mouseEnabled: true,
       
     

    data: [
      {
        xValueFormatString: "HH:mm, DD/MM ",
        type: "line",
        markerColor: "#2F4F4F",
        markerSize : 6,
        dataPoints: dataPoints,
      },
    ],

  },
    ],
    
    rangeSelector: {
      selectedRangeButtonIndex: 0,
      enabled: true,


      buttonStyle: {
      backgroundColor: "#F0EBEA",
      spacing: 4,
      },
      buttons: [{
        range: 1,            
        rangeType: "day",
        label: "Aujourd'hui" 
      },{
        range: 1, 
        rangeType: "week",
        label: "1 Semaine"
      },{            
        range: 2,
        rangeType: "week",
        label: "2 Semaine"
      },{
        range: 1,            
        rangeType: "month",
        label: "1 Mois" 
      },{            
        rangeType: "all",
        label: "Tout" 
      }
    ],
    },

    navigator: {
      axisX: {
     
  },
      data: [
        {
          dataPoints: dataPoints,

        },
      ],
    },
   
  
  });

  for (var i = 0; i < jsonData.length; i++) {
    var dataPoint = {
      x: new Date(jsonData[i].x),
      y: jsonData[i].y,
    };
    dataPoints.push(dataPoint);
  }

  stockChart.render();


  

};



</script>
</head>
<body>
<style>
 body {
        background-color: #F0EBEA;
        }

    .form-group {
          display: flex;
          justify-content: center;
          align-items: center;

          margin-bottom: 15px;
          margin-top: 15px;
        }
      label {
          margin-inline: 8px;
      }

      button {
          margin-bottom: 8px;
      }
       

    table#tableReadings {
          display: flex;
          justify-content: center;
          align-items: center;
          margin-bottom: 10px;  
          width: 100%;
          border-collapse: collapse;
    }

      table#tableReadings th,
      table#tableReadings td {
            padding: 2px;
            border: 0.5px solid #ccc;
           
        }
   
</style>
<!-- Formulaire de choix de capteur/type de données -->
<?php  $results = allsensor(); ?>
<form id="chartOptions">
  <div class="form-group">

  <label for="id_capteur">Capteur:</label>
  <select name="id_capteur" id="id_capteur"  onchange="loadOptions()"> <!-- Appelle de la fonction qui change dynamiquement les types de données dans le second menu selon le capteur choisi -->
  
  <?php foreach ($results as $result): ?>
      <option value="<?php echo $result['id']; ?>"><?php echo $result['id']; ?></option>
    <?php endforeach; ?>
  </select>


  <label for="type">Type:</label>
  <select name="type" id="type"></select> 
  </div>

  <div class="form-group">
  <button type="button" onclick="getData()">Obtenir les données</button> <!-- Appelle de la fonction de mise à jour des données du graphique -->
  </div>
</form>

<br>
<br>


<div id="chartContainer" style="height: 500px; width: 100%;"></div>
<br>
<br>

<!-- Formulaire de connexion -->
<form method="post">
        <div class="form-group">
          <label for="nom">Nom d'utilisateur :</label>
          <input type="text" name="nom" required><br>
        </div>
        <div class="form-group">
          <label for="mdp">Mot de passe :</label>
          <input type="password" name="mdp" required><br>
        </div>
        <div class="form-group">
          <button type="submit">Se connecter</button>
        </div>
    </form>
   
   <?php   
   // Tableau de rappel des informations des différents capteurs
    echo '<table cellspacing="5" cellpadding="5" id="tableReadings">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Emplacement</th>
                    <th>État</th>
                    <th>Date</th>
                </tr>';

    $result = allsensor();
    
        if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_nom = $row["nom"];
            $row_emplacement = $row["description"];
            $row_état = $row["état_capteur"];
            $row_date = $row["last_installation"];

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
?>


<script>



window.addEventListener("load", function() {
  loadOptions()
});

// Récupération des types de données relatives au capteur choisi
function loadOptions() {
    var id_capteur = document.getElementById("id_capteur").value;
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var type = JSON.parse(this.responseText);
        var typeSelect = document.getElementById("type");
        
      typeSelect.innerHTML = ""; // Effacement de l'ancien menu 
      type.forEach(function(type) {
        typeSelect.innerHTML += "<option value='" + type.id + "'>" + type.type + "</option>"; // "Push" des types de données dans le menu
      });
    }
  };
    xhr.open("GET", "load_options.php?id_capteur=" + id_capteur, true); // requête GET qui indique l'iD du capteur choisi
    xhr.send();
  }

  // Mise à jour des données du graphique 
  function getData() {
    // donnée choisie des menus qui serve à paramétrer la méthode de récupération
  var id_capteur = document.getElementById("id_capteur").value;
  var type = document.getElementById("type").value;
    $.ajax({
      url: "update.php",  //appel du fichier contenant la méthode de récupération des nouvel donnée
      type: "POST",
      data: {
        id_capteur: id_capteur,
        type: type
      },
      success: function(data) {
        // effacer les données actuelles
      dataPoints.splice(0, dataPoints.length);
      jsonData = JSON.parse(data);
       
      unite = jsonData[0].unite;
    // Mise à jour des données comme fait a la création du graphique mais avec les nouvelles donnnées
      for (var i = 0; i < jsonData.length; i++) {
      var dataPoint = {
        x: new Date(jsonData[i].x),
        y: jsonData[i].y
      };
      dataPoints.push(dataPoint);
      
    }
    stockChart.options.charts[0].axisY.suffix = unite; // Mise à jour du suffixe de l'axe Y
    stockChart.options.subtitles[0].text ="Capteur : " + id_capteur; // Mise à jour du numéro du capteur

    stockChart.setOptions(stockChart.options);
    stockChart.render();
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Une erreur est survenue: " + errorThrown);
      }
    });
  };




</script>

</body>
</html>


