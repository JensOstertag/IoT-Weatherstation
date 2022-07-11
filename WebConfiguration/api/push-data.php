<?php
  require("../connection.php"); // $con = new mysqli(host, username, password, database)
  if($con->connect_error) {
    $return = array(
      "code" => 500,
      "content" => "Internal Error: Database Connection Failed"
    );
    header('Content-Type: application/json');
    exit(json_encode($return));
  }

  if(isset($_GET["key"]) && isset($_GET["auth"]) && isset($_GET["temp"]) && isset($_GET["humi"]) && isset($_GET["pres"])) {
    $key = $_GET["key"];
    $password = md5($_GET["auth"]);
    $date = date("Y-m-d H:i:s");
    $temperature = $_GET["temp"];
    $humidity = $_GET["humi"];
    $pressure = $_GET["pres"];

    $searchStation = $con->prepare("SELECT StationID FROM WeatherStations WHERE StationKey = ? AND StationPassword = ?");
    $searchStation->bind_param("ss", $key, $password);
    $searchStation->execute();
    $searchStationResult = $searchStation->get_result();
    $searchStation->close();

    if($searchStationResult->num_rows == 1) {
      $weatherStation = $searchStationResult->fetch_object();
      $stationID = $weatherStation->StationID;

      $insertData = $con->prepare("INSERT INTO WeatherData VALUES (?, ?, ?, ?, ?)");
      $insertData->bind_param("isddd", $stationID, $date, $temperature, $humidity, $pressure);
      $insertData->execute();
      $insertData->close();

      $return = array(
        "code" => 200,
        "content" => "OK"
      );
      header('Content-Type: application/json');
      exit(json_encode($return));
    } else {
      $return = array(
        "code" => 403,
        "content" => "Forbidden"
      );
      header('Content-Type: application/json');
      exit(json_encode($return));
    }
  } else {
    $return = array(
      "code" => 400,
      "content" => "Bad Request"
    );
    header('Content-Type: application/json');
    exit(json_encode($return));
  }
?>
