<?php
	$h = $_GET["host"];
	$un = $_GET["username"];
	$pw = $_GET["password"];
	$db = $_GET["database"];
	$tn = $_GET["tablename"];
	
	$con = mysqli_connect($h, $un, $pw, $db);
	
	if(mysqli_connect_errno($con)) {
		echo "Could not connect to MySQL database.";
	}
	
	mysqli_select_db($con, $db);
	
	$temp = $_GET["temp"];
	$humi = $_GET["humi"];
	$pres = $_GET["pres"];
	$date = $_GET["date"];
	
	$sql = "INSERT INTO $tn (Date, Temperature, Humidity, Pressure) VALUES ('$date', '$temp', '$humi', '$pres')";
	if($con->query($sql) === TRUE) {
		echo "Data uploaded.";
	} else {
		echo "Error while uploading data.<br>" . $sql;
	}
	
	mysqli_close($con);
?>