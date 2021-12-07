<!DOCTYPE html>
<html> 
<head> 

<title>CO2 monitor</title>
<meta charset="UTF-8">
<link rel=icon href=enicon.png>

 <head>
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!-- Load Jquery -->
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <!-- Make the chart -->
    <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.charts.load('current', {'packages':['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
          var jsonData = $.ajax({
              url: "getData.php",
              dataType: "json", // type of data we're expecting from server
              async: false // make true to avoid waiting for the request to be complete
              });


          var data = new google.visualization.DataTable(jsonData.responseText);          
          var chart = new google.visualization.LineChart(document.getElementById('chart'));

        
            
        var options_line = {
          title: 'CO2 last 24h',
          curveType: 'function',
          width: 1200,
          height: 800,
          legend: { position: 'best' }
        };

          chart.draw(data, options_line);
        };

      </script>
  </head>

</head>



<body style="font-family: 'Verdana', 'Geneva', 'Kalimati', sans-serif;">


<?php


include '../db/includedb.php';

$sql = "select timestamp, co2 from sensor_tbl order by timestamp desc limit 50";

$QUERY1  = $databaseConnection->query ($sql); 
$row = $QUERY1->fetch_assoc();

$cas = $row['timestamp'];
$co2 = $row['co2'];

echo "CO2 level<br>";
echo  $cas . " - " . $co2 . "ppm";


$databaseConnection->close();

          
  
?>

  <div id="chart"></div>
</body>
</html>
    