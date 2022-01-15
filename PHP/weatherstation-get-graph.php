<?php
	$dp = array();
	
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
	
	if($rq == "temp") {
		$sql = "SELECT Date, Temperature FROM $tn";
		foreach($con->query($sql) as $value) {
			array_push($dp, array("x" => DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $value["Date"])->getTimestamp(), "y" => $value["Temperature"]));
		}
	} else if($rq == "humi") {
		$sql = "SELECT Date, Humidity FROM $tn";
		foreach($con->query($sql) as $value) {
			array_push($dp, array("x" => DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $value["Date"])->getTimestamp(), "y" => $value["Humidity"]));
		}
	} else if($rq == "pres") {
		$sql = "SELECT Date, Pressure FROM $tn";
		foreach($con->query($sql) as $value) {
			array_push($dp, array("x" => DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $value["Date"])->getTimestamp(), "y" => $value["Pressure"]));
		}
	}
?>

<!DOCTYPE HTML>
<html>
	<head> 
		<script>
			var dataPoint = [];
			window.onload = function() {
				setData();
				
				var chart = new CanvasJS.Chart("chartContainer", {
					theme: "light2",
					animationEnabled: true,
					zoomEnabled: true,
					zoomType: "x",
					exportEnabled: true,
					exportFileName: "Wetterstation",
					title: {
						text: ""
					},
					axisX: {
						interval: 1,
						intervalType: "hour",
						valueformatString: "HH:mm"
					},
					data: [{
						type: "spline",
						markerColor: "#900500",
						lineColor: "#FC3E3E",
						dataPoints: dataPoint
					}]
				});
				
				chart.render();
			}
			
			function setData() {
				var arr = <?php echo json_encode($dp, JSON_NUMERIC_CHECK);?>;
				
				if(arr.length > 200) {
					for(var i = arr.length - (arr.length - 200); i < arr.length; i++) {
						dataPoint.push({
							x: new Date(arr[i]["x"] * 1000),
							y: arr[i]["y"]
						});
					}
				} else if(arr.length <= 200) {
					for(var i = 0; i < arr.length; i++) {
						dataPoint.push({
							x: new Date(arr[i]["x"] * 1000),
							y: arr[i]["y"]
						});
					}
				}				
			}
		</script>
	</head>
	<body>
		<div id="chartContainer" style="height: 80vh; width: 100%;"></div>
		<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
	</body>
</html>