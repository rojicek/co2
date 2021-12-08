<?php

// jen pho zapis do databaze z GET

include '../db/includedb.php';
   
if (isset($_GET['temperature'])) 
{
    $temperature = $_GET['temperature'];
}
 else 
{
  // Fallback behaviour goes here
  echo "no temp";
  $temperature = -1;
}

if (isset($_GET['co2'])) 
{
    $co2 = $_GET['co2'];
}
 else 
{
  // Fallback behaviour goes here
  echo "no co2";
  $co2 = -1;
}
 
 
 if (isset($_GET['humidity'])) 
{
    $humidity = $_GET['humidity'];
}
 else 
{
  // Fallback behaviour goes here
  echo "no humidity";
  $humidity = -1;
}
 

$sql = "INSERT INTO sensor_tbl(timestamp, temperature, co2, humidity) VALUES (\"" . date("Y-m-d H:i:s") . "\", " . $temperature . ", ". $co2 .  ", " . $humidity . ")";
//echo $sql; 

if ($databaseConnection->query($sql) === TRUE) 
{
  echo "New record created successfully";
} 
else 
{
  echo "Error: " . $sql . "<br>" . $databaseConnection->error;
}


$databaseConnection->close();
    
?>