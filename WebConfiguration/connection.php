<?php
  $h = "localhost";
  $un = "username";
  $pw = "password";
  $db = "database";

  $con = new mysqli($h, $un, $pw, $db);
  
  $createUsers = $con->prepare("CREATE TABLE IF NOT EXISTS Users (UserID INT NOT NULL AUTO_INCREMENT, UserName VARCHAR(100) NOT NULL, Password VARCHAR(32) NOT NULL, Name VARCHAR(100), PRIMARY KEY (UserID)) ENGINE = InnoDB;");
  $createUsers->execute();
  $createUsers->close();
  
  $createWeatherData = $con->prepare("CREATE TABLE IF NOT EXISTS WeatherData (StationID INT NOT NULL, PushDate VARCHAR(20) NOT NULL, Temperature FLOAT NOT NULL, Humidity FLOAT NOT NULL, Pressure FLOAT NOT NULL) ENGINE = InnoDB;");
  $createWeatherData->execute();
  $createWeatherData->close();
  
  $createWeatherStations = $con->prepare("CREATE TABLE IF NOT EXISTS WeatherStations (StationID INT NOT NULL AUTO_INCREMENT, StationKey VARCHAR(32) NOT NULL, StationName VARCHAR(32) NOT NULL, StationLocation VARCHAR(32) NOT NULL, StationAuthor INT NOT NULL, StationPassword VARCHAR(32) NOT NULL, PRIMARY KEY (StationID)) ENGINE = InnoDB;");
  $createWeatherStations->execute();
  $createWeatherStations->close();
?>
