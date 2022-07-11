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

  if(isset($_POST["submit"])) {
    $stationName = $_POST["name"];
    $stationLocation = $_POST["location"];
    $stationPassword = md5($_POST["password"]);
    $stationPasswordRepeat = md5($_POST["password-repeat"]);
    $stationAuthor = $_SESSION["user"];

    if($stationPassword == $stationPasswordRepeat) {
      $stationKey = "";
      $keyFound = false;

      do {
        $stationKey = bin2hex(random_bytes(16));

        $searchKey = $con->prepare("SELECT * FROM WeatherStations WHERE StationKey = ?");
        $searchKey->bind_param("s", $stationKey);
        $searchKey->execute();
        $searchKeyResult = $searchKey->get_result();
        $searchKey->close();

        if($searchKeyResult->num_rows == 0) {
          $keyFound = true;
        }
      } while($keyFound == false);

      $createStation = $con->prepare("INSERT INTO WeatherStations VALUES (NULL, ?, ?, ?, ?, ?)");
      $createStation->bind_param("sssis", $stationKey, $stationName, $stationLocation, $stationAuthor, $stationPassword);
      $createStation->execute();
      $createStation->close();

      //$message = "<p>Your Weather Station was registered successfully.</p>";
      header("Location: station-registered.php?key=$stationKey");
    } else {
      $message = "<p>Your Passwords do not match.</p>";
    }
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
         Register Weather Station
      </h1>
      <p >
        Register a new Weather Station. <a href="home.php">Back to Home</a>
      </p>
      <form class="register viewport-animation viewport-bottom" method="post">
        <input type="text" name="name" value="" placeholder="Weather Station Name" required>
        <input type="text" name="location" value="" placeholder="Weather Station Location" required>
        <input type="password" name="password" value="" placeholder="Weather Station Authentication" required>
        <input type="password" name="password-repeat" value="" placeholder="Weather Station Authentication (repeat)" required>
        <input type="submit" name="submit" value="Register new Weather Station">
        <?php
          echo $message;
        ?>
      </form>
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
    <!-- COOKIE CONSENT -->
    <script src="script/cookieconsent.js"></script>
    <script src="script/viewport.js"></script>
    <script type="text/javascript">
      function loadPage() {
        cookieConsent();
      }
    </script>

    <!-- SIDEBAR -->
    <script src="script/sidebar.js"></script>
  </body>
</html>
