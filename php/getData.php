<?php

include '../db/includedb.php';

$data0 = "{
  \"cols\": [
        {\"id\":\"\",\"label\":\"Topping\",\"pattern\":\"\",\"type\":\"string\"},
        {\"id\":\"\",\"label\":\"Slices\",\"pattern\":\"\",\"type\":\"number\"}
      ],
  \"rows\": [
        {\"c\":[{\"v\":\"MXXhrooms\",\"f\":null},{\"v\":3,\"f\":null}]},
        {\"c\":[{\"v\":\"Onions\",\"f\":null},{\"v\":1,\"f\":null}]},
        {\"c\":[{\"v\":\"Olives\",\"f\":null},{\"v\":1,\"f\":null}]},
        {\"c\":[{\"v\":\"Zucchini\",\"f\":null},{\"v\":1,\"f\":null}]},
        {\"c\":[{\"v\":\"Pepperoni\",\"f\":null},{\"v\":2,\"f\":null}]}
      ]
}";

$data1 = "{
\"cols\":[
    {\"label\":\"Day\",\"type\":\"number\"},
    {\"label\":\"Guardians\",\"type\":\"number\"},
    {\"label\":\"Avengers\",\"type\":\"number\"},
    {\"label\":\"Transformers\",\"type\":\"number\"}
],
\"rows\":[
    {\"c\":[{\"v\":1},{\"v\":37.8},{\"v\":80.8},{\"v\":41.8}]},
    {\"c\":[{\"v\":2},{\"v\":30.9},{\"v\":69.5},{\"v\":32.4}]},
    {\"c\":[{\"v\":3},{\"v\":25.4},{\"v\":57.0},{\"v\":25.7}]},
    {\"c\":[{\"v\":4},{\"v\":11.7},{\"v\":18.8},{\"v\":10.5}]},
    {\"c\":[{\"v\":5},{\"v\":11.9},{\"v\":17.6},{\"v\":10.4}]},
    {\"c\":[{\"v\":6},{\"v\":8.8},{\"v\":13.6},{\"v\":7.7}]},
    {\"c\":[{\"v\":7},{\"v\":7.6},{\"v\":12.3},{\"v\":9.6}]},
    {\"c\":[{\"v\":8},{\"v\":12.3},{\"v\":29.2},{\"v\":10.6}]},
    {\"c\":[{\"v\":9},{\"v\":16.9},{\"v\":42.9},{\"v\":14.8}]},
    {\"c\":[{\"v\":10},{\"v\":12.8},{\"v\":30.9},{\"v\":11.6}]},
    {\"c\":[{\"v\":11},{\"v\":5.3},{\"v\":7.9},{\"v\":4.7}]},
    {\"c\":[{\"v\":12},{\"v\":6.6},{\"v\":8.4},{\"v\":5.2}]},
    {\"c\":[{\"v\":13},{\"v\":4.8},{\"v\":6.3},{\"v\":3.6}]},
    {\"c\":[{\"v\":14},{\"v\":4.2},{\"v\":6.2},{\"v\":3.4}]}
]}";

header('Content-Type: application/json');
//echo json_encode($data);


$data = "{
  \"cols\": [
        {\"id\":\"\",\"label\":\"Time\",\"pattern\":\"\",\"type\":\"string\"},
        {\"id\":\"\",\"label\":\"CO2\",\"pattern\":\"\",\"type\":\"number\"}
      ],";

$data = $data . "\"rows\":[";

////////////////////////

$sql = "select timestamp, co2 from sensor_tbl where co2>100 and timestamp > now() - INTERVAL 1 DAY order by timestamp asc";

$QUERY1  = $databaseConnection->query ($sql);

$first = 1;
//$casi = 0;
while ($row = $QUERY1->fetch_assoc())
{

    if ($first == 1)
    {
       $first = 0;                    
    }
     else
    {
      $data = $data . ", ";      
    }
    
  
    
    $cas = $row['timestamp'];
    $co2 = $row['co2'];
    //$data = $data ."{\"c\":[{\"v\":" . $casi . "},{\"v\":".$co2."}]}";
    $data = $data ."{\"c\":[{\"v\":\"" . $cas . "\"},{\"v\":".$co2."}]}";
        
 
    //$casi = $casi + 1;
    
}
 
$databaseConnection->close();

//$data = $data ."{\"c\":[{\"v\":1},{\"v\":37.8}]},";

//////////////////
// konec
$data = $data . "]}";

echo $data;


?>