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

  if(isset($_POST["submit"])) {
    $forename = $_POST["forename"];
    $surname = $_POST["surname"];
    $name = $surname . ", " . $forename;
    $username = $_POST["username"];
    $password = md5($_POST["password"]);
    $passwordRepeat = md5($_POST["password-repeat"]);

    if($password == $passwordRepeat) {
      $searchUser = $con->prepare("SELECT * FROM Users WHERE Username = ?");
      $searchUser->bind_param("s", $username);
      $searchUser->execute();
      $searchUserResult = $searchUser->get_result();
      $searchUser->close();

      if($searchUserResult->num_rows == 0) {
        $createUser = $con->prepare("INSERT INTO Users VALUES (NULL, ?, ?, ?)");
        $createUser->bind_param("sss", $username, $password, $name);
        $createUser->execute();
        $createUser->close();

        $message = "<p>Your Account was created. You may sign in now.</p>";
      } else {
        $message = "<p>This Username is already registered.</p>";
      }
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

    <section id="login">
      <h1 >
        Register
      </h1>
      <p >
        To create an Account, please fill out the following Form.
      </p>
      <form class="register viewport-animation viewport-bottom" method="post">
        <input type="text" name="forename" value="" placeholder="Forename" required id="half-1">
        <input type="text" name="surname" value="" placeholder="Surname" required id="half-2">
        <input type="text" name="username" value="" placeholder="Username" required>
        <input type="password" name="password" value="" placeholder="Password" required>
        <input type="password" name="password-repeat" value="" placeholder="Password (repeat)" required>
        <input type="submit" name="submit" value="Register">
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

    <!-- SIDEBAR -->
    <script src="script/sidebar.js"></script>
  </body>
</html>
