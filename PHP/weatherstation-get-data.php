<?php
	$latestWeatherData = array();
	$responseLatest = array();
	
	$h = $_GET["host"];
	$un = $_GET["username"];
	$pw = $_GET["password"];
	$db = $_GET["database"];
	$tn = $_GET["tablename"];
	$rq = $_GET["request"];
	
	$con = mysqli_connect($h, $un, $pw, $db);
	
	if(mysqli_connect_errno($con)) {
		echo "Could not connect to MySQL database.";
	}
	
	mysqli_select_db($con, $db);
	
	$sqlLatest = "SELECT * FROM $tn ORDER BY Date DESC LIMIT 1";
	if($stmtLatest = $con->prepare($sqlLatest)) {
		$stmtLatest->execute();
		
		$stmtLatest->bind_result($latestDate, $latestTemp, $latestHumi, $latestPres);
		
		if($stmtLatest->fetch()) {
			$latestWeatherData["date"] = $latestDate;
			$latestWeatherData["temp"] = $latestTemp;
			$latestWeatherData["humi"] = $latestHumi;
			$latestWeatherData["pres"] = $latestPres;
			
			$responseLatest["success"] = 1;
			$responseLatest["data"] = $latestWeatherData;			
		} else {
			$responseLatest["success"] = 0;
			$responseLatest["data"] = "null";
		}
		
		$stmtLatest->close();
	} else {
		$responseLatest["success"] = 0;
		$responseLatest["data"] = "null";
	}
	
	if($rq == "latest-temp") {
		echo $responseLatest["data"]["temp"];
	} else if($rq == "latest-humi") {
		echo $responseLatest["data"]["humi"];
	} else if($rq == "latest-pres") {
		echo $responseLatest["data"]["pres"];
	} else if($rq == "latest-date") {
		echo $responseLatest["data"]["date"];
	} else if($rq == "all-temp") {
		$sql = "SELECT Temperature FROM $tn";
		foreach($con->query($sql) as $row){
			echo "<p>".$row["Temperature"]."</p>";
		}
	} else if($rq == "all-humi") {
		$sql = "SELECT Humidity FROM $tn";
		foreach($con->query($sql) as $row){
			echo "<p>".$row["Humidity"]."</p>";
		}
	} else if($rq == "all-pres") {
		$sql = "SELECT Pressure FROM $tn";
		foreach($con->query($sql) as $row){
			echo "<p>".$row["Pressure"]."</p>";
		}
	} else if($rq == "all-date") {
		$sql = "SELECT Date FROM $tn";
		foreach($con->query($sql) as $row){
			echo "<p>".$row["Date"]."</p>";
		}
	} else {
		echo json_encode($responseLatest);
	}
	
	mysqli_close($con);
?>