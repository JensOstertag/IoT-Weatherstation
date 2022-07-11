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

  if(isset($_GET["key"]) && isset($_GET["pagelength"]) && isset($_GET["page"])) {
    $key = $_GET["key"];
    $pageLength = $_GET["pagelength"];
    $page = $_GET["page"];
    $offset = $page * $pageLength;

    if($pageLength >= 1 && $pageLength <= 100) {
      $searchStation = $con->prepare("SELECT StationID FROM WeatherStations WHERE StationKey = ?");
      $searchStation->bind_param("s", $key);
      $searchStation->execute();
      $searchStationResult = $searchStation->get_result();
      $searchStation->close();

      if($searchStationResult->num_rows == 1) {
        $searchData = $con->prepare("SELECT PushDate, Temperature, Humidity, Pressure FROM WeatherData AS a INNER JOIN (SELECT StationID FROM WeatherStations WHERE StationKey = ?) AS b ON a.StationID = b.StationID ORDER BY PushDate DESC LIMIT ? OFFSET ?");
        $searchData->bind_param("sii", $key, $pageLength, $offset);
        $searchData->execute();
        $searchDataResult = $searchData->get_result();
        $searchData->close();

        if($searchDataResult->num_rows > 0) {
          $allData = array();

          while($data = $searchDataResult->fetch_assoc()) {
            $date = $data["PushDate"];
            $temperature = $data["Temperature"];
            $humidity = $data["Humidity"];
            $pressure = $data["Pressure"];

            $dataArray = array(
              "latestPush" => $date,
              "temperature" => $temperature,
              "humidity" => $humidity,
              "pressure" => $pressure
            );
            array_push($allData, $dataArray);
          }

          $return = array(
            "code" => 200,
            "content" => $allData
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
  } else {
    $return = array(
      "code" => 400,
      "content" => "Bad Request"
    );
    header('Content-Type: application/json');
    exit(json_encode($return));
  }
?>
