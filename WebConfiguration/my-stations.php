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
        My Weather Stations
      </h1>
      <p >
        You can select any of the following Weather Stations in order to see their Data. <a href="home.php">Back to Home</a>
      </p>
      <div class="station-list">
        <?php
          $searchStations = $con->prepare("SELECT StationKey, StationName, UserName FROM WeatherStations AS a INNER JOIN Users AS b ON a.StationAuthor = b.UserID WHERE StationAuthor = ?");
          $searchStations->bind_param("i", $_SESSION["user"]);
          $searchStations->execute();
          $searchStationsResult = $searchStations->get_result();
          $searchStations->close();

          if($searchStationsResult->num_rows == 0) {
            echo "<p>You haven't registered any Weather Stations.</p>";
          }

          while($weatherStation = $searchStationsResult->fetch_assoc()) {
            $stationKey = $weatherStation["StationKey"];
            $stationName = $weatherStation["StationName"];
            $stationAuthor = $weatherStation["UserName"];

            echo "<a href=\"weather-station.php?key=$stationKey\" target=\"_blank\" class=\"station-list-item\"><p id=\"station-name\">$stationName</p><p id=\"station-author\">Created by $stationAuthor</p></a>";
          }
        ?>
      </div>
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
