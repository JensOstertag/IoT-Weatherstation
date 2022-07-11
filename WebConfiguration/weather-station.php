<?php
  session_start();

  require("connection.php"); // $con = new mysqli(host, username, password, database)
  if($con->connect_error) {
    $return = array(
      "code" => 500,
      "content" => "Internal Error: Database Connection Failed"
    );
    header('Content-Type: application/json');
    exit(json_encode($return));
  }

  if(!(isset($_SESSION["user"]))) {
    header("Location: index.php");
  }

  if(isset($_GET["key"])) {
    $stationKey = $_GET["key"];

    $searchStation = $con->prepare("SELECT StationID, StationKey, StationName, StationLocation, StationAuthor, UserName FROM WeatherStations AS a INNER JOIN Users AS b ON a.StationAuthor = b.UserID WHERE StationKey = ?");
    $searchStation->bind_param("s", $stationKey);
    $searchStation->execute();
    $searchStationResult = $searchStation->get_result();
    $searchStation->close();

    if($searchStationResult->num_rows == 1) {
      $weatherStation = $searchStationResult->fetch_object();
      $stationID = $weatherStation->StationID;
      $stationName = $weatherStation->StationName;
      $stationLocation = $weatherStation->StationLocation;
      $stationKey = $weatherStation->StationKey;
      $stationAuthor = $weatherStation->UserName;

      $searchData = $con->prepare("SELECT * FROM WeatherData WHERE StationID = ? ORDER BY Date DESC LIMIT 1");
      $searchData->bind_param("i", $stationID);
      $searchData->execute();
      $searchDataResult = $searchData->get_result();
      $searchData->close();

      if($searchDataResult->num_rows == 1) {
        $weatherData = $searchDataResult->fetch_object();
        $latestPush = $weatherData->PushDate;
        $latestTemperature = $weatherData->Temperature;
        $latestHumidity = $weatherData->Humidity;
        $latestPressure = $weatherData->Pressure;
      }
    } else {
      header("Location: home.php");
    }
  } else {
    header("Location: home.php");
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WeatherStations</title>

    <link rel="shortcut icon" href="img/app-icon.svg"/>

    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="style/site.min.css">
    <link rel="stylesheet" href="style/header.min.css">
    <link rel="stylesheet" href="style/footer.min.css">
    <link rel="stylesheet" href="style/content.min.css">

  </head>
  <body>
    <script src="script/websiteconfig.js"></script>

    <nav>
      <div class="logo">
        <a href="./">
          WeatherStations
        </a>
      </div>
      <ul class="nav-links nav-default">
        <li><a href="./index.php">Login</a></li>
        <hr>
        <li><a href="./register.php">Register</a></li>
      </ul>

      <div class="burger">
        <div class="l1"></div>
        <div class="l2"></div>
        <div class="l3"></div>
      </div>

      <div class="alpha alpha-default"></div>
    </nav>

    <section id="home">
      <h1 >
         Weather Station Details
      </h1>
      <p >
        Have an Overview over this Weather Stations's Data. <a href="home.php">Back to Home</a>
      </p>
      <?php
        if($notFound == false) {
          echo "<p><b>Station Name:</b> $stationName<br><b>Station Location:</b> $stationLocation<br><b>Station Author:</b> $stationAuthor<br><b>Station Key:</b> $stationKey</p>";

          if(isset($latestPush) && isset($latestTemperature) && isset($latestHumidity) && isset($latestPressure)) {
            echo "<p><b>Last Data Push:</p> $latestPush<br><b>Last Measured Temperature:</b> $latestTemperature °C<br><b>Last Measured Humidity:</b> $latestHumidity %<br><b>Last Measured Pressure:</b> $latestPressure</p>";
          } else {
            echo "<p>There is no Data for this Weather Station yet.</p>";
          }

          echo "<p><a href=\"create-api\">Use this Station's Data</a></p>";
        } else {
          echo "<p>The Weather Station with the given Key wasn't found.</p>";
        }
      ?>
    </section>

    <footer>
      <div class="wrapper">
        <div class="footer-text" style="margin-top: 20px;">
          <div class="footer-logo">
            WeatherStations
          </div>
          <p class="copyright">&copy; 2020 - <script>document.write(new Date().getFullYear())</script> &nbsp; Jens Ostertag</p>
          <p class="version"><img src="img/ico/gear.svg" class="gear"> Version: <script>document.write(version);</script></p>
        </div>
      </div>
    </footer>

    <!-- SIDEBAR -->
    <script src="script/sidebar.js"></script>
  </body>
</html>
