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

  if(isset($_GET["key"])) {
    $key = $_GET["key"];

    $searchStation = $con->prepare("SELECT StationID FROM WeatherStations WHERE StationKey = ?");
    $searchStation->bind_param("s", $key);
    $searchStation->execute();
    $searchStationResult = $searchStation->get_result();
    $searchStation->close();

    if($searchStationResult->num_rows == 1) {
      $searchData = $con->prepare("SELECT PushDate, Temperature, Humidity, Pressure FROM WeatherData AS a INNER JOIN (SELECT StationID FROM WeatherStations WHERE StationKey = ?) AS b ON a.StationID = b.StationID ORDER BY PushDate DESC LIMIT 1");
      $searchData->bind_param("s", $key);
      $searchData->execute();
      $searchDataResult = $searchData->get_result();
      $searchData->close();

      if($searchDataResult->num_rows == 1) {
        $data = $searchDataResult->fetch_object();
        $date = $data->PushDate;
        $temperature = $data->Temperature;
        $humidity = $data->Humidity;
        $pressure = $data->Pressure;

        $return = array(
          "code" => 200,
          "content" => array(
            "latestPush" => $date,
            "temperature" => $temperature,
            "humidity" => $humidity,
            "pressure" => $pressure
          )
        );
        header('Content-Type: application/json');
        exit(json_encode($return));
      } else {
        $return = array(
          "code" => 204,
          "content" => "No Content"
        );
        header('Content-Type: application/json');
        exit(json_encode($return));
      }
    } else {
      $return = array(
        "code" => 404,
        "content" => "Not Found"
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
